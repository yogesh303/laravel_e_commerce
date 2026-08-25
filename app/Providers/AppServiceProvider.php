<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
        try {
            if (Schema::hasTable('settings')) {
                config([
                    'services.stripe.key'    => Setting::get('stripe_key', config('services.stripe.key')),
                    'services.stripe.secret' => Setting::get('stripe_secret', config('services.stripe.secret')),

                    'services.razorpay.key'    => Setting::get('razorpay_key', config('services.razorpay.key')),
                    'services.razorpay.secret' => Setting::get('razorpay_secret', config('services.razorpay.secret')),

                    'mail.mailers.smtp.host'     => Setting::get('mail_host', config('mail.mailers.smtp.host')),
                    'mail.mailers.smtp.port'     => Setting::get('mail_port', config('mail.mailers.smtp.port')),
                    'mail.mailers.smtp.username' => Setting::get('mail_username', config('mail.mailers.smtp.username')),
                    'mail.mailers.smtp.password' => Setting::get('mail_password', config('mail.mailers.smtp.password')),
                    'mail.from.address'          => Setting::get('mail_from_address', config('mail.from.address')),
                    'mail.from.name'             => Setting::get('mail_from_name', config('mail.from.name')),
                ]);
            }
        } catch (\Throwable $e) {
            // DB not reachable yet (e.g. fresh install before migrate) — ignore and fall back to .env
        }
    }
}