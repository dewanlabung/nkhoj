<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailWithOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $otp)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(__(':app सुरक्षा कोड: :code', ['app' => config('app.name'), 'code' => $this->otp]))
            ->greeting('नमस्ते ' . $notifiable->name . ',')
            ->line('तपाईंको इमेल प्रमाणीकरण कोड:')
            ->line('**' . $this->otp . '**')
            ->line('यो कोड 30 मिनेटमा समाप्त हुन्छ।')
            ->line('यदि तपाईंले यो अनुरोध गर्नुभएको छैन भने, यो सन्देशलाई बेवास्ता गर्नुहोस्।');
    }
}
