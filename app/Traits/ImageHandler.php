<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;

trait ImageHandler
{
    /**
     * Store an image with proper formatting and optimization
     *
     * @param UploadedFile $image
     * @param string $path
     * @param string $oldImage
     * @return string
     */
    public function storeImage(UploadedFile $image, string $path, string $oldImage = null): string
    {
        // Delete old image if exists
        if ($oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
        $fullPath = $path . '/' . $filename;

        // Create image manager instance
        $manager = new ImageManager(new Driver());

        // Process the image
        $img = $manager->read($image->getRealPath())
            ->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode(new JpegEncoder(80));

        // Store the image
        Storage::disk('public')->put($fullPath, $img);

        return $fullPath;
    }

    /**
     * Get the full URL for an image
     *
     * @param string|null $imagePath
     * @return string
     */
    public function getImageUrl(?string $imagePath): string
    {
        if (!$imagePath) {
            return asset('images/default-vehicle.jpg');
        }

        return Storage::disk('public')->url($imagePath);
    }
}
