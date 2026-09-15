<?php

namespace App\Notifications;

use App\Models\Notifications\NotificationPreference;
use App\Models\UserEngagement\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Abstract base for structured nkhoj notifications.
 *
 * Inspired by BeDesk's TicketingNotification: extending classes declare
 * notifType() and the via() method automatically routes to the channels
 * the user has enabled in notification_preferences.
 *
 * Usage:
 *   class CommentNotification extends BaseNotification {
 *       public function notifType(): string { return 'comment'; }
 *       protected function subject(User $notifiable): string { ... }
 *       protected function body(User $notifiable): string { ... }
 *       protected function actionUrl(): ?string { ... }
 *   }
 *   $user->notify(new CommentNotification(...));
 */
abstract class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    abstract public function notifType(): string;
    abstract protected function subject(User $notifiable): string;
    abstract protected function body(User $notifiable): string;

    protected function actionUrl(): ?string
    {
        return null;
    }

    protected function actionLabel(): string
    {
        return 'View';
    }

    public function via(User $notifiable): array
    {
        $pref = NotificationPreference::where('user_id', $notifiable->id)
            ->where('type', $this->notifType())
            ->first();

        $channels = [];

        if (!$pref || $pref->in_app) {
            $channels[] = 'database';
        }

        if ($pref?->email) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(User $notifiable): MailMessage
    {
        $msg = (new MailMessage())
            ->subject($this->subject($notifiable))
            ->line($this->body($notifiable));

        if ($url = $this->actionUrl()) {
            $msg->action($this->actionLabel(), $url);
        }

        return $msg;
    }

    public function toArray(User $notifiable): array
    {
        return [
            'type'    => $this->notifType(),
            'subject' => $this->subject($notifiable),
            'body'    => $this->body($notifiable),
            'url'     => $this->actionUrl(),
        ];
    }
}
