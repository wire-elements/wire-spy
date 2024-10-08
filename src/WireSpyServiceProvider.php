<?php

namespace WireElements\WireSpy;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class WireSpyServiceProvider extends ServiceProvider
{
    public function register()
    {
        Livewire::component('wire-spy', WireSpy::class);
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'wire-spy');

        app('livewire')->componentHook(SupportProfilling::class);
        app('livewire')->componentHook(SupportHotReloading::class);
        app('livewire')->componentHook(SupportAutoInjectedAssets::class);

        Route::get('/wire-spy/hot-reload', [SupportHotReloading::class, 'route']);
    }
}
