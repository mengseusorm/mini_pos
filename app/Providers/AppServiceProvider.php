<?php

namespace App\Providers;

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Builder::defaultStringLength(191);

        $currency = Setting::currencySymbol();
        View::share('currency', $currency);
    }
}

