<?php

namespace App\Http\Controllers\Pattern\Web\v1;

use App\Models\User;
use App\Models\Pattern;
use App\Models\PatternLike;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToggleLikeController extends Controller
{
    public function __invoke($id, Request $request)
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            return abort(Response::HTTP_FORBIDDEN);
        }

        $pattern = $this->getPattern($id, $user);

        if (!$pattern instanceof Pattern) {
            return abort(Response::HTTP_NOT_FOUND);
        }

        if ($pattern->likes->isEmpty()) {
            $like = new PatternLike([
                'pattern_id' => $pattern->id,
                'user_id' => $user->id,
            ]);

            $like->save();
        } else {
            $like = $pattern->likes->first()->delete();
        }

        return $request->wantsJson()
            ? response()
            : back();
    }

    protected function getPattern($id, User $user): Pattern
    {
        $q = Pattern::query()
            ->where('id', $id)
            ->where('is_published', true)
            ->with([
                'likes' => fn(HasMany $sq) => $sq->where('user_id', $user->id)
            ]);

        return $q->first();
    }
}
