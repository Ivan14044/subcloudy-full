<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SupportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    protected $supportService;

    public function __construct(SupportService $supportService)
    {
        $this->supportService = $supportService;
    }

    /**
     * Обработка вебхука от Telegram
     */
    public function handle(Request $request)
    {
        $update = $request->all();

        // Логируем входящий запрос для отладки
        Log::info('Telegram Webhook Update', $update);

        try {
            $this->supportService->handleTelegramWebhook($update);
        } catch (\Exception $e) {
            Log::error('Error handling Telegram Webhook', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}


