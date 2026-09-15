<?php

namespace App\Core\Notifications;

use App\Models\UserEngagement\User;
use Illuminate\Support\Arr;

class SystemErrorNotification extends BaseNotification
{
    public const NOTIF_ID = 'system_error';

    public function __construct(
        public string $errorType,
        public string $errorMessage,
        public ?array $context = null,
    ) {}

    public function via(User $notifiable): array
    {
        // System errors only go to database for admins
        if ($notifiable->hasPermission('admin.view_system_errors')) {
            return ['database'];
        }
        return [];
    }

    public function notificationType(): string
    {
        return 'system_error';
    }

    protected function subject(User $notifiable): string
    {
        return "System Error: {$this->errorType}";
    }

    protected function body(User $notifiable): string
    {
        return Str::limit($this->errorMessage, 200);
    }

    protected function icon(): ?string
    {
        return 'alert-circle';
    }

    public function toArray(User $notifiable): array
    {
        return array_merge(parent::toArray($notifiable), [
            'error_type' => $this->errorType,
            'error_message' => $this->errorMessage,
            'context' => $this->context,
            'severity' => 'error',
        ]);
    }
}
