<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Number;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Validates an uploaded academic document by extension allow-list, server-side
 * content sniffing, size, and file signature. The browser-supplied MIME type
 * is never trusted.
 */
class SafeDocument implements ValidationRule
{
    /** @var array<string, list<string>> Leading bytes expected for each family of formats. */
    private const SIGNATURES = [
        'pdf' => ['%PDF-'],
        'ole' => ["\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"],
        'ooxml' => ["PK\x03\x04"],
        'jpg' => ["\xFF\xD8\xFF"],
        'png' => ["\x89PNG\r\n\x1A\n"],
    ];

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('The file could not be uploaded. Please try again.');

            return;
        }

        /** @var array<string, list<string>> $allowed */
        $allowed = config('zytecs.uploads.allowed');
        $extension = strtolower($value->getClientOriginalExtension());
        $maxBytes = (int) config('zytecs.uploads.max_kb') * 1024;

        if (! array_key_exists($extension, $allowed)) {
            $fail('Upload a PDF, Word, PowerPoint, Excel, JPG or PNG file.');

            return;
        }

        $size = $value->getSize();

        if ($size === false || $size === 0) {
            $fail('The file is empty.');

            return;
        }

        if ($size > $maxBytes) {
            $fail('The file must not be larger than '.Number::fileSize($maxBytes).'.');

            return;
        }

        $detected = (new \finfo(FILEINFO_MIME_TYPE))->file($value->getRealPath()) ?: '';

        if (! in_array($detected, $allowed[$extension], true) || ! $this->hasExpectedSignature($value, $extension)) {
            $fail('The file content does not match its .'.$extension.' extension, or the file is damaged.');
        }
    }

    private function hasExpectedSignature(UploadedFile $file, string $extension): bool
    {
        $family = match ($extension) {
            'pdf' => 'pdf',
            'doc', 'ppt', 'xls' => 'ole',
            'docx', 'pptx', 'xlsx' => 'ooxml',
            'jpg', 'jpeg' => 'jpg',
            'png' => 'png',
            default => null,
        };

        if ($family === null) {
            return false;
        }

        $handle = fopen($file->getRealPath(), 'rb');

        if ($handle === false) {
            return false;
        }

        $head = (string) fread($handle, 16);
        fclose($handle);

        foreach (self::SIGNATURES[$family] as $signature) {
            if (str_starts_with($head, $signature)) {
                return $family !== 'ooxml' || $this->isOfficeOpenXml($file, $extension);
            }
        }

        return false;
    }

    /**
     * Confirms a ZIP container really is the expected Office document type
     * rather than an arbitrary archive renamed to .docx/.pptx/.xlsx.
     */
    private function isOfficeOpenXml(UploadedFile $file, string $extension): bool
    {
        $zip = new \ZipArchive;

        if ($zip->open($file->getRealPath(), \ZipArchive::RDONLY) !== true) {
            return false;
        }

        $expectedPart = match ($extension) {
            'docx' => 'word/document.xml',
            'pptx' => 'ppt/presentation.xml',
            'xlsx' => 'xl/workbook.xml',
            default => null,
        };

        if ($expectedPart === null) {
            $zip->close();

            return false;
        }

        $contentTypes = (string) $zip->getFromName('[Content_Types].xml');
        $isValid = $contentTypes !== ''
            && $zip->locateName($expectedPart) !== false
            // Macro-enabled documents (e.g. .docm renamed to .docx) are refused.
            && stripos($contentTypes, 'macroEnabled') === false
            && $zip->locateName('vbaProject.bin', \ZipArchive::FL_NODIR) === false;
        $zip->close();

        return $isValid;
    }
}
