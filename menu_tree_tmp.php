<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = DB::table('menu')->orderBy('parent_menu')->orderBy('menu_order')->get();

$byParent = [];
foreach ($rows as $r) {
    $byParent[$r->parent_menu][] = $r;
}

$totalActive = 0;
$totalInactive = 0;

function printNode($node, $depth, &$out, &$totalActive, &$totalInactive) {
    global $byParent;
    $indent = str_repeat('  ', $depth);
    $prefix = $depth === 0 ? '-' : '-';
    $statusMark = $node->status == 1 ? '' : ' _(inactive)_';
    if ($node->status == 1) $totalActive++; else $totalInactive++;

    if ($node->menutype == 1) {
        // Menu group
        $out[] = "{$indent}{$prefix} **{$node->menu_name}** `({$node->menu_icon})`{$statusMark}";
    } else {
        $routeInfo = $node->route_name ? " — route: `{$node->route_name}`" : '';
        $out[] = "{$indent}{$prefix} {$node->menu_name}{$routeInfo}{$statusMark}";
    }

    if (isset($byParent[$node->id])) {
        foreach ($byParent[$node->id] as $child) {
            printNode($child, $depth + 1, $out, $totalActive, $totalInactive);
        }
    }
}

$out = [];
if (isset($byParent[0])) {
    foreach ($byParent[0] as $top) {
        printNode($top, 0, $out, $totalActive, $totalInactive);
    }
}

echo implode("\n", $out) . "\n";
echo "\n---\nTOTAL ACTIVE: $totalActive, TOTAL INACTIVE: $totalInactive\n";
