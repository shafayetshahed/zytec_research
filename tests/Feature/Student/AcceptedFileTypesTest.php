<?php

namespace Tests\Feature\Student;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\MakesUploads;
use Tests\TestCase;

class AcceptedFileTypesTest extends TestCase
{
    use LazilyRefreshDatabase, MakesUploads;

    /**
     * @return array<string, array{0: \Closure(self): UploadedFile, 1: string}>
     */
    public static function acceptedFiles(): array
    {
        return [
            'pdf' => [fn (self $t) => $t->pdfUpload('a.pdf'), 'pdf'],
            'docx' => [fn (self $t) => $t->docxUpload('a.docx'), 'docx'],
            'legacy doc' => [fn (self $t) => $t->oleUpload('a.doc'), 'doc'],
            'legacy ppt' => [fn (self $t) => $t->oleUpload('a.ppt'), 'ppt'],
            'legacy xls' => [fn (self $t) => $t->oleUpload('a.xls'), 'xls'],
            'jpg' => [fn (self $t) => $t->jpgUpload('a.jpg'), 'jpg'],
            'jpeg uppercase' => [fn (self $t) => $t->jpgUpload('A.JPEG'), 'jpeg'],
            'png' => [fn (self $t) => $t->pngUpload('a.png'), 'png'],
        ];
    }

    #[DataProvider('acceptedFiles')]
    public function test_supported_formats_are_accepted(\Closure $makeFile, string $extension): void
    {
        Storage::fake('local');
        Mail::fake();

        $this->actingAs(User::factory()->create())->post(route('student.submissions.store'), [
            'assignment_name' => 'Report', 'module_code' => 'ABC123', 'brief' => $makeFile($this),
            'whatsapp_number' => '+447700900123', 'confirm_whatsapp' => '1', 'terms' => '1', 'client_token' => (string) Str::uuid(),
        ])->assertSessionHasNoErrors();

        $file = Submission::sole()->files->sole();
        $this->assertSame($extension, $file->extension);
        $this->assertStringEndsWith('.'.$extension, $file->storage_path);
    }

    public function test_jpeg_bytes_with_png_extension_are_rejected(): void
    {
        Storage::fake('local');

        $this->actingAs(User::factory()->create())->post(route('student.submissions.store'), [
            'assignment_name' => 'Report', 'module_code' => 'ABC123', 'brief' => $this->jpgUpload('a.png'),
            'whatsapp_number' => '+447700900123', 'confirm_whatsapp' => '1', 'terms' => '1', 'client_token' => (string) Str::uuid(),
        ])->assertSessionHasErrors('brief');
    }

    public function test_ole_bytes_with_docx_extension_are_rejected(): void
    {
        Storage::fake('local');

        $this->actingAs(User::factory()->create())->post(route('student.submissions.store'), [
            'assignment_name' => 'Report', 'module_code' => 'ABC123', 'brief' => $this->oleUpload('a.docx'),
            'whatsapp_number' => '+447700900123', 'confirm_whatsapp' => '1', 'terms' => '1', 'client_token' => (string) Str::uuid(),
        ])->assertSessionHasErrors('brief');
    }
}
