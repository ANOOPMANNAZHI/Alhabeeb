<?php

if (!function_exists('jasper_template')) {
    /**
     * Absolute path of a JasperReports template shipped in the repository.
     * Templates used to live in vendor/cossou/jasperphp/examples, which is
     * not under version control.
     */
    function jasper_template(string $file): string
    {
        return resource_path('reports/jasper/' . ltrim($file, '/'));
    }
}

if (!function_exists('jasper_output_dir')) {
    /**
     * Directory JasperStarter writes generated reports into. Public so the
     * browser can be redirected straight to the file (see jasper_output_url).
     */
    function jasper_output_dir(): string
    {
        $dir = public_path('reports');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir;
    }
}

if (!function_exists('jasper_output_url')) {
    /** Browser URL of a report file produced into jasper_output_dir(). */
    function jasper_output_url(string $file): string
    {
        return url('reports/' . ltrim($file, '/'));
    }
}
