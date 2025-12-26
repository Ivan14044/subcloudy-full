<?php

namespace Tests\Unit;

use App\Models\Ticket;
use App\Models\User;
use App\Services\SupportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $supportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->supportService = new SupportService();
    }

    /**
     * Тест создания тикета для авторизованного пользователя
     */
    public function test_creates_ticket_for_authenticated_user()
    {
        $user = User::factory()->create();

        $ticket = $this->supportService->getOrCreateTicket($user, null, 'web', null);

        $this->assertInstanceOf(Ticket::class, $ticket);
        $this->assertEquals($user->id, $ticket->user_id);
        $this->assertEquals('open', $ticket->status);
    }

    /**
     * Тест создания тикета для гостя
     */
    public function test_creates_ticket_for_guest_with_email()
    {
        $email = 'guest@example.com';
        $sessionToken = 'sess_test123';

        $ticket = $this->supportService->getOrCreateTicket(null, $email, 'web', $sessionToken);

        $this->assertInstanceOf(Ticket::class, $ticket);
        $this->assertEquals($email, $ticket->guest_email);
        $this->assertEquals($sessionToken, $ticket->session_token);
    }

    /**
     * Тест возвращения существующего тикета
     */
    public function test_returns_existing_ticket_for_user()
    {
        $user = User::factory()->create();
        $existingTicket = Ticket::factory()->create(['user_id' => $user->id]);

        $ticket = $this->supportService->getOrCreateTicket($user, null, 'web', null);

        $this->assertEquals($existingTicket->id, $ticket->id);
    }

    /**
     * Тест отправки сообщения
     */
    public function test_sends_message_to_ticket()
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $message = $this->supportService->sendMessage($ticket, $user, [
            'text' => 'Test message',
            'source' => 'web',
        ]);

        $this->assertNotNull($message);
        $this->assertEquals('Test message', $message->text);
        $this->assertEquals('client', $message->sender_type);
    }

    /**
     * Тест переоткрытия закрытого тикета при новом сообщении
     */
    public function test_reopens_closed_ticket_on_new_message()
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'status' => 'closed'
        ]);

        $this->supportService->sendMessage($ticket, $user, [
            'text' => 'Reopening message',
            'source' => 'web',
        ]);

        $ticket->refresh();
        $this->assertEquals('open', $ticket->status);
    }

    /**
     * Тест генерации Telegram ссылки
     */
    public function test_generates_telegram_link()
    {
        $ticket = Ticket::factory()->create();

        $link = $this->supportService->getTelegramLink($ticket);

        $this->assertStringContainsString('t.me', $link);
        $this->assertStringContainsString('subcloudy_support_bot', $link);
    }
}

