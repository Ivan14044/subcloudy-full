<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Services\EmailService;
use App\Services\NotificationTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupportController extends Controller
{
    /**
     * Список всех обращений
     */
    public function index(Request $request)
    {
        $query = Ticket::has('messages') // Показываем только тикеты с сообщениями
            ->with(['user', 'lastMessage.sender'])
            ->orderBy('updated_at', 'desc');

        // Фильтры
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('channel') && $request->channel) {
            if ($request->channel === 'web') {
                $query->where(function($q) {
                    $q->where('external_channel', 'web')
                      ->orWhereNull('external_channel');
                });
            } elseif ($request->channel === 'telegram') {
                $query->where('external_channel', 'telegram');
            } elseif ($request->channel === 'both') {
                $query->whereNotNull('telegram_chat_id')
                      ->where(function($q) {
                          $q->where('external_channel', 'web')
                            ->orWhereNull('external_channel');
                      });
            }
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('guest_email', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('email', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->paginate(20);

        return view('admin.support.index', compact('tickets'));
    }

    /**
     * Просмотр конкретного обращения
     */
    public function show($id)
    {
        $ticket = Ticket::with(['user', 'messages.sender'])
            ->findOrFail($id);

        return view('admin.support.show', compact('ticket'));
    }

    /**
     * Получить новые сообщения (для админ-панели)
     */
    public function getNewMessages(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $lastMessageId = $request->input('last_message_id');

        $query = $ticket->messages()->with('sender');

        if ($lastMessageId) {
            $query->where('id', '>', $lastMessageId);
        }

        $messages = $query->get()->map(function ($message) {
            return [
                'id' => $message->id,
                'text' => $message->text,
                'sender_type' => $message->sender_type,
                'source' => $message->source,
                'created_at' => $message->created_at->toIso8601String(),
                'image_url' => $message->image_path ? asset('storage/' . $message->image_path) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'status' => $ticket->status
        ]);
    }

    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'text' => 'required_without:image|string|nullable|max:4000',
            'image' => 'nullable|image|max:5120|mimes:jpeg,png,jpg,gif,webp',
        ]);

        $ticket = Ticket::findOrFail($id);
        $admin = auth()->user();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('support', 'public');
        }

        // Автоматически определяем канал ответа на основе последнего сообщения клиента
        $lastClientMsg = $ticket->messages()->where('sender_type', 'client')->latest()->first();
        $source = $lastClientMsg ? $lastClientMsg->source : ($ticket->external_channel ?? 'web');

        // Sanitize текст от XSS
        $messageText = $request->input('text');
        if ($messageText) {
            $messageText = htmlspecialchars($messageText, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
        }

        // Создаем сообщение от админа
        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'admin',
            'sender_id' => $admin->id,
            'source' => $source,
            'ticket_status' => $ticket->status === 'open' ? 'in_progress' : $ticket->status, // Сохраняем статус, который будет
            'text' => $messageText,
            'image_path' => $imagePath,
        ]);

        // Обновляем статус тикета
        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        $ticket->touch(); // Принудительное обновление времени

        // Отправка email уведомления об ответе
        if ($source === 'web' || $source === 'both') {
            $params = [
                'message_text' => $messageText ?: 'Прикреплено изображение',
            ];

            if ($ticket->user_id) {
                EmailService::send('support_reply', $ticket->user_id, $params);
                app(NotificationTemplateService::class)->sendToUser($ticket->user, 'support_reply');
            } elseif ($ticket->guest_email) {
                // Используем сохраненный язык тикета или 'en' по умолчанию
                $locale = $ticket->lang ?: 'en';
                EmailService::sendToEmail('support_reply', $ticket->guest_email, $params, $locale);
            }
        }

        // Отправляем в Telegram только если выбран этот канал и привязан чат
        if ($source === 'telegram' && $ticket->telegram_chat_id) {
            if ($request->input('text')) {
                $this->sendTelegramMessage($ticket->telegram_chat_id, $request->input('text'));
            }
            // Если есть изображение, отправляем и его
            if ($imagePath) {
                $this->sendTelegramPhoto($ticket->telegram_chat_id, storage_path('app/public/' . $imagePath));
            }
        }

        return redirect()->back()->with('success', 'Сообщение отправлено');
    }

    /**
     * Отправить фото в Telegram через бота
     */
    private function sendTelegramPhoto($chatId, $photoPath)
    {
        $botToken = config('services.telegram.bot_token');
        if (!$botToken) return;

        try {
            \Illuminate\Support\Facades\Http::attach(
                'photo', file_get_contents($photoPath), basename($photoPath)
            )->post("https://api.telegram.org/bot{$botToken}/sendPhoto", [
                'chat_id' => $chatId,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error sending Telegram photo', ['chat_id' => $chatId, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Изменить статус тикета
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,closed',
        ]);

        $ticket = Ticket::findOrFail($id);
        $oldStatus = $ticket->status;
        $newStatus = $request->input('status');
        
        $ticket->update(['status' => $newStatus]);

        // Если статус реально изменился, создаем системное сообщение для истории
        if ($oldStatus !== $newStatus) {
            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_type' => 'admin',
                'sender_id' => auth()->id(),
                'source' => $ticket->external_channel ?? 'web',
                'ticket_status' => $newStatus,
                'text' => null, // Пустой текст = системное уведомление
            ]);
            $ticket->touch();
        }

        return redirect()->back()->with('success', 'Статус обновлен');
    }

    /**
     * Отправить сообщение в Telegram через бота
     */
    private function sendTelegramMessage($chatId, $text)
    {
        $botToken = config('services.telegram.bot_token');
        
        if (!$botToken) {
            \Log::warning('Telegram bot token not configured');
            return;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5),
                'parse_mode' => 'HTML',
            ]);

            if (!$response->successful()) {
                \Log::error('Failed to send Telegram message', [
                    'chat_id' => $chatId,
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error sending Telegram message', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Статистика по обращениям (с кэшированием)
     */
    public function stats()
    {
        // Считаем только тикеты, в которых есть хотя бы одно сообщение
        $baseQuery = Ticket::has('messages');

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'open' => (clone $baseQuery)->where('status', 'open')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'closed' => (clone $baseQuery)->where('status', 'closed')->count(),
            'today' => (clone $baseQuery)->whereDate('created_at', today())->count(),
            'last_client_message_id' => TicketMessage::where('sender_type', 'client')->max('id'),
        ];

        return response()->json($stats);
    }

    /**
     * Массовые действия
     */
    public function massAction(Request $request)
    {
        \Log::info('Mass Action called', $request->all());
        
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:tickets,id',
                'action' => 'required|in:close,open,delete',
            ]);
        } catch (\Exception $e) {
            \Log::error('Mass Action validation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $ids = $request->input('ids');
        $action = $request->input('action');

        try {
            if ($action === 'close') {
                Ticket::whereIn('id', $ids)->update(['status' => 'closed']);
                return response()->json(['success' => true, 'message' => 'Обращения закрыты']);
            } elseif ($action === 'open') {
                Ticket::whereIn('id', $ids)->update(['status' => 'open']);
                return response()->json(['success' => true, 'message' => 'Обращения открыты']);
            } elseif ($action === 'delete') {
                Ticket::whereIn('id', $ids)->delete();
                return response()->json(['success' => true, 'message' => 'Обращения удалены']);
            }
        } catch (\Exception $e) {
            \Log::error('Mass Action execution failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return response()->json(['success' => false, 'message' => 'Неизвестное действие'], 400);
    }
}

