<?php

namespace App\Listeners;

use App\Events\TicketCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyAdminsAboutTicket implements ShouldQueue
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
    public function handle(TicketCreated $event): void
    {
        $ticket = $event->ticket;

        // Логируем создание тикета
        Log::info('New support ticket created', [
            'ticket_id' => $ticket->id,
            'user_id' => $ticket->user_id,
            'guest_email' => $ticket->guest_email,
            'source' => $ticket->external_channel ?? 'web',
        ]);

        // Здесь можно добавить отправку email уведомлений админам
        // Mail::to(config('app.admin_email'))->send(new NewTicketNotification($ticket));
    }
}

