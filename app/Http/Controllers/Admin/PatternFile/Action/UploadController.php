<?php

namespace App\Http\Controllers\Admin\PatternFile\Action;

use App\Models\PatternFile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PatternFile\UploadRequest;

class UploadController extends Controller
{
    public function __invoke(UploadRequest $request)
    {
        $saved = [];

        if ($request->hasFile('files')) {
            $newPatternFile = new PatternFile();
            /**
             * @var \Illuminate\Http\UploadedFile $file
             */
            foreach ($request->file('files') as $file) {
                $path = $file->store($newPatternFile->getUploadPath(), $newPatternFile->getSaveToDiskName());

                if ($path !== false) {
                    $saved[] = asset('storage/' . $path); // TODO: private storages?
                }
            }
        }

        return response()->json($saved);
    }
}
