<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\ContentReport;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ContentReportController extends Controller
{
    public function store(Request $request)
    {
        if (!Schema::hasTable('content_reports')) {
            return response()->json(['message' => 'Reporting not available yet.'], 503);
        }

        $request->validate([
            'type'    => 'required|in:post,comment',
            'id'      => 'required|integer',
            'reason'  => 'required|in:spam,misinformation,hate_speech,violence,copyright,other',
            'details' => 'nullable|string|max:500',
        ]);

        $type = $request->input('type') === 'post' ? Post::class : Comment::class;

        $existing = ContentReport::where([
            'reporter_id'     => auth()->id(),
            'reportable_type' => $type,
            'reportable_id'   => $request->id,
        ])->exists();

        if ($existing) {
            return response()->json(['message' => 'You have already reported this content.'], 409);
        }

        ContentReport::create([
            'reporter_id'     => auth()->id(),
            'reportable_type' => $type,
            'reportable_id'   => $request->id,
            'reason'          => $request->reason,
            'details'         => $request->details,
        ]);

        return response()->json(['message' => 'Report submitted. Thank you.']);
    }

    // ── Admin ──────────────────────────────────────────────────────────────

    public function adminIndex(Request $request)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $status = $request->get('status', 'pending');
        $reports = ContentReport::with('reporter')
            ->where('status', $status)
            ->latest()
            ->paginate(20);

        $counts = [
            'pending'   => ContentReport::where('status', 'pending')->count(),
            'reviewed'  => ContentReport::where('status', 'reviewed')->count(),
            'dismissed' => ContentReport::where('status', 'dismissed')->count(),
        ];

        return view('admin.reports', compact('reports', 'status', 'counts'));
    }

    public function adminAction(Request $request, int $id)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $request->validate([
            'action'     => 'required|in:reviewed,dismissed',
            'admin_note' => 'nullable|string|max:500',
        ]);

        ContentReport::findOrFail($id)->update([
            'status'      => $request->action,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_note'  => $request->admin_note,
        ]);

        return back()->with('success', 'Report ' . $request->action . '.');
    }
}
