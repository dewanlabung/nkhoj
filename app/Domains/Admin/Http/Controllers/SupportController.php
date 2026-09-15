<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Models\SupportReply;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;

class SupportController extends BaseAdminController
{
    public function supportTickets(Request $request)
    {
        $this->requireAdmin();
        $status = $request->query('status', 'all');

        $query = SupportTicket::with(['requester', 'agent'])->withCount('replies');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $tickets = $query->latest()->paginate(20);

        $counts = [
            'all'         => SupportTicket::count(),
            'open'        => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'pending'     => SupportTicket::where('status', 'pending')->count(),
            'solved'      => SupportTicket::where('status', 'solved')->count(),
            'closed'      => SupportTicket::where('status', 'closed')->count(),
        ];

        $open = $counts['open'];

        return view('admin.support', compact('tickets', 'counts', 'open', 'status'))->with('currentStatus', $status);
    }

    public function supportShow(SupportTicket $ticket)
    {
        $this->requireAdmin();
        $ticket->load(['requester', 'agent', 'replies.user']);
        $agents = User::whereIn('role', ['admin', 'moderator'])->orderBy('name')->get();

        return view('admin.support-show', compact('ticket', 'agents'));
    }

    public function supportReply(Request $request, SupportTicket $ticket)
    {
        $this->requireAdmin();
        $request->validate(['body' => 'required|string|max:5000']);

        SupportReply::create([
            'ticket_id' => $ticket->id,
            'user_id'   => auth()->id(),
            'body'      => $request->body,
            'is_staff'  => true,
        ]);

        if ($request->input('action') === 'reply_solve') {
            $ticket->update(['status' => 'solved']);
        } elseif ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Reply sent.');
    }

    public function supportAssign(Request $request, SupportTicket $ticket)
    {
        $this->requireAdmin();
        $ticket->update(['agent_id' => $request->input('agent_id') ?: null]);

        return back()->with('success', 'Agent updated.');
    }

    public function supportStatus(Request $request, SupportTicket $ticket)
    {
        $this->requireAdmin();
        $request->validate(['status' => 'required|in:open,pending,in_progress,solved,closed']);
        $ticket->update(['status' => $request->status]);

        return back()->with('success', 'Status updated.');
    }

    public function supportDelete(SupportTicket $ticket)
    {
        $this->requireAdmin();
        $ticket->delete();

        return redirect('/admin/support')->with('success', 'Ticket deleted.');
    }
}
