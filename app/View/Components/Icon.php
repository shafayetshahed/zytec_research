<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\View\ComponentAttributeBag;

/**
 * Renders a Lucide icon (ISC licence) from resources/icons as inline SVG.
 * Icons are decorative by default; pass a label to expose one to assistive technology.
 */
class Icon extends Component
{
    /** @var array<string, string> */
    private static array $cache = [];

    public function __construct(public string $name, public ?string $label = null) {}

    public function render(): Closure
    {
        return function (array $data): string {
            /** @var ComponentAttributeBag $attributes */
            $attributes = $data['attributes'];
            $class = trim('shrink-0 '.($attributes->get('class') ?? 'size-5'));
            $a11y = $this->label !== null
                ? 'role="img" aria-label="'.e($this->label).'"'
                : 'aria-hidden="true" focusable="false"';

            $svg = self::$cache[$this->name] ??= $this->load();

            // Escape Blade echo syntax defensively; the SVG files contain none.
            return str_replace(['{{', '{!!', '@'], ['&#123;&#123;', '&#123;!!', '&#64;'], (string) preg_replace('/<svg\b/', '<svg class="'.e($class).'" '.$a11y, $svg, 1));
        };
    }

    private function load(): string
    {
        $path = resource_path('icons/'.basename($this->name).'.svg');

        if (! is_file($path)) {
            return '<svg viewBox="0 0 24 24"></svg>';
        }

        $svg = (string) file_get_contents($path);
        $svg = preg_replace('/<!--.*?-->/s', '', $svg) ?? $svg;
        // Strip sizing and class from the root <svg> only; inner shapes keep their geometry.
        $svg = preg_replace_callback('/<svg\b[^>]*>/', fn (array $tag) => preg_replace('/\s(class|width|height)="[^"]*"/', '', $tag[0]) ?? $tag[0], $svg, 1) ?? $svg;

        return trim(preg_replace('/\s+/', ' ', $svg) ?? $svg);
    }
}
