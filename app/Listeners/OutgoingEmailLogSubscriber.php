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

        // Guard: skip if already logged (prevents duplicate row on retry/double-fire)
        if ($msg->getHeaders()->has('X-NK-LOG-ID')) {
            return;
        }

        $toAddresses   = $msg->getTo();
        $fromAddresses = $msg->getFrom();
        $to   = !empty($toAddresses)   ? $toAddresses[0]->getAddress()   : '';
        $from = !empty($fromAddresses) ? $fromAddresses[0]->getAddress() : '';

        $log = OutgoingEmailLog::create([
            'message_id' => $msg->generateMessageId(),
            'from'       => $from,
            'to'         => $to,
            'subject'    => $msg->getSubject(),
            'mime'       => substr($msg->toString(), 0, 65535),
            'status'     => 'not-sent',
        ]);

        $msg->getHeaders()->addTextHeader('X-NK-LOG-ID', $log->id);
    }

    public function handleSent(MessageSent $event): void
    {
        $sentMsg  = $event->message;
        $original = method_exists($sentMsg, 'getOriginalMessage')
            ? $sentMsg->getOriginalMessage()
            : $sentMsg;

        if (!method_exists($original, 'getHeaders')) return;

        $header = $original->getHeaders()->get('X-NK-LOG-ID');
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
