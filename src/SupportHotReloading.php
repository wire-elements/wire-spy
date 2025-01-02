<?php

namespace WireElements\WireSpy;

use Illuminate\Support\Facades\File;
use Livewire\Component;
use Livewire\ComponentHook;
use function Livewire\on;
use ReflectionObject;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupportHotReloading extends ComponentHook
{
    protected array $pathsByComponentId = [];

    protected string $cachePath = 'framework/cache/wire-spy-hot-reload.json';

    public function boot()
    {
        // To support hot reloading we need to track the view cache files
        on('view:compile', function (Component $component, $viewPath) {
            // Per component, we build an array to hold the view path
            if (! isset($this->pathsByComponentId[$component->getId()])) {
                $this->pathsByComponentId[$component->getId()] = [];
            }

            // check if path isn't already in the array we will track it
            if (in_array($viewPath, $this->pathsByComponentId, true) === false) {
                $this->pathsByComponentId[$component->getId()][] = $viewPath;
            }
        });

        on('dehydrate', function ($component, $context) {
            if (! $context->mounting) {
                return;
            }

            $cache = $this->getCache();
            $componentName = $component->getName();

            // Let's grab the component filename
            $componentFilePath = (new ReflectionObject($component))->getFileName();

            // Next we create a new array if needed
            if (! isset($cache[$componentFilePath])) {
                $cache[$componentFilePath] = [];
            }

            // Finally we associate the component file with the component name
            if (! in_array($componentName, $cache[$componentFilePath], true)) {
                $cache[$componentFilePath][] = $componentName;
            }

            // Add the component views
            foreach ($this->pathsByComponentId[$component->getId()] ?? [] as $componentViewFilePath) {
                if (! isset($cache[$componentViewFilePath])) {
                    $cache[$componentViewFilePath] = [];
                }
                if (! in_array($componentName, $cache[$componentViewFilePath], true)) {
                    $cache[$componentViewFilePath][] = $componentName;
                }
            }

            $this->putCache($cache);
        });
    }

    public function route(): StreamedResponse
    {
        // To prevent timeouts we are turning it off
        set_time_limit(0);

        return response()->stream(function () {
            $lastPing = null;
            $filesByTime = [];

            while (true) {
                // Break the loop if the client aborted the connection
                if (connection_aborted()) {
                    break;
                }

                // Get all the component names and associated files
                $componentNamesByPath = $this->getCache();

                foreach ($componentNamesByPath as $file => $names) {
                    $file = (string) $file;

                    // In case the file doesn't exist anymore we remove it from the cache and continue
                    if (! file_exists($file)) {
                        $this->removeFileFromCache($file);

                        continue;
                    }

                    $time = filemtime($file);

                    if (isset($filesByTime[$file]) && $filesByTime[$file] !== $time) {
                        echo $this->fileChangedResponse(
                            file: $file,
                            componentNames: $names
                        );

                        $this->flush();

                        $lastPing = microtime(true);
                    }

                    $filesByTime[$file] = $time;
                }

                usleep(250000);

                // Every 5 seconds we send a ping to keep the connection alive
                if (! $lastPing || microtime(true) > ($lastPing + 5)) {
                    echo $this->pingResponse(
                        fileCount: count($filesByTime)
                    );

                    $this->flush();

                    $lastPing = microtime(true);
                }
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    protected function removeFileFromCache($file)
    {
        $cache = $this->getCache();

        unset($cache[$file]);

        $this->putCache($cache);
    }

    protected function getCache()
    {
        $path = storage_path($this->cachePath);

        // Create file if it doesn't exist.
        if (! File::exists($path)) {
            File::put($path, json_encode([]));
        }

        return json_decode(File::get($path), true);
    }

    protected function putCache($cache): void
    {
        File::put(storage_path($this->cachePath), json_encode($cache));
    }

    protected function pingResponse($fileCount): string
    {
        return $this->formatSSEResponse([
            'ping' => true,
            'files' => $fileCount,
        ]);
    }

    protected function fileChangedResponse(string $file, array $componentNames): string
    {
        // Strip path
        $file = str_replace(base_path(), '', $file);

        return $this->formatSSEResponse(['file' => $file, 'components' => $componentNames]);
    }

    protected function formatSSEResponse($array): string
    {
        return 'data: '.json_encode($array)."\n\n";
    }

    protected function flush(): void
    {
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();
    }
}
