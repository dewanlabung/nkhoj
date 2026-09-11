<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::where('is_published', true)
            ->with('organizer:id,name,username')
            ->select('id', 'title', 'slug', 'description', 'thumbnail_url', 'category', 'event_type', 'starts_at', 'ends_at', 'venue', 'location', 'going_count', 'interested_count', 'user_id');

        if ($s = $request->query('q')) {
            $query->where('title', 'like', "%{$s}%");
        }
        if ($cat = $request->query('category')) {
            $query->where('category', $cat);
        }
        if ($request->query('upcoming', true)) {
            $query->where('starts_at', '>=', now());
        }

        $events = $query->orderBy('starts_at')->paginate(20)->withQueryString();

        return response()->json([
            'data' => $events->items(),
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page'    => $events->lastPage(),
                'per_page'     => $events->perPage(),
                'total'        => $events->total(),
            ],
        ]);
    }

    public function show(string $slug)
    {
        $event = Event::where('slug', $slug)->where('is_published', true)
            ->with('organizer:id,name,username')
            ->firstOrFail();

        return response()->json(['data' => $event]);
    }
}
