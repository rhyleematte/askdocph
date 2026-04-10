<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DailyAffirmation;

$rows = DailyAffirmation::orderBy('publish_at')->get(['id','quote','is_published','publish_at']);
echo 'now: ' . now()->toDateTimeString() . PHP_EOL;

foreach ($rows as $r) {
    echo $r->id
        . ' | ' . ($r->is_published ? '1' : '0')
        . ' | ' . ($r->publish_at ? $r->publish_at->toDateTimeString() : 'null')
        . ' | ' . (strlen($r->quote) > 40 ? substr($r->quote, 0, 40) . '...' : $r->quote)
        . PHP_EOL;
}
