<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;

/**
 * @property int $id
 * @property int $submission_id
 * @property bool $is_brief
 * @property string $original_name
 * @property string $storage_path
 * @property string $mime_type
 * @property string $extension
 * @property int $size
 * @property string $sha256
 */
class SubmissionFile extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_brief' => 'boolean',
            'size' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Submission, $this>
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function humanSize(): string
    {
        return Number::fileSize($this->size, precision: 1);
    }
}
