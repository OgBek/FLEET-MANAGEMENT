<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Illuminate\Support\Facades\Storage;

class CreateDefaultVehicleImage extends Command
{
    protected $signature = 'vehicle:create-default-image';
    protected $description = 'Create a default vehicle image placeholder';

    public function handle()
    {
        $manager = new ImageManager(new Driver());
        
        // Create a 800x600 image with a light gray background
        $img = $manager->create(800, 600, function ($canvas) {
            $canvas->background('#f3f4f6');
        });

        // Add a car icon placeholder (just a simple shape)
        $img->drawRectangle(300, 200, 500, 400, function ($draw) {
            $draw->background('#d1d5db');
        });

        // Add some text
        $img->text('Vehicle Image', 400, 500, function ($font) {
            $font->filename(public_path('fonts/arial.ttf'));
            $font->size(32);
            $font->color('#6b7280');
            $font->align('center');
            $font->valign('middle');
        });

        // Save the image
        $encodedImage = $img->encode(new JpegEncoder(80));
        Storage::disk('public')->put('images/default-vehicle.jpg', $encodedImage);

        $this->info('Default vehicle image created successfully.');
    }
}
