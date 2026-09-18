<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * PHP 8 promoted a handful of runtime notices to warnings ("Undefined
 * variable", "Undefined array key", reading a property of null, ...).
 * Laravel converts warnings into ErrorException, so legacy views and
 * controllers written for PHP 7 (where the server's error_reporting
 * hid those notices) now abort the request instead of rendering an
 * empty value.
 *
 * This handler restores the PHP 7 outcome for that exact set of messages
 * and logs each occurrence so they can be fixed properly over time.
 * Everything else is passed to Laravel's own handler unchanged.
 */
class Php7NoticeCompat
{
    private const PATTERNS = [
        '/^Undefined variable \$/',
        '/^Undefined array key /',
        '/^Undefined (index|offset): /',
        '/^Undefined property: /',
        '/^Attempt to read property "[^"]+" on (null|bool|int|float|string|array)$/',
        '/^Trying to access array offset on (value of type )?(null|bool|int|float)$/',
        '/^Trying to get property .* of non-object$/',
    ];

    /** @var array<string,true> */
    private static array $seen = [];

    public static function register(): void
    {
        $previous = null;
        $previous = set_error_handler(function (int $level, string $message, string $file = '', int $line = 0) use (&$previous): bool {
            if ($level === E_WARNING && self::isPromotedNotice($message)) {
                self::logOnce($message, $file, $line);
                return true; // handled: continue as PHP 7 would have
            }

            if ($previous) {
                return (bool) $previous($level, $message, $file, $line);
            }

            return false; // fall through to PHP's default handling
        });
    }

    private static function isPromotedNotice(string $message): bool
    {
        foreach (self::PATTERNS as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }
        return false;
    }

    private static function logOnce(string $message, string $file, int $line): void
    {
        $key = $message . '|' . $file . '|' . $line;
        if (isset(self::$seen[$key])) {
            return;
        }
        self::$seen[$key] = true;

        if (config('app.debug')) {
            Log::debug("php7-compat: $message", ['file' => $file, 'line' => $line]);
        }
    }
}
