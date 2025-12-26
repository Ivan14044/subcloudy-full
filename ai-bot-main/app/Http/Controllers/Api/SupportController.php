<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Services\SupportService;
use App\Traits\VerifiesTicketAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    use VerifiesTicketAccess;

    protected $supportService;

    public function __construct(SupportService $supportService)
    {
        $this->supportService = $supportService;
    }

    /**
     * Получить авторизованного пользователя из запроса
     */
    protected function getApiUser(Request $request)
    {
        $user = $request->user();
        
        // Если через middleware пользователь не определен, пробуем вручную через токен
        if (!$user && $request->bearerToken()) {
            $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            if ($token && $token->tokenable instanceof \App\Models\User) {
                $user = $token->tokenable;
                // Устанавливаем пользователя в запрос для дальнейшего использования
                $request->setUserResolver(fn () => $user);
            }
        }
        
        return $user;
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
            $source = $request->input('source');
            
            if ($email) {
                $validator = Validator::make(['email' => $email], [
                    'email' => 'required|email|max:255',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Invalid email format'
                    ], 400);
                }
            } else if ($source !== 'telegram') {
                return response()->json([
                    'success' => false,
                    'error' => 'Email required for web guest users'
                ], 400);
            }
        }

        $ticket = $this->supportService->getOrCreateTicket(
            $user, 
            $request->input('email'), 
            $request->input('source'),
            $request->input('session_token')
        );

        // Загружаем сообщения (фильтруем по каналу, если указано)
        $channel = $request->input('channel') ?: 'web';
        $query = $ticket->messages()->orderBy('created_at', 'asc');
        
        if ($channel) {
            $query->where('source', $channel);
        }
        
        $messages = $query->get();

        // Проверяем, есть ли ответы в другом канале
        $lastAdminMessage = $ticket->messages()
            ->where('sender_type', 'admin')
            ->latest()
            ->first();
        
        $otherChannelReply = null;
        if ($lastAdminMessage && $channel && $lastAdminMessage->source !== $channel) {
            $otherChannelReply = $lastAdminMessage->source;
        }

        return response()->json([
            'success' => true,
            'ticket' => [
                'id' => $ticket->id,
                'user_id' => $ticket->user_id,
                'status' => $ticket->status,
                'subject' => $ticket->subject,
                'other_channel_reply' => $otherChannelReply,
                'messages' => $messages->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'sender_type' => $message->sender_type,
                        'source' => $message->source,
                        'ticket_status' => $message->ticket_status,
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
        
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'error' => 'Ticket not found'
            ], 404);
        }

        // Загружаем сообщения (фильтруем по каналу, если указано)
        $channel = $request->input('channel') ?: 'web';
        $query = $ticket->messages()->orderBy('created_at', 'asc');
        
        if ($channel) {
            $query->where('source', $channel);
        }
        
        $messages = $query->get();

        // Проверяем, есть ли ответы в другом канале
        $lastAdminMessage = $ticket->messages()
            ->where('sender_type', 'admin')
            ->latest()
            ->first();
        
        $otherChannelReply = null;
        if ($lastAdminMessage && $channel && $lastAdminMessage->source !== $channel) {
            $otherChannelReply = $lastAdminMessage->source;
        }

        // Проверка доступа через trait
        $accessError = $this->verifyTicketAccess($ticket, $user, $request);
        if ($accessError) {
            return $accessError;
        }

        return response()->json([
            'success' => true,
            'ticket' => [
                'id' => $ticket->id,
                'user_id' => $ticket->user_id,
                'status' => $ticket->status,
                'subject' => $ticket->subject,
                'other_channel_reply' => $otherChannelReply,
                'messages' => $messages->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'sender_type' => $message->sender_type,
                        'source' => $message->source,
                        'ticket_status' => $message->ticket_status,
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
            'text' => 'required_without:image|string|nullable|max:4000',
            'image' => 'nullable|image|max:5120|mimes:jpeg,png,jpg,gif,webp',
            'source' => 'in:web,telegram',
            'email' => 'nullable|email|max:255',
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

        // Проверка доступа через trait
        $accessError = $this->verifyTicketAccess($ticket, $user, $request);
        if ($accessError) {
            return $accessError;
        }

        // Sanitize текст от XSS
        $messageData = $request->all();
        if (isset($messageData['text'])) {
            $messageData['text'] = htmlspecialchars($messageData['text'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
        }

        $message = $this->supportService->sendMessage($ticket, $user, $messageData);

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
            return response()->json(['success' => false, 'error' => 'Ticket not found'], 404);
        }

        // Проверка доступа через trait
        $accessError = $this->verifyTicketAccess($ticket, $user, $request);
        if ($accessError) {
            return $accessError;
        }

        if (!$lastMessageId) {
            $lastMessageId = $request->input('last_message_id');
        }

        // Получаем новые сообщения
        $channel = $request->input('channel') ?: 'web';
        $query = $ticket->messages()
            ->where('id', '>', $lastMessageId ?: 0)
            ->orderBy('created_at', 'asc');
            
        if ($channel) {
            $query->where('source', $channel);
        }
        
        $messages = $query->get();

        // Проверяем, есть ли ответы в другом канале
        $lastAdminMessage = $ticket->messages()
            ->where('sender_type', 'admin')
            ->latest()
            ->first();
        
        $otherChannelReply = null;
        if ($lastAdminMessage && $channel && $lastAdminMessage->source !== $channel) {
            $otherChannelReply = $lastAdminMessage->source;
        }

        return response()->json([
            'success' => true,
            'status' => $ticket->status,
            'other_channel_reply' => $otherChannelReply,
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
            return response()->json(['success' => false, 'error' => 'Ticket not found'], 404);
        }

        if ($user && $ticket->user_id !== $user->id) {
            return response()->json(['success' => false, 'error' => 'Access denied'], 403);
        }

        $telegramLink = $this->supportService->getTelegramLink($ticket);

        return response()->json([
            'success' => true,
            'telegram_link' => $telegramLink,
        ]);
    }
}
