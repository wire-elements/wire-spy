<?php

namespace WireElements\WireSpy;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class WireSpyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerLivewireComponent();
        $this->registerBladeViews();
        $this->registerLivewireComponentHooks();
        $this->registerHotReloadRoute();
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
