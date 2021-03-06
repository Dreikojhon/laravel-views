<?php

namespace LaravelViews;

use LaravelViews\Console\ActionMakeCommand;
use LaravelViews\Console\FilterMakeCommand;
use LaravelViews\Console\TableViewMakeCommand;
use LaravelViews\Data\Contracts\Filterable;
use LaravelViews\Data\Contracts\Searchable;
use LaravelViews\Data\TableViewFilterData;
use LaravelViews\Data\TableViewSearchData;
use LaravelViews\UI\UI;
use LaravelViews\UI\Variants;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class LaravelViewsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $file =  __DIR__ . '/helpers.php';
        if (file_exists($file)) {
            require_once($file);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(Searchable::class, TableViewSearchData::class);
        $this->app->bind(Filterable::class, TableViewFilterData::class);
        $this->app->bind('laravel-views', function () {
            return new LaravelViews();
        });
        $this->app->bind('variants', function () {
            return new Variants;
        });
        $this->app->bind('ui', function () {
            return new UI;
        });

        $this->loadViews()
            ->loadCommands()
            ->publish()
            ->bladeDirectives()
            ->configFiltes();
    }

    private function publish()
    {
        $this->publishes([
            __DIR__.'/../public/laravel-views.js' => public_path('vendor/laravel-views.js'),
            __DIR__.'/../public/laravel-views.css' => public_path('vendor/laravel-views.css'),
        ], 'public');

        $this->publishes([
            __DIR__.'/config/laravel-views.php' => config_path('laravel-views.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/views/components' => resource_path('views/vendor/laravel-views/components'),
            __DIR__.'/../resources/views/table-view' => resource_path('views/vendor/laravel-views/table-view'),
        ], 'views');

        return $this;
    }

    private function loadViews()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-views');

        return $this;
    }

    private function loadCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                FilterMakeCommand::class,
                ActionMakeCommand::class,
                TableViewMakeCommand::class
            ]);
        }

        return $this;
    }

    private function bladeDirectives()
    {
        $laravelViews = new LaravelViews;
        Blade::directive('laravelViewsStyles', function () use ($laravelViews) {
            return $laravelViews->css();
        });

        Blade::directive('laravelViewsScripts', function () use ($laravelViews) {
            return $laravelViews->js();
        });

        return $this;
    }

    private function configFiltes()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/laravel-views.php', 'laravel-views');

        return $this;
    }
}
