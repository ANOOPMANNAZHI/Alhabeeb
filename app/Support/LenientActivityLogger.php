<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Spatie\Activitylog\Support\ActivityLogger;

/**
 * activitylog v5 types withProperties() as array|Collection; ~180 legacy call
 * sites pass an Eloquent model (v3 accepted anything and collect()ed it).
 * Bound in AppServiceProvider so activity()->withProperties($model) keeps
 * working instead of throwing a TypeError on every desktop create/update.
 */
class LenientActivityLogger extends ActivityLogger
{
    public function withProperties(mixed $properties): static
    {
        if ($properties instanceof \Illuminate\Contracts\Support\Arrayable) {
            $properties = $properties->toArray();
        } elseif (!is_array($properties) && !$properties instanceof Collection) {
            $properties = (array) $properties;
        }
        return parent::withProperties($properties);
    }
}
