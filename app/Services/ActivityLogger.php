<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public function log(
        ?User $actor,
        string $action,
        ?Model $subject = null,
        array $properties = [],
        ?string $subjectName = null
    ): ActivityLog {
        return ActivityLog::query()->create([
            'user_id' => $actor?->id,
            'user_name' => $actor?->name ?? 'System',
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'subject_name' => $subjectName ?? $this->nameOf($subject),
            'properties' => $this->redact($properties),
        ]);
    }

    private function nameOf(?Model $subject): ?string
    {
        if (! $subject) {
            return null;
        }

        foreach (['name', 'title', 'email'] as $attribute) {
            if (is_string($subject->getAttribute($attribute))) {
                return $subject->getAttribute($attribute);
            }
        }

        return null;
    }

    private function redact(array $properties): array
    {
        foreach ($properties as $key => $value) {
            if (preg_match('/password|token|secret|credential/i', (string) $key) === 1) {
                unset($properties[$key]);
            } elseif (is_array($value)) {
                $properties[$key] = $this->redact($value);
            }
        }

        return $properties;
    }
}
