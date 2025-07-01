<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class TestImageProcessing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test image processing functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Crear una imagen de prueba
            $manager = new ImageManager(new Driver());
            $image = $manager->create(300, 200)->fill('ff0000');

            // Aplicar algunos efectos
            $image->text('Test Image', 10, 100, function ($font) {
                $font->size(20);
                $font->color('ffffff');
            });

            // Guardar
            $path = storage_path('app/test_image.jpg');
            $image->toJpeg(90)->save($path);

            $this->info('✅ Image processing is working correctly!');
            $this->info("Test image saved at: {$path}");
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
        }
    }
}
