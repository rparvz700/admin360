<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Interfaces\ImageInterface;

class ImageService
{
    /**
     * Default max dimension (px) for the longest edge.
     */
    protected int $maxDimension = 1200;

    /**
     * Default JPEG quality (1-100).
     */
    protected int $quality = 80;

    /**
     * Compress and store an uploaded image.
     *
     * Scales down proportionally to fit within maxDimension,
     * converts to JPEG, and stores on the given disk.
     *
     * @param  UploadedFile  $file     The uploaded image file.
     * @param  string        $folder   Storage folder path (e.g. 'odo_images/start').
     * @param  string        $disk     Storage disk name.
     * @param  int|null      $maxDimension  Max width/height in px (null = use default).
     * @param  int|null      $quality       JPEG quality 1-100 (null = use default).
     * @return string  The stored file path relative to the disk root.
     */
    public function compress(
        UploadedFile $file,
        string $folder,
        string $disk = 'public',
        ?int $maxDimension = null,
        ?int $quality = null,
    ): string {
        $maxDimension ??= $this->maxDimension;
        $quality ??= $this->quality;

        // Read image via Intervention Image
        $image = Image::read($file->getRealPath());

        // Scale down only if larger than max dimension (never upscale)
        $image = $this->scaleDown($image, $maxDimension);

        // Encode as JPEG with specified quality
        $encoded = $image->toJpeg($quality);

        // Generate unique filename
        $filename = $folder . '/' . Str::uuid() . '.jpg';

        // Store the compressed image
        Storage::disk($disk)->put($filename, (string) $encoded);

        return $filename;
    }

    /**
     * Scale image down proportionally so the longest edge
     * fits within the given max dimension. Never upscales.
     */
    protected function scaleDown(ImageInterface $image, int $maxDimension): ImageInterface
    {
        $width = $image->width();
        $height = $image->height();

        // Only downscale, never upscale
        if ($width <= $maxDimension && $height <= $maxDimension) {
            return $image;
        }

        // Intervention v3: scaleDown resizes proportionally, never enlarging
        return $image->scaleDown($maxDimension, $maxDimension);
    }
}
