<?php

namespace App\Domains\Communication\Http\Controllers\Admin;

use App\Domains\Communication\Models\CommunicationCampaign;
use App\Domains\Communication\Models\CommunicationRecipient;
use App\Domains\Communication\Models\CommunicationTemplate;
use App\Domains\Communication\Models\CommunicationSegment;
use App\Domains\Communication\Services\CommunicationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CommunicationAdminController extends Controller
{
    protected $communicationService;

    public function __construct(CommunicationService $communicationService)
    {
        $this->communicationService = $communicationService;
    }

    public function index(Request $request)
    {
        $campaigns = CommunicationCampaign::query()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total_campaigns' => CommunicationCampaign::count(),
            'active_campaigns' => CommunicationCampaign::whereIn('status', ['sending', 'scheduled'])->count(),
            'total_sent' => CommunicationCampaign::sum('sent_count'),
            'avg_open_rate' => round(CommunicationCampaign::avg('open_rate'), 2),
        ];

        return view('admin.communications.index', compact('campaigns', 'stats'));
    }

    public function create()
    {
        $templates = CommunicationTemplate::all();
        $segments = CommunicationSegment::all();
        $channels = ['email', 'in_app', 'announcement', 'push'];
        $targetTypes = [
            'all_users' => 'All Users',
            'activated_users' => 'Activated Users',
            'inactive_users' => 'Inactive Users',
            'segment' => 'Custom Segment',
            'tag' => 'User Tags',
            'custom_list' => 'Custom User List',
            'newsletter_subscribers' => 'Newsletter Subscribers',
        ];

        return view('admin.communications.create', compact('templates', 'segments', 'channels', 'targetTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'html_content' => 'required|string',
            'text_content' => 'nullable|string',
            'channels' => 'required|array',
            'channels.*' => 'in:email,in_app,announcement,push',
            'target_type' => 'required|in:all_users,activated_users,inactive_users,segment,tag,custom_list,newsletter_subscribers',
            'target_config' => 'nullable|array',
            'schedule_type' => 'required|in:immediate,scheduled,recurring',
            'scheduled_at' => 'nullable|datetime',
            'recurrence_rule' => 'nullable|string',
            'announcement_type' => 'nullable|in:info,warning,success,promotion',
            'announcement_position' => 'nullable|in:top,bottom,modal,sidebar',
        ]);

        $campaign = $this->communicationService->createCampaign(
            $validated,
            auth()->id()
        );

        return redirect()->route('admin.communications.show', $campaign)
            ->with('success', 'Campaign created successfully');
    }

    public function show(CommunicationCampaign $campaign)
    {
        $campaign->load('recipients');
        $analytics = $this->communicationService->getAnalytics($campaign);
        $recipients = $campaign->recipients()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.communications.show', compact('campaign', 'analytics', 'recipients'));
    }

    public function edit(CommunicationCampaign $campaign)
    {
        if ($campaign->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft campaigns can be edited');
        }

        $templates = CommunicationTemplate::all();
        $segments = CommunicationSegment::all();
        $channels = ['email', 'in_app', 'announcement', 'push'];
        $targetTypes = [
            'all_users' => 'All Users',
            'activated_users' => 'Activated Users',
            'inactive_users' => 'Inactive Users',
            'segment' => 'Custom Segment',
            'tag' => 'User Tags',
            'custom_list' => 'Custom User List',
            'newsletter_subscribers' => 'Newsletter Subscribers',
        ];

        return view('admin.communications.edit', compact('campaign', 'templates', 'segments', 'channels', 'targetTypes'));
    }

    public function update(Request $request, CommunicationCampaign $campaign)
    {
        if ($campaign->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft campaigns can be edited');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'html_content' => 'required|string',
            'text_content' => 'nullable|string',
            'channels' => 'required|array',
            'target_type' => 'required|string',
            'schedule_type' => 'required|string',
        ]);

        $campaign->update($validated);

        return redirect()->route('admin.communications.show', $campaign)
            ->with('success', 'Campaign updated successfully');
    }

    public function delete(CommunicationCampaign $campaign)
    {
        if ($campaign->status !== 'draft') {
            return response()->json(['error' => 'Only draft campaigns can be deleted'], 400);
        }

        $campaign->delete();
        return response()->json(['success' => true]);
    }

    public function sendTest(Request $request, CommunicationCampaign $campaign)
    {
        $request->validate([
            'recipient_email' => 'required|email',
        ]);

        try {
            $user = \App\Models\User::where('email', $request->recipient_email)->first();

            if (!$user) {
                return back()->with('error', 'Recipient not found');
            }

            $this->communicationService->sendEmail(
                new CommunicationRecipient(['user_id' => $user->id]),
                $campaign,
                $user
            );

            return back()->with('success', 'Test email sent successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    public function launch(CommunicationCampaign $campaign)
    {
        try {
            if ($campaign->total_recipients === 0) {
                $this->communicationService->calculateRecipients($campaign);
            }

            $this->communicationService->startSending($campaign);

            return response()->json([
                'success' => true,
                'message' => 'Campaign launched successfully',
                'campaign' => $campaign,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function pause(CommunicationCampaign $campaign)
    {
        if ($campaign->status !== 'sending') {
            return response()->json(['error' => 'Only sending campaigns can be paused'], 400);
        }

        $campaign->pause();
        return response()->json(['success' => true, 'message' => 'Campaign paused']);
    }

    public function resume(CommunicationCampaign $campaign)
    {
        if ($campaign->status !== 'paused') {
            return response()->json(['error' => 'Only paused campaigns can be resumed'], 400);
        }

        $campaign->resume();
        return response()->json(['success' => true, 'message' => 'Campaign resumed']);
    }

    public function cancel(CommunicationCampaign $campaign)
    {
        if (!in_array($campaign->status, ['draft', 'scheduled', 'sending', 'paused'])) {
            return response()->json(['error' => 'Campaign cannot be cancelled'], 400);
        }

        $campaign->cancel();
        return response()->json(['success' => true, 'message' => 'Campaign cancelled']);
    }

    public function getRecipientCount(Request $request)
    {
        $targetType = $request->get('target_type');
        $targetConfig = $request->get('target_config', []);

        $campaign = new CommunicationCampaign([
            'target_type' => $targetType,
            'target_config' => $targetConfig,
        ]);

        try {
            $recipients = $this->communicationService->getTargetedRecipients($campaign);
            $count = $recipients->count();

            return response()->json([
                'success' => true,
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
