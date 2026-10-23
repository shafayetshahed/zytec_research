<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a private file as an attachment with safe headers.
 */
class PrivateDownload
{
    public static function response(string $path, string $name, string $mime): StreamedResponse
    {
        return Storage::disk(config('zytecs.uploads.disk'))->download($path, $name, [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
