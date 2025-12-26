<?php

namespace App\Services;

use App\Events\TicketCreated;
use App\Events\NewMessageReceived;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SupportService
{
    /**
     * Обработка вебхука от Telegram
     */
    public function handleTelegramWebhook(array $update)
    {
        if (!isset($update['message'])) {
            return;
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'] ?? null;
        $text = $message['text'] ?? null;
        $photo = $message['photo'] ?? null;

        if (!$chatId) return;

        // 1. Пытаемся найти пользователя по telegram_id
        $fromId = $message['from']['id'] ?? null;
        $user = null;
        if ($fromId) {
            $user = User::where('telegram_id', $fromId)->first();
        }

        // 2. Обработка команды /start
        if ($text && str_starts_with($text, '/start')) {
            // Обычное приветствие для всех
            $this->sendTelegramMessage($chatId, "Здравствуйте! Чем я могу вам помочь? Опишите ваш вопрос, и наш менеджер ответит вам в ближайшее время.");
            return;
        }

        // 3. Пытаемся найти ПОСЛЕДНИЙ тикет по chat_id (даже закрытый)
        $ticket = Ticket::where('telegram_chat_id', $chatId)
            ->orderBy('updated_at', 'desc')
            ->first();

        // 4. Если тикета нет, создаем новый
        if (!$ticket) {
            $ticket = Ticket::create([
                'user_id' => $user ? $user->id : null,
                'telegram_chat_id' => $chatId,
                'status' => 'open',
                'subject' => 'Telegram Support Request',
                'external_channel' => 'telegram',
                'guest_email' => $message['from']['username'] ?? ('tg_' . $fromId),
            ]);
            
            // Мы не отправляем TicketCreated здесь, 
            // так как ниже будет отправлено NewMessageReceived.
            // Если нужно именно TicketCreated для первого сообщения - добавим проверку ниже.
        }

        // 5. Сохранение сообщения
        $imagePath = null;
        if ($photo) {
            // Берем самое большое фото (последнее в массиве)
            $fileId = end($photo)['file_id'];
            $imagePath = $this->downloadTelegramFile($fileId);
        }

        if ($text || $imagePath) {
            $isFirstMessage = $ticket->messages()->count() === 0;

            $newMessage = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_type' => 'client',
                'source' => 'telegram',
                'ticket_status' => 'open', // При новом сообщении всегда будет open
                'text' => $text,
                'image_path' => $imagePath,
            ]);

            // ВСЕГДА переоткрываем тикет при новом сообщении от клиента
            $ticket->update(['status' => 'open']);
            $ticket->touch(); // Принудительное обновление времени для сортировки
            
            // Если это первое сообщение в тикете, отправляем TicketCreated
            if ($isFirstMessage) {
                event(new \App\Events\TicketCreated($ticket));
            }

            // Отправляем событие о новом сообщении
            event(new NewMessageReceived($newMessage, $ticket));
        }
    }

    /**
     * Скачать файл из Telegram
     */
    private function downloadTelegramFile($fileId)
    {
        $botToken = config('services.telegram.bot_token');
        if (!$botToken) return null;

        try {
            // Получаем информацию о файле
            $fileInfo = Http::get("https://api.telegram.org/bot{$botToken}/getFile", [
                'file_id' => $fileId
            ])->json();

            if (!isset($fileInfo['result']['file_path'])) return null;

            $filePath = $fileInfo['result']['file_path'];
            $fileUrl = "https://api.telegram.org/file/bot{$botToken}/{$filePath}";

            // Скачиваем файл
            $contents = file_get_contents($fileUrl);
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $newFileName = 'support/' . Str::random(40) . '.' . $extension;

            Storage::disk('public')->put($newFileName, $contents);

            return $newFileName;
        } catch (\Exception $e) {
            Log::error('Error downloading Telegram file', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Отправить текстовое сообщение в Telegram
     */
    private function sendTelegramMessage($chatId, $text)
    {
        $botToken = config('services.telegram.bot_token');
        if (!$botToken) return;

        Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }

    /**
     * Получить или создать тикет
     */
    public function getOrCreateTicket(?User $user, ?string $email = null, ?string $source = null, ?string $sessionToken = null): Ticket
    {
        $lockKey = 'ticket_lock_' . ($user ? $user->id : md5($email . $sessionToken));
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 10); // Блокировка на 10 секунд

        return $lock->block(5, function () use ($user, $email, $source, $sessionToken) {
            if ($user) {
                // Ищем последнее обращение пользователя строго по каналу
                $query = Ticket::where('user_id', $user->id);
                
                if ($source === 'web') {
                    $query->whereNull('telegram_chat_id');
                } elseif ($source === 'telegram') {
                    $query->whereNotNull('telegram_chat_id');
                }
                
                $ticket = $query->orderBy('created_at', 'desc')->first();

                if (!$ticket) {
                    $ticket = Ticket::create([
                        'user_id' => $user->id,
                        'status' => 'open',
                        'subject' => 'Support Request',
                        'external_channel' => $source === 'telegram' ? 'telegram' : null,
                        'lang' => $user->lang ?? app()->getLocale(),
                    ]);
                    
                    // Убрали немедленную отправку события TicketCreated.
                    // Теперь оно будет отправлено при первом сообщении.
                }
            } else {
                $ticket = null;
                if ($email) {
                    // Для гостей тоже разделяем по каналу
                    $query = Ticket::whereNull('user_id')
                        ->where('guest_email', $email)
                        ->orderBy('created_at', 'desc');
                    
                if ($source === 'web') {
                    $query->whereNull('telegram_chat_id')
                          ->where('session_token', $sessionToken);
                } elseif ($source === 'telegram') {
                        $query->whereNotNull('telegram_chat_id');
                    }

                    $ticket = $query->first();
                }

                if (!$ticket) {
                    $ticket = Ticket::create([
                        'user_id' => null,
                        'guest_email' => $email,
                        'session_token' => $source === 'web' ? $sessionToken : null,
                        'status' => 'open',
                        'subject' => 'Support Request',
                        'external_channel' => $source === 'telegram' ? 'telegram' : null,
                        'lang' => app()->getLocale(),
                    ]);
                    
                    // Убрали немедленную отправку события TicketCreated.
                    // Теперь оно будет отправлено при первом сообщении.
                }
            }

            return $ticket;
        });
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

        // Проверяем, первое ли это сообщение в тикете
        $isFirstMessage = $ticket->messages()->count() === 0;

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => $data['sender_type'] ?? 'client',
            'sender_id' => $user ? $user->id : null,
            'source' => $data['source'] ?? 'web',
            'ticket_status' => $ticket->status, // Сохраняем текущий статус
            'text' => $data['text'] ?? null,
            'image_path' => $imagePath,
        ]);

        // Обновляем статус тикета
        if ($ticket->status === 'closed') {
            $ticket->update(['status' => 'open']);
        }

        $ticket->touch(); // Принудительное обновление времени для сортировки

        if (isset($data['source']) && $data['source'] === 'telegram' && !$ticket->external_channel) {
            $ticket->update(['external_channel' => 'telegram']);
        }

        // Очищаем кэш статистики
        \Cache::forget('support_stats');

        // Отправляем события
        if ($message->sender_type === 'client') {
            // Если это первое сообщение, уведомляем о создании тикета
            if ($isFirstMessage) {
                event(new \App\Events\TicketCreated($ticket));
            }
            
            // Всегда уведомляем о новом сообщении
            event(new NewMessageReceived($message, $ticket));
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
        $botUsername = config('services.telegram.bot_username', 'subcloudy_support_bot');
        
        // Убрали привязку SUPPORT_{id}, чтобы в Telegram создавалось отдельное обращение
        return "https://t.me/{$botUsername}";
    }
}
