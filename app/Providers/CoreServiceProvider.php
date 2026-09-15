<?php

namespace App\Providers;

use App\Core\Contracts\OtpService as OtpServiceContract;
use App\Core\Contracts\BanService as BanServiceContract;
use App\Core\Services\Auth\OtpAuthService;
use App\Core\Services\Auth\BanService;
use App\Core\Services\Mail\MailService;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerAuthServices();
        $this->registerMailServices();
    }

    public function boot(): void
    {
        //
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
