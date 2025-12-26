<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Option;

$val = Option::get('cookie_countries');
echo "Option::get('cookie_countries') type: " . gettype($val) . "\n";
echo "Option::get('cookie_countries') value: " . json_encode($val) . "\n";

$raw = \DB::table('options')->where('name', 'cookie_countries')->first();
echo "Raw DB value: " . ($raw ? $raw->value : 'NULL') . "\n";

// Test country code from IP (simulated)
// Let's check a German IP: 8.8.8.8 is US, 1.1.1.1 is AU, 95.91.240.0 is DE
$ips = ['95.91.240.0' => 'DE', '8.8.8.8' => 'US', '1.1.1.1' => 'AU'];

foreach ($ips as $ip => $expected) {
    $code = null;
    if (class_exists(\GeoIp2\Database\Reader::class)) {
        try {
            $reader = new \GeoIp2\Database\Reader(storage_path('app/geoip/GeoLite2-Country.mmdb'));
            $record = $reader->country($ip);
            $code = $record->country->isoCode;
        } catch (\Exception $e) {
            $code = "ERROR: " . $e->getMessage();
        }
    } else {
        $code = "CLASS NOT FOUND";
    }
    echo "IP $ip -> Detected: $code (Expected: $expected)\n";
}

