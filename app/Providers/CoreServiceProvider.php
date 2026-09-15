<?php

namespace App\Providers;

use App\Core\Contracts\OtpService as OtpServiceContract;
use App\Core\Contracts\BanService as BanServiceContract;
use App\Core\Services\Auth\OtpAuthService;
use App\Core\Services\Auth\BanService;
use App\Core\Services\Mail\MailService;
use App\Core\Events\CommentReplyCreated;
use App\Core\Listeners\SendCommentReplyNotification;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerAuthServices();
        $this->registerMailServices();
    }

    public function boot(): void
    {
        Event::listen(
            CommentReplyCreated::class,
            SendCommentReplyNotification::class
        );
    }

    private function registerAuthServices(): void
    {
        $this->app->singleton(OtpServiceContract::class, OtpAuthService::class);
        $this->app->singleton(BanServiceContract::class, BanService::class);
    }

    private function registerMailServices(): void
    {
        $this->app->singleton(MailService::class, function () {
            return new MailService();
        });
    }
}
