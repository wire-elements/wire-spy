<?php

namespace WireElements\WireSpy;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class WireSpyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerPackageConfig();
        $this->registerLivewireComponent();
        $this->registerBladeViews();
        $this->registerLivewireComponentHooks();
        $this->registerHotReloadRoute();
    }

    private function registerPackageConfig(): void
    {
        $this->registerConfig(__DIR__.'/config/wire-spy.php', 'wire-spy');
        $this->publishes([__DIR__.'/config/wire-spy.php' => config_path('wire-spy.php'),], 'wire-spy-config');
    }

    private function registerLivewireComponent(): void
    {
        Livewire::component('wire-spy', WireSpy::class);
    }

    private function registerBladeViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'wire-spy');
    }

    private function registerLivewireComponentHooks(): void
    {
        app('livewire')->componentHook(SupportHotReloading::class);
        app('livewire')->componentHook(SupportAutoInjectedAssets::class);
    }

    private function registerHotReloadRoute(): void
    {
        Route::get('/wire-spy/hot-reload', [SupportHotReloading::class, 'route']);
    }
}
