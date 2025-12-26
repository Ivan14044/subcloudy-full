<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Option;

class CookieConsentController extends Controller
{
    public function check(Request $request)
    {
        $ip = $request->header('CF-Connecting-IP') ?? $request->ip();
        $countryCode = $this->getCountryCodeFromIp($ip);

        $allowedCountries = Option::get('cookie_countries', []);
        if (is_string($allowedCountries)) {
            $allowedCountries = json_decode($allowedCountries, true) ?: [];
        }
        $showBanner = in_array($countryCode, $allowedCountries);

        return response()->json([
            'show_cookie_banner' => $showBanner,
            'country_code' => $countryCode,
        ]);
    }

    protected function getCountryCodeFromIp(string $ip): ?string
    {
        // 1. Пробуем через локальную базу GeoIP2
        if (class_exists(\GeoIp2\Database\Reader::class)) {
            try {
                $dbPath = storage_path('app/geoip/GeoLite2-Country.mmdb');
                if (file_exists($dbPath)) {
                    $reader = new \GeoIp2\Database\Reader($dbPath);
                    $record = $reader->country($ip);
                    return $record->country->isoCode;
                }
            } catch (\Exception $e) {
                \Log::warning('Local GeoIP failed: ' . $e->getMessage());
            }
        }

        // 2. Фолбэк: используем бесплатный внешний API (ip-api.com)
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=countryCode");
            if ($response->successful()) {
                return $response->json('countryCode');
            }
        } catch (\Exception $e) {
            \Log::error('External GeoIP fallback failed: ' . $e->getMessage());
        }

        return null;
    }
}
