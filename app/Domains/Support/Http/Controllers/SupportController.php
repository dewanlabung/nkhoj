<?php

namespace App\Domains\Support\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\SupportTicket;
use App\Models\SupportReply;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query  = SupportTicket::with(['requester', 'agent', 'replies'])
            ->where('requester_id', auth()->id());

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $tickets = $query->latest()->paginate(15);

        $counts = [
            'all'         => SupportTicket::where('requester_id', auth()->id())->count(),
            'open'        => SupportTicket::where('requester_id', auth()->id())->where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('requester_id', auth()->id())->where('status', 'in_progress')->count(),
            'pending'     => SupportTicket::where('requester_id', auth()->id())->where('status', 'pending')->count(),
            'solved'      => SupportTicket::where('requester_id', auth()->id())->where('status', 'solved')->count(),
            'closed'      => SupportTicket::where('requester_id', auth()->id())->where('status', 'closed')->count(),
        ];

        return view('support.index', compact('tickets', 'counts', 'status'));
    }

    public function create()
    {
        return view('support.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body'    => 'required|string|max:5000',
            'priority'=> 'in:low,normal,high,urgent',
        ]);

        $ticket = SupportTicket::create([
            'requester_id' => auth()->id(),
            'subject'      => $request->subject,
            'body'         => $request->body,
            'priority'     => $request->input('priority', 'normal'),
            'status'       => 'open',
        ]);

        return redirect("/support/{$ticket->id}")->with('success', 'Ticket submitted successfully.');
    }

    public function show(SupportTicket $ticket)
    {
        if ($ticket->requester_id !== auth()->id() && !auth()->user()->isAdmin() && !auth()->user()->isMod()) {
            abort(403);
        }

        $ticket->load(['requester', 'agent', 'replies.user']);

        return view('support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        if ($ticket->requester_id !== auth()->id() && !auth()->user()->isAdmin() && !auth()->user()->isMod()) {
            abort(403);
        }

        $request->validate(['body' => 'required|string|max:5000']);

        $isStaff = auth()->user()->isAdmin() || auth()->user()->isMod();

        SupportReply::create([
            'ticket_id' => $ticket->id,
            'user_id'   => auth()->id(),
            'body'      => $request->body,
            'is_staff'  => $isStaff,
        ]);

        if ($isStaff && $ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Reply posted.');
    }

    public function close(SupportTicket $ticket)
    {
        if ($ticket->requester_id !== auth()->id()) {
            abort(403);
        }

        $ticket->update(['status' => 'closed']);

        return back()->with('success', 'Ticket closed.');
    }
}
