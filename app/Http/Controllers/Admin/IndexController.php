<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.admin.index');
    }
}
