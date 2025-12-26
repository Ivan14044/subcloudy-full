<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$ips = ['95.91.240.0' => 'DE', '8.8.8.8' => 'US', '1.1.1.1' => 'AU'];

foreach ($ips as $ip => $expected) {
    try {
        $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}?fields=countryCode");
        $code = $response->successful() ? $response->json('countryCode') : 'FAILED';
        echo "IP $ip -> Detected: $code (Expected: $expected)\n";
    } catch (\Exception $e) {
        echo "IP $ip -> ERROR: " . $e->getMessage() . "\n";
    }
}

