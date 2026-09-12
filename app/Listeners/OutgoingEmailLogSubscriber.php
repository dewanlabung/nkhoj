<?php

namespace App\Listeners;

use App\Models\OutgoingEmailLog;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;

class OutgoingEmailLogSubscriber
{
    public function handleSending(MessageSending $event): void
    {
        $msg = $event->message;

        $to = collect($msg->getTo())->keys()->first() ?? '';
        $from = collect($msg->getFrom())->keys()->first() ?? '';

        $log = OutgoingEmailLog::create([
            'message_id' => $msg->generateMessageId(),
            'from'       => $from,
            'to'         => $to,
            'subject'    => $msg->getSubject(),
            'mime'       => substr($msg->toString(), 0, 65535),
            'status'     => 'not-sent',
        ]);

        // Attach log ID to message headers so we can match on sent event
        $msg->getHeaders()->addTextHeader('X-NK-LOG-ID', $log->id);
    }

    public function handleSent(MessageSent $event): void
    {
        $msg = $event->message;

        $header = $msg->getHeaders()->get('X-NK-LOG-ID');
        if (!$header) return;

        $id = (int) $header->getBodyAsString();
        OutgoingEmailLog::where('id', $id)->update(['status' => 'sent']);
    }

    public function subscribe($events): array
    {
        return [
            MessageSending::class => 'handleSending',
            MessageSent::class    => 'handleSent',
        ];
    }
}
