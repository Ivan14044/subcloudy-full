<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест создания тикета авторизованным пользователем
     */
    public function test_authenticated_user_can_create_ticket()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/support/ticket', [
                'source' => 'web',
                'channel' => 'web',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'ticket' => ['id', 'status', 'messages']
            ]);

        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'status' => 'open',
        ]);
    }

    /**
     * Тест создания тикета гостем с email
     */
    public function test_guest_can_create_ticket_with_email()
    {
        $response = $this->postJson('/api/support/ticket', [
            'email' => 'guest@example.com',
            'source' => 'web',
            'channel' => 'web',
            'session_token' => 'sess_test123',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('tickets', [
            'guest_email' => 'guest@example.com',
            'status' => 'open',
        ]);
    }

    /**
     * Тест валидации email при создании тикета
     */
    public function test_ticket_creation_requires_valid_email_for_guest()
    {
        $response = $this->postJson('/api/support/ticket', [
            'email' => 'invalid-email',
            'source' => 'web',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => 'Invalid email format'
            ]);
    }

    /**
     * Тест отправки сообщения с валидацией
     */
    public function test_user_can_send_message_to_ticket()
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/support/ticket/{$ticket->id}/message", [
                'text' => 'Test message',
                'source' => 'web',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $ticket->id,
            'text' => 'Test message',
            'sender_type' => 'client',
        ]);
    }

    /**
     * Тест ограничения длины сообщения
     */
    public function test_message_text_has_max_length_validation()
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $longText = str_repeat('a', 4001); // Больше лимита

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/support/ticket/{$ticket->id}/message", [
                'text' => $longText,
                'source' => 'web',
            ]);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    /**
     * Тест проверки доступа к чужому тикету
     */
    public function test_user_cannot_access_other_user_ticket()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2, 'sanctum')
            ->getJson("/api/support/ticket/{$ticket->id}");

        $response->assertStatus(403)
            ->assertJson(['success' => false, 'error' => 'Access denied']);
    }

    /**
     * Тест rate limiting для создания тикетов
     */
    public function test_ticket_creation_is_rate_limited()
    {
        for ($i = 0; $i < 11; $i++) {
            $response = $this->postJson('/api/support/ticket', [
                'email' => "test{$i}@example.com",
                'source' => 'web',
                'session_token' => "sess_test{$i}",
            ]);

            if ($i < 10) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Тест sanitization текста сообщения
     */
    public function test_message_text_is_sanitized()
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $xssText = '<script>alert("XSS")</script>Hello';

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/support/ticket/{$ticket->id}/message", [
                'text' => $xssText,
                'source' => 'web',
            ]);

        $response->assertStatus(200);

        $message = TicketMessage::where('ticket_id', $ticket->id)->first();
        $this->assertStringNotContainsString('<script>', $message->text);
        $this->assertStringContainsString('&lt;script&gt;', $message->text);
    }
}

