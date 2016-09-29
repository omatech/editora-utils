<?php

namespace Omatech\Editora\Utils;

use Illuminate\Support\ServiceProvider;
use Omatech\Editora\Utils\Editora;

class EditoraUtilsServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     * @var bool
     */
    protected $defer = false;


    /**
     * Bootstrap the application services.
     * @return void
     */
    public function boot()
    {
        $this->app['conn_doctrine'] = [
            'dbname' => env('DB_DATABASE', 'forge'),
            'dbuser' => env('DB_USERNAME', 'forge'),
            'dbpass' => env('DB_PASSWORD', ''),
            'dbhost' => env('DB_HOST', 'localhost'),
        ];

    }

    /**
     * Register the application services.
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Editora', function ($app) {
            return new Editora($app['conn_doctrine']);
        });
    }
}

