<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MarketingPageController extends Controller
{
    public function __invoke(Request $request, ?string $path = null): View
    {
        $path = trim($path ?? '', '/');

        if ($path === '') {
            return view('marketing.index');
        }

        if (str_contains($path, '..')) {
            abort(404);
        }

        $view = 'marketing.'.str_replace('/', '.', $path).'.index';

        abort_unless(view()->exists($view), 404);

        return view($view);
    }
}
