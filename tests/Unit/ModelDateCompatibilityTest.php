<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ModelDateCompatibilityTest extends TestCase
{
    private function modelFiles(): array
    {
        $root = dirname(__DIR__, 2);
        $files = glob($root . '/Modules/*/Entities/*.php');
        $files[] = $root . '/app/User.php';
        return $files;
    }

    public function test_no_model_uses_removed_dates_property(): void
    {
        $offenders = [];
        foreach ($this->modelFiles() as $f) {
            if (preg_match('/protected\s+\$dates\s*=/', file_get_contents($f))) {
                $offenders[] = basename($f);
            }
        }
        $this->assertSame([], $offenders, 'protected $dates was removed in Laravel 10');
    }

    public function test_every_model_keeps_legacy_date_serialisation(): void
    {
        $missing = [];
        foreach ($this->modelFiles() as $f) {
            $src = file_get_contents($f);
            if (preg_match('/extends\s+(Model|Authenticatable|Pivot)\b/', $src)
                && !str_contains($src, 'LegacyDateSerialization')) {
                $missing[] = basename($f);
            }
        }
        $this->assertSame([], $missing);
    }
}
