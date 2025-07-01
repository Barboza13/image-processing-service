<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;

class ImageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ImageManager::class, function ($app) {
            $driver = config('image.driver', 'gd');

            return new ImageManager(
                $driver === 'imagick' && extension_loaded('imagick')
                    ? new ImagickDriver()
                    : new GdDriver()
            );
        });

        // Registrar alias para facilitar el uso
        $this->app->alias(ImageManager::class, 'image');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
