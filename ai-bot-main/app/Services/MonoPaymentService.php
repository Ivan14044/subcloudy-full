<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;

class MonoPaymentService
{
    // Константа для кода валюты (840 = USD)
    public const CURRENCY_CODE = 840;

    /**
     * Создание инвойса для оплаты
     */
    public static function createInvoice(
        float $amount,
        string $redirectUrl,
        string $webhookUrl,
        string $walletId
    ): array|false {
        $response = self::makeRequest('post', 'invoice/create', [
            'amount' => intval($amount * 100),
            'ccy' => self::CURRENCY_CODE,
            'redirectUrl' => $redirectUrl,
            'webHookUrl' => $webhookUrl,
            'reference' => uniqid('order_'),
            'validity' => 3600 * 12,
            'saveCardData' => [
                'saveCard' => true,
                'walletId' => md5($walletId),
            ],
        ]);

        return $response;
    }

    /**
     * Списание денег по токену карты
     */
    public static function chargeWithToken(string $cardToken, float $amount, ?string $redirectUrl = null, ?string $webHookUrl = null): array|false
    {
        $data = [
            'cardToken' => $cardToken,
            'amount' => intval($amount * 100),
            'ccy' => self::CURRENCY_CODE,
            'initiationKind' => 'merchant', // merchant - платіж з ініціативи мерчанта (регулярний платіж)
        ];

        if ($redirectUrl) {
            $data['redirectUrl'] = $redirectUrl;
        }

        if ($webHookUrl) {
            $data['webHookUrl'] = $webHookUrl;
        }

        return self::makeRequest('post', 'wallet/payment', $data);
    }

    /**
     * Получение списка сохранённых карт по walletId
     */
    public static function getWalletCard(string $walletId): array
    {
        $response = self::makeRequest('get', 'wallet', [
            'walletId' => md5($walletId),
        ]);

        // Согласно документации Mono, ответ содержит поле 'wallet' с массивом карт
        return $response['wallet'] ?? [];
    }

    /**
     * Общий метод отправки запроса к Monobank API
     */
    private static function makeRequest(string $method, string $endpoint, array $data): array|false
    {
        $token = config('monobank.token');
        if (!$token) {
            Log::error('MonoPaymentService: Token not configured', [
                'endpoint' => $endpoint,
            ]);
            return false;
        }

        $url = "https://api.monobank.ua/api/merchant/{$endpoint}";

        Log::info('MonoPaymentService: Making request', [
            'method' => $method,
            'endpoint' => $endpoint,
            'url' => $url,
            'data' => $data,
            'token_present' => $token ? 'yes' : 'no',
        ]);

        $http = Http::withHeaders(['X-Token' => $token]);

        /** @var Response $response */
        $response = $method === 'get'
            ? $http->get($url . '?' . http_build_query($data))
            : $http->post($url, $data);

        if ($response->failed()) {
            Log::error('MonoPaymentService: Request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'json' => $response->json(),
                'endpoint' => $endpoint,
                'data' => $data,
            ]);
            return false;
        }

        $result = $response->json();
        Log::info('MonoPaymentService: Request successful', [
            'endpoint' => $endpoint,
            'response' => $result,
        ]);

        return $result;
    }
}
