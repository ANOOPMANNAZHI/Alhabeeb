<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;

/**
 * Debug helper for the upgrade smoke run: SMOKE_ROUTES="name1,name2" prints
 * the exception behind each 500 instead of the rendered error page.
 *
 * @group smoke-one
 */
class SmokeOneRouteTest extends TestCase
{
    public function test_routes_from_env(): void
    {
        $names = array_filter(array_map('trim', explode(',', (string) getenv('SMOKE_ROUTES'))));
        if (!$names) {
            $this->markTestSkipped('SMOKE_ROUTES not set');
        }
        $this->withoutExceptionHandling();
        $this->actingAs(User::where('username', 'admin')->firstOrFail());

        foreach ($names as $name) {
            $uri = '/' . ltrim(app('router')->getRoutes()->getByName($name)->uri(), '/');
            try {
                $status = $this->get($uri)->getStatusCode();
                fwrite(STDERR, "\n$name => $status\n");
            } catch (\Throwable $e) {
                $frame = collect($e->getTrace())->first(fn ($f) => isset($f['file']) && !str_contains($f['file'], 'vendor'));
                fwrite(STDERR, "\n$name => " . get_class($e) . ': ' . $e->getMessage()
                    . "\n    at " . $e->getFile() . ':' . $e->getLine()
                    . ($frame ? "\n    app frame " . $frame['file'] . ':' . ($frame['line'] ?? '?') : '') . "\n");
            }
        }
        $this->assertTrue(true);
    }
}
