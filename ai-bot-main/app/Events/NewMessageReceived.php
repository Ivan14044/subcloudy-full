<?php

namespace App\Events;

use App\Models\TicketMessage;
use App\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $ticket;

    /**
     * Create a new event instance.
     */
    public function __construct(TicketMessage $message, Ticket $ticket)
    {
        $this->message = $message;
        $this->ticket = $ticket;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('admin-notifications'),
        ];
    }

    /**
     * Название события для broadcasting
     */
    public function broadcastAs(): string
    {
        return 'message.received';
    }

    /**
     * Данные для broadcasting
     */
    public function broadcastWith(): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'message_id' => $this->message->id,
            'sender_type' => $this->message->sender_type,
            'text' => $this->message->text ? substr($this->message->text, 0, 100) : null,
            'has_image' => !empty($this->message->image_path),
            'created_at' => $this->message->created_at->toISOString(),
            'user_email' => $this->ticket->user ? $this->ticket->user->email : $this->ticket->guest_email,
        ];
    }
}

