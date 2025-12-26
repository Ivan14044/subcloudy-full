<?php

namespace App\Listeners;

use App\Events\NewMessageReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyAdminsAboutMessage implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NewMessageReceived $event): void
    {
        $message = $event->message;
        $ticket = $event->ticket;

        // Уведомляем только о сообщениях от клиентов
        if ($message->sender_type !== 'client') {
            return;
        }

        // Логируем получение сообщения
        Log::info('New support message received', [
            'ticket_id' => $ticket->id,
            'message_id' => $message->id,
            'source' => $message->source,
            'has_image' => !empty($message->image_path),
        ]);

        // Здесь можно добавить отправку email уведомлений админам
        // Mail::to(config('app.admin_email'))->send(new NewMessageNotification($message, $ticket));
    }
}

