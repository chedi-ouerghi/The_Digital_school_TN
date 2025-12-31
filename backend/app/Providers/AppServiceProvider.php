<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Cryptomoney;
use App\Policies\CryptmoneyPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Enregistrer les policies
     */
    protected $policies = [
        Cryptomoney::class => CryptmoneyPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enregistrer les policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}

