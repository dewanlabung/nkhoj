<?php

namespace App\Services\Newsletter;

use App\Domains\Newsletter\Models\EmailCampaign;
use App\Domains\Newsletter\Models\NewsletterSubscriber;
use App\Services\Email\EmailServiceFactory;
use Illuminate\Support\Str;

class NewsletterService
{
    protected $emailService;

    public function __construct()
    {
        $this->emailService = EmailServiceFactory::create();
    }

    public function createCampaign(string $subject, string $htmlContent, string $textContent = ''): EmailCampaign
    {
        $activeSubscribers = NewsletterSubscriber::where('is_active', true)->count();

        return EmailCampaign::create([
            'subject' => $subject,
            'html_content' => $htmlContent,
            'text_content' => $textContent,
            'total_recipients' => $activeSubscribers,
            'status' => 'draft',
        ]);
    }

    public function sendCampaign(int $campaignId): void
    {
        $campaign = EmailCampaign::findOrFail($campaignId);
        $campaign->markAsSending();

        $subscribers = NewsletterSubscriber::where('is_active', true)->cursor();

        foreach ($subscribers as $subscriber) {
            $htmlContent = $this->injectUnsubscribeLink($campaign->html_content, $subscriber->token);

            if ($this->emailService->send($subscriber->email, $campaign->subject, $htmlContent, $campaign->text_content)) {
                $campaign->incrementSentCount();
            } else {
                \Log::warning("Failed to send newsletter to {$subscriber->email}");
            }
        }
    }

    public function sendCampaignAsync(int $campaignId, int $perBatch = 10): void
    {
        // TODO: Implement async sending with job queue
        $this->sendCampaign($campaignId);
    }

    public function sendNextBatch(int $campaignId, int $batchSize = 10): array
    {
        $campaign = EmailCampaign::findOrFail($campaignId);

        if ($campaign->status === 'completed') {
            return ['sent' => 0, 'remaining' => 0, 'completed' => true];
        }

        $campaign->markAsSending();

        $unsent = NewsletterSubscriber::where('is_active', true)
            ->skip($campaign->sent_count)
            ->take($batchSize)
            ->get();

        $sentCount = 0;
        foreach ($unsent as $subscriber) {
            $htmlContent = $this->injectUnsubscribeLink($campaign->html_content, $subscriber->token);

            if ($this->emailService->send($subscriber->email, $campaign->subject, $htmlContent, $campaign->text_content)) {
                $campaign->increment('sent_count');
                $sentCount++;
            }
        }

        if ($campaign->sent_count >= $campaign->total_recipients) {
            $campaign->update(['status' => 'completed']);
        }

        return [
            'sent' => $sentCount,
            'remaining' => max(0, $campaign->total_recipients - $campaign->sent_count),
            'completed' => $campaign->status === 'completed',
            'progress' => $campaign->getProgressPercentage(),
        ];
    }

    public function unsubscribe(string $token): bool
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();

        if (!$subscriber) {
            return false;
        }

        $subscriber->update(['is_active' => false]);

        return true;
    }

    public function resubscribe(string $token): bool
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();

        if (!$subscriber) {
            return false;
        }

        $subscriber->update(['is_active' => true]);

        return true;
    }

    protected function injectUnsubscribeLink(string $htmlContent, string $token): string
    {
        $unsubscribeUrl = route('newsletter.unsubscribe', ['token' => $token]);
        $unsubscribeHtml = sprintf(
            '<p style="text-align:center;margin-top:30px;font-size:12px;color:#999;"><a href="%s" style="color:#999;text-decoration:underline;">Unsubscribe from this newsletter</a></p>',
            $unsubscribeUrl
        );

        if (strpos($htmlContent, '</body>') !== false) {
            return str_replace('</body>', $unsubscribeHtml . '</body>', $htmlContent);
        }

        return $htmlContent . $unsubscribeHtml;
    }
}
