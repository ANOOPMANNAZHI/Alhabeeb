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
        'keyAccept',                // routes pointing at controller methods that do not exist
        'complaintReAssignModal', 'servicecomplaintReportAutocompleteCode', 'servicecomplaintReportNoAutocompleteCode',
        'buildingAutocompleteByUnit', 'averageReceivables', 'renewedContractMd',
        'buildingList',             // leftover debug endpoint: print_r + die
        'menuOrder',                // reorder action; needs its POSTed ids
        'plmsApproval',             // duplicate name: dashboard /plmsApproval calls plmsApproval($type) with no {type} segment
        'buildingTypeAutocompleteCode', // calls an undefined relation; broken on 5.7 as well
        'getBouncedCheques',        // loads every bounced PDC with N+1 relations — minutes on production data, same on 5.7
        // server-sent-events endpoints: echo before headers by design and generate every report
        'landlordTaxInvoiceReportStream',
        'maintenanceInvoiceReportV2Stream',
        'normalManagementReportV2Stream',
    ];

    /**
     * Blade compiles to PHP lazily; a PHP 8 parse error (e.g. an unparenthesised
     * nested ternary) only surfaces when the view is first rendered and kills the
     * request. Compile everything and lint the output.
     */
    public function test_every_compiled_view_is_valid_php(): void
    {
        $this->artisan('view:clear')->assertSuccessful();
        $this->artisan('view:cache')->assertSuccessful();

        $bad = [];
        foreach (glob(storage_path('framework/views/*.php')) as $compiled) {
            exec('php -l ' . escapeshellarg($compiled) . ' 2>&1', $out, $code);
            if ($code !== 0) {
                preg_match('/PATH (\S+)/', file_get_contents($compiled), $m);
                $bad[] = ($m[1] ?? $compiled) . ' :: ' . implode(' ', array_filter($out, fn ($l) => str_contains($l, 'error')));
            }
            $out = [];
        }
        $this->artisan('view:clear');

        $this->assertSame([], $bad, "Views that do not compile to valid PHP:\n" . implode("\n", $bad));
    }

    public function test_every_named_get_route_renders_for_super_admin(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();
        $this->actingAs($admin);

        $failures = [];
        $scaffolds = [];
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
                    // `Route::resource` scaffolds whose create() returns view('<module>::create'),
                    // a view that never existed; nothing links to them.
                    if (str_ends_with($name, '.create') && str_contains($response->getContent(), 'View [create] not found')) {
                        $scaffolds[] = $name;
                    } else {
                        $failures[$name] = $status . ' ' . $this->firstErrorLine($response->getContent());
                    }
                }
            } catch (\Throwable $e) {
                $failures[$name] = get_class($e) . ': ' . $e->getMessage();
                file_put_contents(base_path('docs/upgrade/smoke-progress.log'), ' EXCEPTION ' . get_class($e) . "\n", FILE_APPEND);
            }
        }

        file_put_contents(
            base_path('docs/upgrade/smoke-get-routes.txt'),
            "checked=$checked failures=" . count($failures) . " unimplemented-create-scaffolds=" . count($scaffolds) . "\n" .
            implode("\n", array_map(fn ($k, $v) => "$k => $v", array_keys($failures), $failures)) . "\n" .
            "scaffolds: " . implode(', ', $scaffolds) . "\n"
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
