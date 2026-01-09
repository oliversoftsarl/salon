<?php

namespace App\Providers;

use App\Models\ExchangeRate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;

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
        Schema::defaultStringLength(100);
        Paginator::useTailwind();

        // Partager le taux de change actuel avec toutes les vues
        View::composer('*', function ($view) {
            $view->with('currentExchangeRate', ExchangeRate::getCurrentRate());
        });

        // Directive Blade pour formater en FC
        Blade::directive('priceCdf', function ($amount) {
            return "<?php echo number_format($amount, 0, ',', ' ') . ' FC'; ?>";
        });

        // Directive Blade pour formater en USD
        Blade::directive('priceUsd', function ($amount) {
            return "<?php echo '$ ' . number_format($amount, 2, ',', ' '); ?>";
        });

        // Directive Blade pour afficher FC avec équivalent USD
        Blade::directive('priceWithUsd', function ($amount) {
            return "<?php
                \$rate = \\App\\Models\\ExchangeRate::getCurrentRateValue();
                \$usd = \$rate > 0 ? ($amount) / \$rate : 0;
                echo number_format($amount, 0, ',', ' ') . ' FC <small class=\"text-muted\">($ ' . number_format(\$usd, 2, ',', ' ') . ')</small>';
            ?>";
        });
    }
}
