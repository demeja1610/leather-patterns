<?php

namespace App\Http\Controllers\Admin\PatternImage\Action;

use Spatie\Image\Image;
use Illuminate\Support\Str;
use App\Models\PatternImage;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\PatternImage\UploadRequest;

class UploadController extends Controller
{
    public function __invoke(UploadRequest $request)
    {
        $saved = [];
        $newPatternImage = new PatternImage();
        $disk = $newPatternImage->getSaveToDiskName();
        $uploadPath = $newPatternImage->getUploadPath();

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $filename = Str::uuid() . '.jpg';
                $tempPath = $imageFile->getRealPath();

                $tempImage = Image::load($tempPath);
                $originalWidth = $tempImage->getWidth();
                $originalSize = $imageFile->getSize();

                $targetWidth = 1920;
                $shouldResize = $originalWidth > $targetWidth;

                $optimizedTempPath = tempnam(sys_get_temp_dir(), 'optimized_');

                try {
                    $image = Image::load($tempPath);

                    if ($shouldResize) {
                        $image->width($targetWidth);
                    }

                    $image->quality(80)
                        ->format('jpg')
                        ->save($optimizedTempPath);

                    $optimizedSize = filesize($optimizedTempPath);

                    $finalTempPath = $optimizedTempPath;
                    $usedOptimized = true;

                    if ($optimizedSize > $originalSize) {
                        $finalTempPath = $tempPath;
                        $usedOptimized = false;

                        Log::info("Optimized image larger than original", [
                            'original' => $originalSize,
                            'optimized' => $optimizedSize,
                            'filename' => $imageFile->getClientOriginalName()
                        ]);
                    }

                    $savedPath = Storage::disk($disk)->putFileAs($uploadPath, $finalTempPath, $filename);

                    if ($usedOptimized && file_exists($optimizedTempPath)) {
                        unlink($optimizedTempPath);
                    }

                    if ($savedPath) {
                        $saved[] = Storage::disk($disk)->url($savedPath);
                    }
                } catch (\Exception $e) {
                    if (file_exists($optimizedTempPath)) {
                        unlink($optimizedTempPath);
                    }

                    Log::error("Image optimization failed", [
                        'error' => $e->getMessage(),
                        'filename' => $imageFile->getClientOriginalName()
                    ]);
                }
            }
        }

        return response()->json($saved);
    }
}
