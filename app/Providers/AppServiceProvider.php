<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        
        if(env('APP_ENV') == 'production') {
            URL::forceScheme('https');

            //Cria as tabelas e suas atualizações
            $exitCode = Artisan::call('migrate', [
                '--force' => true,
            ]);
        }
    }
}
