<?php

namespace Tests\Unit;

use App\Support\SafeFilename;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class SafeFilenameTest extends TestCase
{
    #[TestWith(['../../etc/passwd.pdf', 'pdf', 'passwd.pdf'])]
    #[TestWith(['C:\\Users\\me\\Brief.docx', 'docx', 'Brief.docx'])]
    #[TestWith(["bad\x00name.pdf", 'pdf', 'badname.pdf'])]
    #[TestWith(['a<b>c:d|e?.pdf', 'pdf', 'a-b-c-d-e.pdf'])]
    #[TestWith(['...pdf', 'pdf', 'pdf.pdf'])]
    #[TestWith(['', 'pdf', 'brief.pdf'])]
    #[TestWith(['Report.PHP', 'pdf', 'Report.pdf'])]
    public function test_sanitizes_display_names(string $original, string $extension, string $expected): void
    {
        $this->assertSame($expected, SafeFilename::from($original, $extension));
    }

    public function test_limits_length(): void
    {
        $this->assertLessThanOrEqual(160, strlen(SafeFilename::from(str_repeat('a', 400).'.pdf', 'pdf')));
    }
}
