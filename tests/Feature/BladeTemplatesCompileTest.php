<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Catches template mistakes such as "text@endif" (a directive glued to a
 * word is printed as text by Blade), which otherwise only fail at runtime
 * on the page that uses the template.
 */
class BladeTemplatesCompileTest extends TestCase
{
    public function test_every_blade_template_compiles_to_valid_php(): void
    {
        $templates = File::allFiles(resource_path('views'));
        $this->assertNotEmpty($templates);

        foreach ($templates as $template) {
            if (! str_ends_with($template->getFilename(), '.blade.php')) {
                continue;
            }

            $php = Blade::compileString(File::get($template->getPathname()));

            try {
                \PhpToken::tokenize($php, TOKEN_PARSE);
            } catch (\ParseError $error) {
                $this->fail($template->getRelativePathname().': '.$error->getMessage());
            }
        }
    }
}
