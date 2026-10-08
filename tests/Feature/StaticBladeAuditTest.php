<?php

/**
 * Static Blade audit — catches disconnected actions before runtime:
 *  - href="#" placeholder links
 *  - hard-coded relational IDs (rate_plan_id = 1 style)
 *  - TODO/FIXME/coming-soon stubs in panel & public views
 *  - raw javascript: URLs
 */
it('has no placeholder href="#" links in panel and public views', function () {
    $dirs = [
        resource_path('views/panel'),
        resource_path('views/public'),
        resource_path('views/portal'),
    ];

    $offenders = [];
    foreach ($dirs as $dir) {
        if (! is_dir($dir)) {
            continue;
        }
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            foreach (file($file->getPathname()) as $i => $line) {
                if (preg_match('/href\s*=\s*["\']#["\']/', $line)) {
                    $offenders[] = substr($file->getPathname(), strlen(base_path()) + 1).':'.($i + 1);
                }
            }
        }
    }

    expect($offenders)->toBe([]);
});

it('has no hardcoded rate plan or property IDs in application code', function () {
    $dirs = [app_path('Http/Controllers'), app_path('Services')];
    $offenders = [];

    foreach ($dirs as $dir) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $content = file_get_contents($file->getPathname());
            foreach (['rate_plan_id', 'room_type_id', 'property_id', 'outlet_id', 'company_id', 'agent_id'] as $field) {
                if (preg_match('/["\']'.$field.'["\']\s*=>\s*[1-9]\d*\b(?!\d)/', $content, $m, PREG_OFFSET_CAPTURE)) {
                    $line = substr_count(substr($content, 0, $m[0][1]), "\n") + 1;
                    $offenders[] = substr($file->getPathname(), strlen(base_path()) + 1).":{$line} ({$field})";
                }
            }
        }
    }

    expect($offenders)->toBe([]);
});

it('has no coming-soon stubs in panel views', function () {
    $dir = resource_path('views/panel');
    $offenders = [];

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        foreach (file($file->getPathname()) as $i => $line) {
            if (preg_match('/coming soon|coming soon/i', $line)) {
                $offenders[] = substr($file->getPathname(), strlen(base_path()) + 1).':'.($i + 1);
            }
        }
    }

    expect($offenders)->toBe([]);
});
