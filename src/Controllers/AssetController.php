<?php

namespace Veltrix\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class AssetController extends Controller
{
    /**
     * Serve internal bundled assets.
     */
    public function serve(Request $request, string $path): Response
    {
        // Directory traversal protection
        $path = str_replace(['..', '\\'], ['', '/'], $path);
        $fullPath = realpath(__DIR__ . '/../../resources/dist/' . $path);

        $distRoot = realpath(__DIR__ . '/../../resources/dist');

        if (!$fullPath || !$distRoot || !str_starts_with($fullPath, $distRoot) || !is_file($fullPath)) {
            abort(404, 'Asset not found');
        }

        $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css'   => 'text/css; charset=utf-8',
            'js'    => 'application/javascript; charset=utf-8',
            'svg'   => 'image/svg+xml',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2',
            'json'  => 'application/json',
        ];

        $contentType = $mimeTypes[$extension] ?? 'text/plain';

        return response(file_get_contents($fullPath), 200, [
            'Content-Type'  => $contentType,
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
