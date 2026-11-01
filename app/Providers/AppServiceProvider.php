<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword; // 1. Tambahkan import ini

class AppServiceProvider extends ServiceProvider
{
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
        // 2. Timpa pengaturan link Reset Password default Laravel
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            // Arahkan langsung ke port Vue 3 Anda (misal: localhost:5173)
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            
            return $frontendUrl . '/reset-password?token=' . $token . '&email=' . urlencode($notifiable->email);
        });
    }
}