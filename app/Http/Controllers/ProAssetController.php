<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves the Pro-only JavaScript modules (SVG export, batch processing) from
 * storage/app/pro to signed-in Pro subscribers. They are never public files.
 */
class ProAssetController extends Controller
{
    public const MODULES = ['svg', 'batch'];

    public function __invoke(Request $request, string $name): Response
    {
        abort_unless(in_array($name, self::MODULES, true), 404);
        abort_unless($request->user()?->isPro(), 403);

        $path = storage_path('app/pro/'.$name.'.js');
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => 'text/javascript; charset=UTF-8',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
