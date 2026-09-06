<?php

namespace App\Providers;

use App\Services\AI\OrchestratorAgent;
use Illuminate\Support\ServiceProvider;

class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OrchestratorAgent::class, fn() => new OrchestratorAgent());

        $this->mergeConfigFrom(__DIR__ . '/../../config/omniroute.php', 'omniroute');
    }

    public function boot(): void
    {
        //
    }
}
