<x-mail::message>
# Welcome to नखोज Newsletter! 🎉

Hi {{ $subscriber->name ?? 'there' }},

Thank you for subscribing to our newsletter! You'll now receive curated नेपाली news, stories, and updates directly to your inbox.

<x-mail::panel>
You're all set! Expect to receive your first newsletter within the next few days. We send updates 2-3 times per week.
</x-mail::panel>

## What to expect:

- 📰 **Breaking News**: Latest stories from नेपाल
- 📚 **Curated Selection**: Hand-picked articles based on trending topics
- ✨ **Exclusive Content**: Stories and insights you won't find elsewhere

## Manage your subscription

You can update your preferences or unsubscribe anytime by clicking the link at the bottom of future emails.

Thanks for joining our community!

Warm regards,
{{ config('app.name') }} Team

---

*You received this email because you subscribed to our newsletter. [Unsubscribe]({{ url('/newsletter/unsubscribe/' . $subscriber->token) }})*
</x-mail::message>
