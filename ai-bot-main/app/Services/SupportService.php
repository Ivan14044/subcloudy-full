<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class SupportService
{
    /**
     * Получить или создать тикет
     */
    public function getOrCreateTicket(?User $user, ?string $email = null, ?string $source = null): Ticket
    {
        if ($user) {
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
        } else {
            $ticket = null;
            if ($email) {
                $ticket = Ticket::whereNull('user_id')
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
        }

        return $ticket;
    }

    /**
     * Отправить сообщение в тикет
     */
    public function sendMessage(Ticket $ticket, ?User $user, array $data): TicketMessage
    {
        $imagePath = null;
        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $imagePath = $data['image']->store('support', 'public');
        }

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => $data['sender_type'] ?? 'client',
            'sender_id' => $user ? $user->id : null,
            'source' => $data['source'] ?? 'web',
            'text' => $data['text'] ?? null,
            'image_path' => $imagePath,
        ]);

        // Обновляем статус тикета
        if ($ticket->status === 'closed') {
            $ticket->update(['status' => 'open']);
        }

        if (isset($data['source']) && $data['source'] === 'telegram' && !$ticket->external_channel) {
            $ticket->update(['external_channel' => 'telegram']);
        }

        return $message;
    }

    /**
     * Получить новые сообщения
     */
    public function getNewMessages(Ticket $ticket, ?int $lastMessageId = null)
    {
        $query = $ticket->messages()->orderBy('created_at', 'asc');

        if ($lastMessageId) {
            $query->where('id', '>', $lastMessageId);
        }

        return $query->get();
    }

    /**
     * Генерация ссылки для Telegram
     */
    public function getTelegramLink(Ticket $ticket): string
    {
        $botUsername = config('services.telegram.bot_username', 'your_support_bot');
        
        if (!$ticket->external_channel) {
            $ticket->update(['external_channel' => 'telegram']);
        }

        return "https://t.me/{$botUsername}?start=SUPPORT_{$ticket->id}";
    }
}

