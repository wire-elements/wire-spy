<?php

namespace WireElements\WireSpy;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Livewire\ComponentHook;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;

class SupportAutoInjectedAssets extends ComponentHook
{
    public static function provide()
    {
        Route::get('livewire/wire-spy.min.js', function () {
            return response(file_get_contents(base_path('vendor/wire-elements/wire-spy/dist/wire-spy.min.js')))
                ->header(key: 'content-type', values: 'application/javascript');
        });

        app('events')->listen(RequestHandled::class, function ($handled) {
            // If this is a successful HTML response...
            if (! str($handled->response->headers->get('content-type'))->contains('text/html')) {
                return;
            }
            if (! method_exists($handled->response, 'status') || $handled->response->status() !== 200) {
                return;
            }

            $assetsHead = '';
            $assetsBody = '';

            if (static::shouldInjectWireSpyAssets()) {
                $cacheId = json_decode(file_get_contents(base_path('vendor/wire-elements/wire-spy/dist/manifest.json')), true)['/wire-spy.min.js'];

                $assetsHead .= sprintf('<style>%s</style>', file_get_contents(base_path('vendor/wire-elements/wire-spy/dist/wire-spy.min.css')))."\n";
                $assetsBody .= sprintf('<script src="/livewire/wire-spy.min.js?id=%s"></script>', $cacheId)."\n";
                $assetsBody .= Blade::render('<div class="wire-spy"><livewire:wire-spy /></div>');
            }

            if ($assetsHead === '' && $assetsBody === '') {
                return;
            }

            $html = $handled->response->getContent();

            if (str($html)->contains('</html>')) {
                $originalContent = $handled->response->original;
                $handled->response->setContent(\Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::injectAssets($html, $assetsHead, $assetsBody));
                $handled->response->original = $originalContent;
            }
        });
    }

    protected static function shouldInjectWireSpyAssets()
    {
        if (\Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest) {
            return true;
        }

        if (app(FrontendAssets::class)->hasRenderedScripts) {
            return false;
        }

        return true;
    }
}
