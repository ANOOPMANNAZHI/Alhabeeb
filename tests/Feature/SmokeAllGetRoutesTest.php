<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Upgrade smoke test: signed in as the super admin, request every named,
 * parameterless GET route and make sure none of them throw. Runs against
 * the database configured in .env (a copy of production for the upgrade).
 *
 * Run explicitly: vendor/bin/phpunit --group smoke
 *
 * @group smoke
 */
class SmokeAllGetRoutesTest extends TestCase
{
    /** Routes that intentionally are not plain page loads. */
    private const SKIP = [
        'logout', 'login', 'register', 'password.request', 'password.reset', 'password.email', 'password.update',
        'verification.notice', 'verification.resend', 'verification.verify',
        'soapApiAction',            // calls Dynamics AX
        'notificationTest',
        'invoice.create',           // unimplemented resource scaffold (view 'backoffice::create' never existed)
        'getBouncedCheques',        // loads every bounced PDC with N+1 relations — minutes on production data, same on 5.7
    ];

    public function test_every_named_get_route_renders_for_super_admin(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $this->actingAs($admin);

        $failures = [];
        $checked = 0;

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if (!$name || in_array($name, self::SKIP, true)) {
                continue;
            }
            if (!in_array('GET', $route->methods(), true)) {
                continue;
            }
            $uri = $route->uri();
            if (str_contains($uri, '{') || str_starts_with($uri, 'api/') || str_starts_with($uri, '_')) {
                continue;
            }

            $checked++;
            $t = microtime(true);
            file_put_contents(base_path('docs/upgrade/smoke-progress.log'), "$name /$uri ...", FILE_APPEND);
            try {
                $response = $this->get('/' . ltrim($uri, '/'));
                file_put_contents(base_path('docs/upgrade/smoke-progress.log'), ' ' . $response->getStatusCode() . ' ' . round(microtime(true) - $t, 1) . "s\n", FILE_APPEND);
                $status = $response->getStatusCode();
                if ($status >= 500) {
                    $failures[$name] = $status . ' ' . $this->firstErrorLine($response->getContent());
                }
            } catch (\Throwable $e) {
                $failures[$name] = get_class($e) . ': ' . $e->getMessage();
                file_put_contents(base_path('docs/upgrade/smoke-progress.log'), ' EXCEPTION ' . get_class($e) . "\n", FILE_APPEND);
            }
        }

        file_put_contents(
            base_path('docs/upgrade/smoke-get-routes.txt'),
            "checked=$checked failures=" . count($failures) . "\n" .
            implode("\n", array_map(fn ($k, $v) => "$k => $v", array_keys($failures), $failures)) . "\n"
        );

        $this->assertGreaterThan(300, $checked);
        $this->assertSame([], $failures, "Routes that errored:\n" . print_r($failures, true));
    }

    private function firstErrorLine(string $html): string
    {
        if (preg_match('/<title>([^<]+)<\/title>/', $html, $m)) {
            return trim(html_entity_decode($m[1]));
        }
        return substr(strip_tags($html), 0, 160);
    }
}
