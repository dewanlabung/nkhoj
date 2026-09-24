<?php

namespace App\Domains\Communication\Services;

use App\Domains\Communication\Models\CommunicationCampaign;
use App\Domains\Communication\Models\CommunicationRecipient;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Carbon\Carbon;

class CommunicationService
{
    /**
     * Create a new campaign
     */
    public function createCampaign(array $data, $userId)
    {
        $campaign = CommunicationCampaign::create(array_merge($data, [
            'created_by' => $userId,
            'status' => 'draft',
        ]));

        // Queue recipient calculation if targeting is ready
        if (!empty($data['target_type'])) {
            $this->calculateRecipients($campaign);
        }

        return $campaign;
    }

    /**
     * Calculate and create recipient records based on targeting
     */
    public function calculateRecipients(CommunicationCampaign $campaign)
    {
        $recipients = $this->getTargetedRecipients($campaign);
        $campaign->total_recipients = $recipients->count();
        $campaign->save();

        // Create recipient records in batch
        $recipientData = $recipients->map(function ($user) use ($campaign) {
            return [
                'campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->chunk(500);

        foreach ($recipientData as $chunk) {
            CommunicationRecipient::insert($chunk->toArray());
        }
    }

    /**
     * Get targeted recipients based on campaign configuration
     */
    public function getTargetedRecipients(CommunicationCampaign $campaign)
    {
        return match($campaign->target_type) {
            'all_users' => User::query(),
            'activated_users' => User::whereNotNull('email_verified_at'),
            'inactive_users' => User::whereNull('email_verified_at'),
            'newsletter_subscribers' => User::whereHas('communicationPreferences',
                fn($q) => $q->where('email_newsletters', true)),
            'segment' => $this->getSegmentMembers($campaign->target_config['segment_id'] ?? null),
            'tag' => $this->getTaggedUsers($campaign->target_config['tag'] ?? null),
            'custom_list' => User::whereIn('id', $campaign->target_config['user_ids'] ?? []),
            default => User::query(),
        };
    }

    /**
     * Send campaign to a specific recipient
     */
    public function sendToRecipient(CommunicationRecipient $recipient, CommunicationCampaign $campaign)
    {
        try {
            $user = $recipient->user;

            // Check if user is unsubscribed
            if ($this->isUserUnsubscribed($user, $campaign->channels)) {
                $recipient->markAsUnsubscribed();
                return;
            }

            // Check user preferences
            if (!$this->userCanReceive($user, $campaign)) {
                $recipient->status = 'skipped';
                $recipient->save();
                return;
            }

            // Send via each configured channel
            foreach ($campaign->channels as $channel) {
                $this->sendViaChannel($channel, $recipient, $campaign, $user);
            }

            $recipient->markAsSent();
            $campaign->increment('sent_count');

        } catch (\Exception $e) {
            $recipient->setError($e->getMessage());
        }
    }

    /**
     * Send via specific channel
     */
    private function sendViaChannel($channel, $recipient, $campaign, $user)
    {
        match($channel) {
            'email' => $this->sendEmail($recipient, $campaign, $user),
            'in_app' => $this->sendInApp($recipient, $campaign, $user),
            'announcement' => $this->createAnnouncement($campaign, $user),
            'push' => $this->sendPushNotification($recipient, $campaign, $user),
            default => null,
        };
    }

    /**
     * Send email
     */
    public function sendEmail(CommunicationRecipient $recipient, CommunicationCampaign $campaign, $user)
    {
        try {
            $unsubscribeToken = $this->generateUnsubscribeToken($user, 'email');
            $content = $this->replaceEmailVariables(
                $campaign->html_content,
                $user,
                $unsubscribeToken
            );

            Mail::html($content, function ($message) use ($campaign, $user) {
                $message->to($user->email)
                    ->subject($campaign->subject);
            });

            $recipient->markAsDelivered();

        } catch (\Exception $e) {
            $recipient->setError('Email send failed: ' . $e->getMessage());
        }
    }

    /**
     * Send in-app notification
     */
    private function sendInApp(CommunicationRecipient $recipient, CommunicationCampaign $campaign, $user)
    {
        // Create in-app notification record
        // This integrates with existing notification system
        \DB::table('notifications')->insert([
            'to_user_id' => $user->id,
            'from_user_id' => 0,
            'action' => 'mass_notification',
            'message' => $campaign->subject . ': ' . strip_tags($campaign->html_content),
            'node_url' => '/communications/' . $campaign->id,
            'created_at' => now(),
        ]);

        $recipient->markAsDelivered();
    }

    /**
     * Create announcement banner
     */
    private function createAnnouncement($campaign, $user = null)
    {
        \DB::table('announcements')->insert([
            'campaign_id' => $campaign->id,
            'name' => $campaign->name,
            'title' => $campaign->subject,
            'type' => $campaign->announcement_type,
            'code' => $campaign->html_content,
            'start_date' => $campaign->announcement_start_at ?? now(),
            'end_date' => $campaign->announcement_end_at ?? now()->addDays(7),
            'created_at' => now(),
        ]);
    }

    /**
     * Send push notification
     */
    private function sendPushNotification(CommunicationRecipient $recipient, CommunicationCampaign $campaign, $user)
    {
        // Integration point for push notification service
        // (Firebase, Pusher, etc.)
        if ($user->push_token) {
            // Queue for async processing
            Queue::push(function ($job) use ($user, $campaign) {
                // Send push notification
                $job->delete();
            });
        }
    }

    /**
     * Replace email template variables
     */
    public function replaceEmailVariables($content, $user, $unsubscribeToken)
    {
        $unsubscribeLink = route('communications.unsubscribe', ['token' => $unsubscribeToken]);

        return str_replace([
            '{{first_name}}',
            '{{last_name}}',
            '{{email}}',
            '{{username}}',
            '{{unsubscribe_link}}',
            '{{year}}',
            '{{current_date}}',
        ], [
            $user->first_name,
            $user->last_name,
            $user->email,
            $user->username,
            $unsubscribeLink,
            now()->year,
            now()->format('F j, Y'),
        ], $content);
    }

    /**
     * Generate unsubscribe token
     */
    private function generateUnsubscribeToken($user, $type)
    {
        $token = \Illuminate\Support\Str::random(64);

        \DB::table('communication_unsubscribes')->insert([
            'user_id' => $user->id,
            'unsubscribe_type' => $type,
            'unsubscribe_token' => $token,
            'unsubscribed_at' => now(),
            'created_at' => now(),
        ]);

        return $token;
    }

    /**
     * Check if user is unsubscribed
     */
    private function isUserUnsubscribed($user, $channels)
    {
        if (in_array('email', $channels)) {
            $unsub = \DB::table('communication_unsubscribes')
                ->where('user_id', $user->id)
                ->whereIn('unsubscribe_type', ['email', 'all'])
                ->exists();

            if ($unsub) return true;
        }

        return false;
    }

    /**
     * Check if user can receive this communication
     */
    private function userCanReceive(User $user, CommunicationCampaign $campaign)
    {
        $prefs = $user->communicationPreferences;

        if (!$prefs) {
            return true; // Default: allow
        }

        // Check channel-specific preferences
        foreach ($campaign->channels as $channel) {
            $prefKey = ($channel === 'in_app' ? 'in_app_notifications' :
                       ($channel === 'email' ? 'email_newsletters' :
                       ($channel === 'push' ? 'push_notifications' : null)));

            if ($prefKey && !$prefs->{$prefKey}) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get segment members (placeholder)
     */
    private function getSegmentMembers($segmentId)
    {
        return User::whereHas('communicationSegments',
            fn($q) => $q->where('segment_id', $segmentId));
    }

    /**
     * Get tagged users (placeholder)
     */
    private function getTaggedUsers($tag)
    {
        return User::whereJsonContains('tags', $tag);
    }

    /**
     * Start sending campaign
     */
    public function startSending(CommunicationCampaign $campaign)
    {
        if ($campaign->status !== 'draft' && $campaign->status !== 'scheduled') {
            throw new \Exception('Campaign cannot be sent in ' . $campaign->status . ' status');
        }

        $campaign->markAsSending();

        // Queue batch sending
        $recipients = $campaign->recipients()
            ->where('status', 'pending')
            ->get();

        foreach ($recipients as $recipient) {
            Queue::push(function ($job) use ($recipient, $campaign) {
                $this->sendToRecipient($recipient, $campaign);
                $job->delete();
            });
        }
    }

    /**
     * Get campaign analytics
     */
    public function getAnalytics(CommunicationCampaign $campaign)
    {
        return [
            'total_recipients' => $campaign->total_recipients,
            'sent_count' => $campaign->sent_count,
            'delivery_rate' => $campaign->getDeliveryRate(),
            'open_rate' => $campaign->getOpenRate(),
            'click_rate' => $campaign->getClickRate(),
            'bounce_rate' => $campaign->bounce_rate,
            'unsubscribe_rate' => $campaign->total_recipients > 0
                ? round(($campaign->unsubscribed_count / $campaign->total_recipients) * 100, 2)
                : 0,
        ];
    }
}
