<?php

namespace App\Domains\Support\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'subject' => 'nullable|string|max:200',
            'body'    => 'required|string|max:5000',
        ]);

        ContactMessage::create($data);

        return back()->with('success', 'Your message has been sent! We will reply within 1-2 business days.');
    }

    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:150',
            'name'  => 'nullable|string|max:100',
        ]);

        NewsletterSubscriber::firstOrCreate(
            ['email' => $data['email']],
            ['name' => $data['name'] ?? null, 'token' => Str::random(64), 'is_active' => true]
        );

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Subscribed!']);
        }
        return back()->with('success', 'Subscribed to newsletter!');
    }

    public function unsubscribe(string $token)
    {
        $sub = NewsletterSubscriber::where('token', $token)->first();
        if ($sub) $sub->update(['is_active' => false]);
        return view('newsletter.unsubscribed');
    }
}
