<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Stores an already-validated upload on the private disk under a random
 * name, returning the metadata the application records about it.
 */
class PrivateFileStore
{
    /**
     * @return array{original_name: string, storage_path: string, mime_type: string, extension: string, size: int, sha256: string}
     */
    public static function store(UploadedFile $upload, string $area): array
    {
        $extension = strtolower($upload->getClientOriginalExtension());
        $directory = $area.'/'.now()->format('Y/m');

        $path = Storage::disk(config('zytecs.uploads.disk'))->putFileAs($directory, $upload, Str::uuid()->toString().'.'.$extension);

        if ($path === false) {
            throw new \RuntimeException('The file could not be stored.');
        }

        return [
            'original_name' => SafeFilename::from($upload->getClientOriginalName(), $extension),
            'storage_path' => $path,
            'mime_type' => (new \finfo(FILEINFO_MIME_TYPE))->file($upload->getRealPath()) ?: 'application/octet-stream',
            'extension' => $extension,
            'size' => (int) $upload->getSize(),
            'sha256' => hash_file('sha256', $upload->getRealPath()) ?: str_repeat('0', 64),
        ];
    }

    public static function delete(?string $path): void
    {
        if ($path === null) {
            return;
        }

        try {
            Storage::disk(config('zytecs.uploads.disk'))->delete($path);
        } catch (Throwable $exception) {
            Log::warning('Could not delete a stored file.', ['exception' => class_basename($exception)]);
        }
    }
}
