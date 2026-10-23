<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Records security-relevant and administrative actions. Never pass secrets,
 * passcodes, or document contents in the metadata.
 */
class AuditLogger
{
    public function __construct(private Request $request) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(string $action, ?Model $subject = null, array $metadata = [], ?int $actorId = null): AuditLog
    {
        $log = new AuditLog;
        $log->actor_id = $actorId ?? $this->request->user()?->getKey();
        $log->action = $action;
        $log->metadata = $metadata === [] ? null : $metadata;
        $log->ip_address = $this->request->ip();

        if ($subject !== null) {
            $log->subject()->associate($subject);
        }

        $log->save();

        return $log;
    }
}
