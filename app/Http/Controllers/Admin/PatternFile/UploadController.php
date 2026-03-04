<?php

namespace App\Http\Controllers\Admin\PatternFile;

use App\Models\PatternFile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PatternFile\UploadRequest;

class UploadController extends Controller
{
    public function __invoke(UploadRequest $request)
    {
        $saved = [];

        $filePath = (new PatternFile())->getUploadPath();

        if ($request->hasFile('files')) {
            /**
             * @var \Illuminate\Http\UploadedFile $file
             */
            foreach ($request->file('files') as $file) {
                $path = $file->store($filePath, 'public');

                if ($path !== false) {
                    $saved[] = asset('storage/' . $path);
                }
            }
        }

        return response()->json($saved);
    }
}
