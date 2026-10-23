<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Produces a display-safe version of a user-supplied filename. The result is
 * only ever shown or used in Content-Disposition, never as a storage path.
 */
class SafeFilename
{
    public static function from(string $original, string $extension): string
    {
        $name = basename(str_replace('\\', '/', $original));
        $name = preg_replace('/[\p{Cc}\p{Cf}]+/u', '', $name) ?? '';
        $name = preg_replace('/[\/\\\\:*?"<>|]+/', '-', $name) ?? '';
        $name = trim(preg_replace('/\s+/', ' ', $name) ?? '', ' .-');

        $stem = pathinfo($name, PATHINFO_FILENAME);
        $stem = trim(Str::limit($stem, 150, ''), ' .-');

        if ($stem === '') {
            $stem = 'brief';
        }

        return $stem.'.'.$extension;
    }
}
