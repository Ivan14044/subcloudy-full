<?php

namespace App\Traits;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

trait VerifiesTicketAccess
{
    /**
     * Проверить доступ к тикету
     *
     * @param Ticket $ticket
     * @param User|null $user
     * @param Request $request
     * @return JsonResponse|null Возвращает ошибку или null если доступ разрешен
     */
    protected function verifyTicketAccess(Ticket $ticket, ?User $user, Request $request): ?JsonResponse
    {
        // Проверка для авторизованных пользователей
        if ($user) {
            if ($ticket->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Access denied'
                ], 403);
            }
            return null;
        }

        // Проверка для гостей
        $email = $request->input('email');
        $sessionToken = $request->input('session_token');

        // Проверка email
        if ($ticket->guest_email !== $email) {
            $maskedEmail = preg_replace('/(?<=.).(?=.*@)/', '*', $email ?? '');
            \Log::warning('Support Access Denied: Email Mismatch', [
                'ticket_id' => $ticket->id,
                'provided_email' => $maskedEmail,
                'ip' => $request->ip()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Access denied (email mismatch)'
            ], 403);
        }

        // Проверка session token
        if ($ticket->session_token && $ticket->session_token !== $sessionToken) {
            \Log::warning('Support Access Denied: Session Token Mismatch', [
                'ticket_id' => $ticket->id,
                'provided_token' => $sessionToken,
                'expected_token' => $ticket->session_token,
                'ip' => $request->ip()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Access denied (session mismatch)'
            ], 403);
        }

        return null;
    }
}

