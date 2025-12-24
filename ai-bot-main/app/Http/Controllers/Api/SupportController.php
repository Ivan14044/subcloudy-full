<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    /**
     * Получить авторизованного пользователя из запроса
     */
    protected function getApiUser(Request $request)
    {
        return $request->user();
    }

    /**
     * Получить или создать тикет для текущего пользователя
     */
    public function getOrCreateTicket(Request $request)
    {
        $user = $this->getApiUser($request);
        
        // Если пользователь не авторизован, проверяем guest_email
        if (!$user) {
            $email = $request->input('email');
            $source = $request->input('source'); // Получаем источник
            
            // Если email передан, валидируем его
            if ($email) {
                $validator = Validator::make(['email' => $email], [
                    'email' => 'email',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Invalid email format'
                    ], 400);
                }
            } else if ($source !== 'telegram') {
                // Email обязателен только если не Telegram
                return response()->json([
                    'success' => false,
                    'error' => 'Email required for web guest users'
                ], 400);
            }

            // Ищем открытый тикет для гостя
            $ticket = null;
            if ($email) {
                $ticket = Ticket::where('user_id', null)
                    ->where('guest_email', $email)
                    ->where('status', '!=', 'closed')
                    ->first();
            }

            if (!$ticket) {
                $ticket = Ticket::create([
                    'user_id' => null,
                    'guest_email' => $email,
                    'status' => 'open',
                    'subject' => 'Support Request',
                    'external_channel' => $source === 'telegram' ? 'telegram' : null,
                ]);
            }
        } else {
            // Ищем открытый тикет для авторизованного пользователя
            $ticket = Ticket::where('user_id', $user->id)
                ->where('status', '!=', 'closed')
                ->first();

            if (!$ticket) {
                $ticket = Ticket::create([
                    'user_id' => $user->id,
                    'status' => 'open',
                    'subject' => 'Support Request',
                ]);
            }
        }

        // Загружаем сообщения
        $ticket->load('messages');

        return response()->json([
            'success' => true,
            'ticket' => [
                'id' => $ticket->id,
                'status' => $ticket->status,
                'subject' => $ticket->subject,
                'messages' => $ticket->messages->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'sender_type' => $message->sender_type,
                        'source' => $message->source,
                        'text' => $message->text,
                        'image_url' => $message->image_path ? asset('storage/' . $message->image_path) : null,
                        'created_at' => $message->created_at->toISOString(),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Получить тикет с сообщениями
     */
    public function getTicket(Request $request, $id)
    {
        $user = $this->getApiUser($request);
        
        $ticket = Ticket::with('messages')->find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'error' => 'Ticket not found'
            ], 404);
        }

        // Проверка доступа
        if ($user) {
            if ($ticket->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Access denied'
                ], 403);
            }
        } else {
            // Для гостей проверяем email
            if ($ticket->guest_email !== $request->input('email')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Access denied'
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'ticket' => [
                'id' => $ticket->id,
                'status' => $ticket->status,
                'subject' => $ticket->subject,
                'messages' => $ticket->messages->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'sender_type' => $message->sender_type,
                        'source' => $message->source,
                        'text' => $message->text,
                        'image_url' => $message->image_path ? asset('storage/' . $message->image_path) : null,
                        'created_at' => $message->created_at->toISOString(),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Отправить сообщение в тикет
     */
    public function sendMessage(Request $request, $ticketId)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required_without:image|string|nullable',
            'image' => 'nullable|image|max:5120', // Макс 5МБ
            'source' => 'in:web,telegram',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
            ], 400);
        }

        $user = $this->getApiUser($request);
        $ticket = Ticket::find($ticketId);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'error' => 'Ticket not found'
            ], 404);
        }

        // Проверка доступа
        if ($user) {
            if ($ticket->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Access denied'
                ], 403);
            }
        } else {
            // Для гостей проверяем email
            if ($ticket->guest_email !== $request->input('email')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Access denied'
                ], 403);
            }
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('support', 'public');
        }

        // Создаем сообщение
        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'client',
            'sender_id' => $user ? $user->id : null,
            'source' => $request->input('source', 'web'),
            'text' => $request->input('text'),
            'image_path' => $imagePath,
        ]);

        // Обновляем статус тикета, если он был закрыт
        if ($ticket->status === 'closed') {
            $ticket->update(['status' => 'open']);
        }

        // Обновляем external_channel, если указан источник
        if ($request->input('source') === 'telegram' && !$ticket->external_channel) {
            $ticket->update(['external_channel' => 'telegram']);
        }

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_type' => $message->sender_type,
                'source' => $message->source,
                'text' => $message->text,
                'image_url' => $message->image_path ? asset('storage/' . $message->image_path) : null,
                'created_at' => $message->created_at->toISOString(),
            ],
            'ticket' => [
                'status' => $ticket->status,
            ],
        ]);
    }

    /**
     * Получить новые сообщения для тикета (polling)
     */
    public function getNewMessages(Request $request, $ticketId, $lastMessageId = null)
    {
        $user = $this->getApiUser($request);
        $ticket = Ticket::find($ticketId);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'error' => 'Ticket not found'
            ], 404);
        }

        // Проверка доступа
        if ($user) {
            if ($ticket->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Access denied'
                ], 403);
            }
        } else {
            if ($ticket->guest_email !== $request->input('email')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Access denied'
                ], 403);
            }
        }

        // Получаем lastMessageId из query параметров, если не передан как параметр маршрута
        if (!$lastMessageId) {
            $lastMessageId = $request->input('last_message_id');
        }

        $query = $ticket->messages()->orderBy('created_at', 'asc');

        if ($lastMessageId) {
            $query->where('id', '>', $lastMessageId);
        }

        $messages = $query->get();

        return response()->json([
            'success' => true,
            'status' => $ticket->status,
            'messages' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender_type' => $message->sender_type,
                    'source' => $message->source,
                    'text' => $message->text,
                    'image_url' => $message->image_path ? asset('storage/' . $message->image_path) : null,
                    'created_at' => $message->created_at->toISOString(),
                ];
            }),
        ]);
    }

    /**
     * Получить ссылку для Telegram
     */
    public function getTelegramLink(Request $request, $ticketId)
    {
        $user = $this->getApiUser($request);
        $ticket = Ticket::find($ticketId);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'error' => 'Ticket not found'
            ], 404);
        }

        // Проверка доступа
        if ($user) {
            if ($ticket->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Access denied'
                ], 403);
            }
        }

        // Получаем имя бота из настроек или используем дефолтное
        $botUsername = config('services.telegram.bot_username', 'your_support_bot');
        $telegramLink = "https://t.me/{$botUsername}?start=SUPPORT_{$ticket->id}";

        // Обновляем external_channel
        if (!$ticket->external_channel) {
            $ticket->update(['external_channel' => 'telegram']);
        }

        return response()->json([
            'success' => true,
            'telegram_link' => $telegramLink,
        ]);
    }
}

