<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\\Http\\Controllers\\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Services\SupportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SupportController extends Controller
{
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
            $source = $request->input('source');
            
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
                return response()->json([
                    'success' => false,
                    'error' => 'Email required for web guest users'
                ], 400);
            }
        }

        $ticket = $this->supportService->getOrCreateTicket(
            $user, 
            $request->input('email'), 
            $request->input('source')
        );

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
            'image' => 'nullable|image|max:5120',
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
                return response()->json(['success' => false, 'error' => 'Access denied'], 403);
            }
        } else if ($ticket->guest_email !== $request->input('email')) {
            return response()->json(['success' => false, 'error' => 'Access denied'], 403);
        }

        $message = $this->supportService->sendMessage($ticket, $user, $request->all());

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

        // Проверка доступа
        if ($user) {
            if ($ticket->user_id !== $user->id) {
                return response()->json(['success' => false, 'error' => 'Access denied'], 403);
            }
        } else if ($ticket->guest_email !== $request->input('email')) {
            return response()->json(['success' => false, 'error' => 'Access denied'], 403);
        }

        if (!$lastMessageId) {
            $lastMessageId = $request->input('last_message_id');
        }

        $messages = $this->supportService->getNewMessages($ticket, $lastMessageId);

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

