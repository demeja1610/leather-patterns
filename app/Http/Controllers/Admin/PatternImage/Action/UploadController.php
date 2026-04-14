<?php

namespace App\Http\Controllers\Admin\PatternImage\Action;

use App\Models\PatternImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PatternImage\UploadRequest;

class UploadController extends Controller
{
    public function __invoke(UploadRequest $request)
    {
        $saved = [];

        if ($request->hasFile('images')) {
            $newPatternImage = new PatternImage();

            /**
             * @var \Illuminate\Http\UploadedFile $imagefile
             */
            foreach ($request->file('images') as $imagefile) {
                $path = $imagefile->store($newPatternImage->getUploadPath(), $newPatternImage->getSaveToDiskName());

                if ($path !== false) {
                    $saved[] = asset('storage/' . $path); // TODO: private storages?
                }
            }
        }

        return response()->json($saved);
    }
}
