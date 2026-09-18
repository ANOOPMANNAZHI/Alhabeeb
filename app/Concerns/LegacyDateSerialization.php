<?php

namespace App\Concerns;

use DateTimeInterface;

/**
 * Laravel 7+ serialises dates as ISO-8601 in toArray()/toJson().
 * The AJAX list pages and reports were written against the
 * Laravel 5.7 format, so keep that.
 */
trait LegacyDateSerialization
{
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
