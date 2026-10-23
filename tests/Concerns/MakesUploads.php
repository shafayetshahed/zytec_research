<?php

namespace Tests\Concerns;

use Illuminate\Http\UploadedFile;

/**
 * Builds synthetic documents whose bytes really match their extension, so
 * server-side content sniffing is exercised rather than bypassed.
 */
trait MakesUploads
{
    protected function pdfUpload(string $name = 'brief.pdf', int $paddingKb = 1): UploadedFile
    {
        $content = "%PDF-1.4\n1 0 obj << /Type /Catalog >> endobj\n".str_repeat('%synthetic test content'."\n", max(1, $paddingKb * 40))."%%EOF\n";

        return UploadedFile::fake()->createWithContent($name, $content);
    }

    protected function docxUpload(string $name = 'brief.docx'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'docx');
        $zip = new \ZipArchive;
        $zip->open($path, \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"/>');
        $zip->addFromString('word/document.xml', '<?xml version="1.0"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>Synthetic brief</w:t></w:r></w:p></w:body></w:document>');
        $zip->close();

        return UploadedFile::fake()->createWithContent($name, (string) file_get_contents($path));
    }

    protected function plainZipUpload(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'zip');
        $zip = new \ZipArchive;
        $zip->open($path, \ZipArchive::OVERWRITE);
        $zip->addFromString('payload.txt', 'not an office document');
        $zip->close();

        return UploadedFile::fake()->createWithContent($name, (string) file_get_contents($path));
    }

    protected function oleUpload(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1".str_repeat("\0", 504).str_repeat('A', 1024));
    }

    protected function jpgUpload(string $name = 'brief.jpg'): UploadedFile
    {
        $image = imagecreatetruecolor(4, 4);
        ob_start();
        imagejpeg($image);

        return UploadedFile::fake()->createWithContent($name, (string) ob_get_clean());
    }

    protected function pngUpload(string $name = 'brief.png'): UploadedFile
    {
        $image = imagecreatetruecolor(4, 4);
        ob_start();
        imagepng($image);

        return UploadedFile::fake()->createWithContent($name, (string) ob_get_clean());
    }
}
