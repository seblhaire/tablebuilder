<?php

namespace Seblhaire\TableBuilder;

use Illuminate\Support\ServiceProvider;

class TableBuilderHelperServiceProvider extends ServiceProvider {

    protected $defer = true;

    public function boot() {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'tablebuilder');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'tablebuilder');
        $this->publishes([
            __DIR__ . '/../config/' => config_path('vendor/seblhaire'),
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/tablebuilder'),
            __DIR__ . '/../resources/js/tablebuilder.js' => resource_path('js/vendor/seblhaire/tablebuilder/tablebuilder.js'),
            __DIR__ . '/../resources/css/tablebuilder.scss' => resource_path('sass/vendor/seblhaire/tablebuilder/tablebuilder.scss')
        ]);
        $this->publishes([
            __DIR__ . '/../resources/js/tablebuilder.js' => public_path('js/vendor/seblhaire/tablebuilder/tablebuilder.js'),
            __DIR__ . '/../resources/css/tablebuilder.scss' => public_path('css/vendor/seblhaire/tablebuilder/tablebuilder.css')
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        $this->mergeConfigFrom(__DIR__ . '/../config/tablebuilder.php', 'tablebuilder');
        $this->app->singleton('TableBuilderHelperService', function ($app) {
            return new TableBuilderHelperService();
        });
    }

    public function provides() {
        return [
            TableBuilderHelperServiceContract::class
        ];
    }
}
