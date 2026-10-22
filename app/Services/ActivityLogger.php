<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/** Mencatat tindakan sensitif admin. Jangan pernah mengirim password/token ke $properties. */
class ActivityLogger
{
    public function __construct(private Request $request) {}

    /** @param array<string, mixed> $properties */
    public function log(string $action, string $description, ?Model $subject = null, array $properties = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $this->request->user()?->id,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'description' => mb_substr($description, 0, 255),
            'properties' => $properties ?: null,
            'ip_address' => $this->request->ip(),
        ]);
    }
}
