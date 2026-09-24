<?php

namespace App\Domains\Newsletter\Http\Controllers\Admin;

use App\Models\EmailCampaign;
use App\Models\Core\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use App\Http\Controllers\Controller;

class NewsletterAdminController extends Controller
{
    public function campaigns(): View
    {
        $campaigns = EmailCampaign::orderBy('created_at', 'desc')->get();

        $stats = [
            'total' => $campaigns->count(),
            'draft' => $campaigns->where('status', 'draft')->count(),
            'sending' => $campaigns->where('status', 'sending')->count(),
            'completed' => $campaigns->where('status', 'completed')->count(),
            'failed' => $campaigns->where('status', 'failed')->count(),
        ];

        return view('admin.newsletter-campaigns', compact('campaigns', 'stats'));
    }

    public function createCampaign(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'html_content' => 'required|string',
            'text_content' => 'nullable|string',
        ]);

        $subscribers = NewsletterSubscriber::where('is_active', true)->count();

        $campaign = EmailCampaign::create([
            'subject' => $validated['subject'],
            'html_content' => $validated['html_content'],
            'text_content' => $validated['text_content'] ?? null,
            'total_recipients' => $subscribers,
            'status' => 'draft',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Campaign created successfully',
                'campaign' => $campaign,
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

        $subscribers = NewsletterSubscriber::where('is_active', true)->get();

        foreach ($subscribers as $subscriber) {
            $this->sendEmailToSubscriber($campaign, $subscriber);
        }

        $campaign->sent_count = $subscribers->count();
        $campaign->markAsCompleted();

        return response()->json([
            'success' => true,
            'message' => 'Campaign sent successfully',
            'campaign' => $campaign->refresh(),
        ]);
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
}
