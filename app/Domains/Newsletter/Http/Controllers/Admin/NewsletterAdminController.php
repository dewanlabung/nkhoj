<?php

namespace App\Domains\Newsletter\Http\Controllers\Admin;

use App\Models\EmailCampaign;
use App\Models\Core\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class NewsletterAdminController extends Controller
{
    public function campaigns(): View
    {
        $campaigns = EmailCampaign::orderBy('created_at', 'desc')->paginate(15);

        $allCampaigns = EmailCampaign::get();
        $stats = [
            'total' => $allCampaigns->count(),
            'draft' => $allCampaigns->where('status', 'draft')->count(),
            'sending' => $allCampaigns->where('status', 'sending')->count(),
            'completed' => $allCampaigns->where('status', 'completed')->count(),
            'failed' => $allCampaigns->where('status', 'failed')->count(),
        ];

        return view('admin.newsletter-campaigns', compact('campaigns', 'stats'));
    }

    public function createCampaign(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'html_content' => 'required|string',
            'text_content' => 'nullable|string',
            'recipient_filter' => 'required|in:all,activated,inactive,week,month,3months,6months,9months,year,newsletter',
            'test_email' => 'nullable|email',
        ]);

        $subscribers = $this->getFilteredSubscribersCount($validated['recipient_filter']);

        if ($validated['test_email'] ?? false) {
            $this->sendTestEmail(
                $validated['test_email'],
                $validated['subject'],
                $validated['html_content'],
                $validated['text_content']
            );
        }

        $campaign = EmailCampaign::create([
            'subject' => $validated['subject'],
            'html_content' => $validated['html_content'],
            'text_content' => $validated['text_content'] ?? null,
            'total_recipients' => $subscribers,
            'status' => 'draft',
            'recipient_filter' => $validated['recipient_filter'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Campaign created successfully',
                'campaign' => $campaign,
                'subscriber_count' => $subscribers,
            ]);
        }

        return back()->with('success', 'Campaign created successfully');
    }

    public function sendCampaign(Request $request, $id)
    {
        $campaign = EmailCampaign::findOrFail($id);

        if ($campaign->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Campaign is not in draft status',
            ], 422);
        }

        $campaign->markAsSending();

        $filter = $campaign->recipient_filter ?? 'newsletter';
        $emails = $this->getFilteredSubscribers($filter);

        foreach ($emails as $email) {
            $this->sendEmailBatch($campaign, $email);
        }

        $campaign->sent_count = $emails->count();
        $campaign->markAsCompleted();

        return response()->json([
            'success' => true,
            'message' => 'Campaign sent successfully',
            'campaign' => $campaign->refresh(),
        ]);
    }

    private function sendEmailBatch(EmailCampaign $campaign, string $email): void
    {
        try {
            Mail::html($campaign->html_content, function ($message) use ($campaign, $email) {
                $message->to($email)->subject($campaign->subject);
            });
        } catch (\Exception $e) {
            \Log::error('Campaign email failed', [
                'campaign_id' => $campaign->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendNextBatch(Request $request, $id)
    {
        $campaign = EmailCampaign::findOrFail($id);
        $batchSize = $request->input('batch_size', 50);

        if ($campaign->status === 'draft') {
            $campaign->markAsSending();
        }

        if ($campaign->status !== 'sending') {
            return response()->json([
                'success' => false,
                'message' => 'Campaign is not sending',
            ], 422);
        }

        $remainingSubscribers = NewsletterSubscriber::where('is_active', true)
            ->offset($campaign->sent_count)
            ->limit($batchSize)
            ->get();

        foreach ($remainingSubscribers as $subscriber) {
            $this->sendEmailToSubscriber($campaign, $subscriber);
        }

        $campaign->incrementSentCount($remainingSubscribers->count());

        if ($campaign->sent_count >= $campaign->total_recipients) {
            $campaign->markAsCompleted();
        }

        return response()->json([
            'success' => true,
            'message' => 'Batch sent successfully',
            'sent_this_batch' => $remainingSubscribers->count(),
            'campaign' => $campaign->refresh(),
        ]);
    }

    public function campaignStatus($id)
    {
        $campaign = EmailCampaign::findOrFail($id);

        return response()->json([
            'id' => $campaign->id,
            'status' => $campaign->status,
            'sent_count' => $campaign->sent_count,
            'total_recipients' => $campaign->total_recipients,
            'progress' => $campaign->getProgressPercentage(),
        ]);
    }

    public function deleteCampaign($id)
    {
        $campaign = EmailCampaign::findOrFail($id);

        if ($campaign->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft campaigns can be deleted',
            ], 422);
        }

        $campaign->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Campaign deleted successfully');
    }

    private function sendEmailToSubscriber(EmailCampaign $campaign, NewsletterSubscriber $subscriber): void
    {
        try {
            Mail::html($campaign->html_content, function ($message) use ($campaign, $subscriber) {
                $message->to($subscriber->email)
                    ->subject($campaign->subject);
            });
        } catch (\Exception $e) {
            \Log::error('Failed to send campaign email', [
                'campaign_id' => $campaign->id,
                'subscriber_id' => $subscriber->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getFilteredSubscribersCount(string $filter): int
    {
        return match($filter) {
            'all' => User::count(),
            'activated' => User::where('email_verified_at', '!=', null)->count(),
            'inactive' => User::where('email_verified_at', null)->count(),
            'week' => User::where('last_active_at', '<', Carbon::now()->subWeek())->count(),
            'month' => User::where('last_active_at', '<', Carbon::now()->subMonth())->count(),
            '3months' => User::where('last_active_at', '<', Carbon::now()->subMonths(3))->count(),
            '6months' => User::where('last_active_at', '<', Carbon::now()->subMonths(6))->count(),
            '9months' => User::where('last_active_at', '<', Carbon::now()->subMonths(9))->count(),
            'year' => User::where('last_active_at', '<', Carbon::now()->subYear())->count(),
            'newsletter' => NewsletterSubscriber::where('is_active', true)->count(),
            default => 0,
        };
    }

    private function getFilteredSubscribers(string $filter)
    {
        return match($filter) {
            'all' => User::pluck('email'),
            'activated' => User::where('email_verified_at', '!=', null)->pluck('email'),
            'inactive' => User::where('email_verified_at', null)->pluck('email'),
            'week' => User::where('last_active_at', '<', Carbon::now()->subWeek())->pluck('email'),
            'month' => User::where('last_active_at', '<', Carbon::now()->subMonth())->pluck('email'),
            '3months' => User::where('last_active_at', '<', Carbon::now()->subMonths(3))->pluck('email'),
            '6months' => User::where('last_active_at', '<', Carbon::now()->subMonths(6))->pluck('email'),
            '9months' => User::where('last_active_at', '<', Carbon::now()->subMonths(9))->pluck('email'),
            'year' => User::where('last_active_at', '<', Carbon::now()->subYear())->pluck('email'),
            'newsletter' => NewsletterSubscriber::where('is_active', true)->pluck('email'),
            default => collect(),
        };
    }

    private function sendTestEmail(string $email, string $subject, string $html, ?string $text = null): bool
    {
        try {
            Mail::html($html, function ($message) use ($email, $subject) {
                $message->to($email)->subject("[TEST] {$subject}");
            });
            return true;
        } catch (\Exception $e) {
            \Log::error('Test email failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
