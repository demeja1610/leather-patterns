<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\PatternVideo\Page;

use App\Models\PatternVideo;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class EditPageController extends Controller
{
    public function __invoke($id)
    {
        $video = $this->getVideo(id: $id);

        if (!$video instanceof PatternVideo) {
            return abort(code: Response::HTTP_NOT_FOUND);
        }


        return view(view: 'pages.admin.pattern-video.edit', data: [
            'video' => $video,
        ]);
    }

    protected function getVideo($id): ?PatternVideo
    {
        return PatternVideo::query()->find(id: $id);
    }
}
