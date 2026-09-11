<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventAttendee;
use App\Traits\SavesOptimizedThumbnail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    use SavesOptimizedThumbnail;
    public function index(Request $request)
    {
        $tab      = $request->query('tab', 'upcoming');
        $category = $request->query('category');
        $type     = $request->query('type');
        $search   = $request->query('q');

        $query = Event::where('is_published', true)->with('organizer');

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }
        if ($category) {
            $query->where('category', $category);
        }
        if ($type) {
            $query->where('event_type', $type);
        }

        $query = match($tab) {
            'past'     => $query->where('starts_at', '<', now())->latest('starts_at'),
            'featured' => $query->where('is_featured', true)->orderBy('starts_at'),
            default    => $query->where('starts_at', '>=', now())->orderBy('starts_at'),
        };

        $events   = $query->paginate(12)->withQueryString();
        $featured = Event::where('is_published', true)->where('is_featured', true)
                         ->where('starts_at', '>=', now())->orderBy('starts_at')->limit(3)->get();

        $categories = ['Music', 'Technology', 'Sports', 'Arts', 'Food', 'Business', 'Education', 'Health', 'Community', 'Travel', 'Fashion', 'Film'];
        $types      = ['in-person', 'online', 'hybrid'];

        return view('events.index', compact('events', 'featured', 'tab', 'category', 'type', 'categories', 'types', 'search'));
    }

    public function show(string $slug)
    {
        $event     = Event::where('slug', $slug)->where('is_published', true)->with('organizer')->firstOrFail();
        $isGoing   = auth()->check() && $event->isAttendingBy(auth()->user(), 'going');
        $interested = auth()->check() && $event->isAttendingBy(auth()->user(), 'interested');
        $related   = Event::where('is_published', true)
            ->where('id', '!=', $event->id)
            ->where(fn($q) => $q->where('category', $event->category)->orWhere('event_type', $event->event_type))
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')->limit(4)->get();

        return view('events.show', compact('event', 'isGoing', 'interested', 'related'));
    }

    public function create()
    {
        return view('events.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:200',
            'description'      => 'nullable|string|max:5000',
            'category'         => 'nullable|string|max:100',
            'event_type'       => 'required|in:in-person,online,hybrid',
            'starts_at'        => 'required|date',
            'ends_at'          => 'nullable|date|after:starts_at',
            'venue'            => 'nullable|string|max:200',
            'location'         => 'nullable|string|max:300',
            'organizer'        => 'nullable|string|max:200',
            'registration_url' => 'nullable|url|max:500',
            'ticket_price'     => 'nullable|numeric|min:0',
            'thumbnail'        => 'nullable|image|max:4096',
        ]);

        $slug = Str::slug($data['title']);
        $base = $slug ?: 'event'; $i = 1;
        while (Event::where('slug', $slug)->exists()) { $slug = "{$base}-{$i}"; $i++; }

        $thumbnailUrl = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailUrl = $this->saveOptimizedThumbnail($request->file('thumbnail'), 'events');
        }

        $event = Event::create([
            'uuid'             => Str::uuid(),
            'user_id'          => auth()->id(),
            'title'            => $data['title'],
            'slug'             => $slug,
            'description'      => $data['description'],
            'category'         => $data['category'],
            'event_type'       => $data['event_type'],
            'starts_at'        => $data['starts_at'],
            'ends_at'          => $data['ends_at'] ?? null,
            'venue'            => $data['venue'] ?? null,
            'location'         => $data['location'] ?? null,
            'organizer'        => $data['organizer'] ?? auth()->user()->name,
            'registration_url' => $data['registration_url'] ?? null,
            'ticket_price'     => $data['ticket_price'] ?? null,
            'thumbnail_url'    => $thumbnailUrl,
        ]);

        return redirect("/events/{$event->slug}")->with('success', 'Event created!');
    }

    public function exportAttendees(Event $event)
    {
        abort_unless(auth()->id() === $event->user_id, 403);

        $attendees = EventAttendee::where('event_id', $event->id)
            ->with('user:id,name,email')
            ->orderBy('created_at')
            ->get();

        $filename = 'attendees-' . $event->slug . '-' . now()->format('Ymd') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($attendees) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Email', 'Status', 'Registered At']);
            foreach ($attendees as $a) {
                fputcsv($out, [
                    $a->user->name ?? 'Unknown',
                    $a->user->email ?? '',
                    $a->status,
                    $a->created_at->toDateTimeString(),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function attend(Event $event, Request $request)
    {
        $userId = auth()->id();
        $status = $request->input('status', 'going');

        $existing = EventAttendee::where('event_id', $event->id)->where('user_id', $userId)->first();

        if ($existing && $existing->status === $status) {
            $existing->delete();
            $col = $status === 'going' ? 'going_count' : 'interested_count';
            $event->decrement($col);
            $attending = false;
        } else {
            if ($existing) {
                $oldCol = $existing->status === 'going' ? 'going_count' : 'interested_count';
                $event->decrement($oldCol);
                $existing->update(['status' => $status]);
            } else {
                EventAttendee::create(['event_id' => $event->id, 'user_id' => $userId, 'status' => $status]);
            }
            $col = $status === 'going' ? 'going_count' : 'interested_count';
            $event->increment($col);
            $attending = true;
        }

        return response()->json([
            'attending'        => $attending,
            'status'           => $status,
            'going_count'      => $event->fresh()->going_count,
            'interested_count' => $event->fresh()->interested_count,
        ]);
    }
}
