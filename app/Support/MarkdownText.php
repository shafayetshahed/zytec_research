<?php

namespace App\Support;

/**
 * Escapes user-supplied text before it is placed in a Markdown mail template,
 * so names or messages cannot inject links, images or formatting.
 */
class MarkdownText
{
    public static function escape(?string $value): string
    {
        return (string) preg_replace('/([\\\\`*_{}\[\]()#+\-.!<>|~])/', '\\\\$1', (string) $value);
    }
}
