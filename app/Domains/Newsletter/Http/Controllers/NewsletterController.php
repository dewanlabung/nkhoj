<?php

namespace App\Domains\Newsletter\Http\Controllers;

use App\Domains\Newsletter\Models\EmailCampaign;
use App\Domains\Newsletter\Models\NewsletterSubscriber;
use App\Services\Newsletter\NewsletterService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NewsletterController extends Controller
{
    protected $newsletterService;

    public function __construct()
    {
        $this->newsletterService = new NewsletterService();
    }

    public function unsubscribe(string $token)
    {
        $success = $this->newsletterService->unsubscribe($token);

        if ($success) {
            return view('newsletter.unsubscribe-success');
        }

        return view('newsletter.unsubscribe-error', ['message' => 'Invalid or expired unsubscribe link.']);
    }

    public function resubscribe(string $token)
    {
        $success = $this->newsletterService->resubscribe($token);

        if ($success) {
            return view('newsletter.resubscribe-success');
        }

        return view('newsletter.resubscribe-error', ['message' => 'Invalid or expired resubscribe link.']);
    }
}

namespace App\Domains\Newsletter\Http\Controllers\Admin;

use App\Domains\Admin\Http\Controllers\BaseAdminController;
use App\Domains\Newsletter\Models\EmailCampaign;
use App\Domains\Newsletter\Models\NewsletterSubscriber;
use App\Services\Newsletter\NewsletterService;
use Illuminate\Http\Request;

class NewsletterAdminController extends BaseAdminController
{
    protected $newsletterService;

    public function __construct()
    {
        $this->newsletterService = new NewsletterService();
    }

    public function campaigns(Request $request)
    {
        $this->requireAdmin();

        return view('admin.newsletter-campaigns', [
            'campaigns' => EmailCampaign::latest()->paginate(20),
            'stats' => [
                'total' => EmailCampaign::count(),
                'draft' => EmailCampaign::where('status', 'draft')->count(),
                'sending' => EmailCampaign::where('status', 'sending')->count(),
                'completed' => EmailCampaign::where('status', 'completed')->count(),
            ],
        ]);
    }

    public function createCampaign(Request $request)
    {
        $this->requireAdmin();

        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'html_content' => 'required|string',
            'text_content' => 'nullable|string',
        ]);

        $campaign = $this->newsletterService->createCampaign(
            $data['subject'],
            $data['html_content'],
            $data['text_content'] ?? ''
        );

        return response()->json([
            'id' => $campaign->id,
            'subject' => $campaign->subject,
            'status' => $campaign->status,
            'total_recipients' => $campaign->total_recipients,
        ]);
    }

    public function sendCampaign(int $campaignId)
    {
        $this->requireAdmin();

        $campaign = EmailCampaign::findOrFail($campaignId);

        if ($campaign->status !== 'draft') {
            return response()->json(['error' => 'Campaign already sent or in progress.'], 422);
        }

        $this->newsletterService->sendCampaign($campaignId);

        return response()->json([
            'id' => $campaign->id,
            'status' => 'sending',
            'message' => 'Campaign sending started.',
        ]);
    }

    public function sendNextBatch(int $campaignId, Request $request)
    {
        $this->requireAdmin();

        $batchSize = $request->input('batch_size', 10);
        $result = $this->newsletterService->sendNextBatch($campaignId, $batchSize);

        return response()->json($result);
    }

    public function campaignStatus(int $campaignId)
    {
        $this->requireAdmin();

        $campaign = EmailCampaign::findOrFail($campaignId);

        return response()->json([
            'id' => $campaign->id,
            'subject' => $campaign->subject,
            'status' => $campaign->status,
            'total_recipients' => $campaign->total_recipients,
            'sent_count' => $campaign->sent_count,
            'progress' => $campaign->getProgressPercentage(),
        ]);
    }

    public function deleteCampaign(int $campaignId)
    {
        $this->requireAdmin();

        $campaign = EmailCampaign::findOrFail($campaignId);

        if ($campaign->status !== 'draft') {
            return response()->json(['error' => 'Cannot delete non-draft campaigns.'], 422);
        }

        $campaign->delete();

        return response()->json(['message' => 'Campaign deleted.']);
    }
}
