<?php

namespace App\Providers;

use App\Services\InvoiceOcr\InvoiceOcrService;
use App\Services\InvoiceOcr\InvoiceOcrServiceInterface;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(InvoiceOcrServiceInterface::class, fn () => InvoiceOcrService::fromConfig());
        $this->app->singleton(InvoiceOcrService::class, fn () => InvoiceOcrService::fromConfig());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // MySQL (utf8mb4) max key length is 1000 bytes; 191 chars * 4 = 764 bytes.
        Schema::defaultStringLength(191);
    }
}
