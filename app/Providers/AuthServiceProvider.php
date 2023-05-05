<?php

namespace App\Providers;

use App\Services\Auth\JwtAuthGuard;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Auth::extend('jwt', function ($app, $name, array $config) {
            return new JwtAuthGuard(
                Auth::createUserProvider($config['provider']),
                $app->make('request')
            );
        });
    }
}
