<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupportController extends Controller
{
    /**
     * Список всех обращений
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'lastMessage'])
            ->orderBy('updated_at', 'desc');

        // Фильтры
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('channel') && $request->channel) {
            if ($request->channel === 'web') {
                $query->where(function($q) {
                    $q->where('external_channel', 'web')
                      ->orWhereNull('external_channel');
                });
            } elseif ($request->channel === 'telegram') {
                $query->where('external_channel', 'telegram');
            } elseif ($request->channel === 'both') {
                $query->whereNotNull('telegram_chat_id')
                      ->where(function($q) {
                          $q->where('external_channel', 'web')
                            ->orWhereNull('external_channel');
                      });
            }
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('guest_email', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('email', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->paginate(20);

        return view('admin.support.index', compact('tickets'));
    }

    /**
     * Просмотр конкретного обращения
     */
    public function show($id)
    {
        $ticket = Ticket::with(['user', 'messages.sender'])
            ->findOrFail($id);

        return view('admin.support.show', compact('ticket'));
    }

    /**
     * Отправить ответ администратора
     */
    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'text' => 'required_without:image|string|nullable',
            'image' => 'nullable|image|max:5120',
        ]);

        $ticket = Ticket::findOrFail($id);
        $admin = auth()->user();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('support', 'public');
        }

        // Создаем сообщение от админа
        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'admin',
            'sender_id' => $admin->id,
            'source' => 'web',
            'text' => $request->input('text'),
            'image_path' => $imagePath,
        ]);

        // Обновляем статус тикета
        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        // Если у тикета есть Telegram канал, отправляем сообщение через бота
        if ($ticket->telegram_chat_id) {
            if ($request->input('text')) {
                $this->sendTelegramMessage($ticket->telegram_chat_id, $request->input('text'));
            }
            // Если есть изображение, отправляем и его
            if ($imagePath) {
                $this->sendTelegramPhoto($ticket->telegram_chat_id, storage_path('app/public/' . $imagePath));
            }
        }

        return redirect()->back()->with('success', 'Сообщение отправлено');
    }

    /**
     * Отправить фото в Telegram через бота
     */
    private function sendTelegramPhoto($chatId, $photoPath)
    {
        $botToken = config('services.telegram.bot_token');
        if (!$botToken) return;

        try {
            \Illuminate\Support\Facades\Http::attach(
                'photo', file_get_contents($photoPath), basename($photoPath)
            )->post("https://api.telegram.org/bot{$botToken}/sendPhoto", [
                'chat_id' => $chatId,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error sending Telegram photo', ['chat_id' => $chatId, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Изменить статус тикета
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,closed',
        ]);

        $ticket = Ticket::findOrFail($id);
        $ticket->update(['status' => $request->input('status')]);

        return redirect()->back()->with('success', 'Статус обновлен');
    }

    /**
     * Отправить сообщение в Telegram через бота
     */
    private function sendTelegramMessage($chatId, $text)
    {
        $botToken = config('services.telegram.bot_token');
        
        if (!$botToken) {
            \Log::warning('Telegram bot token not configured');
            return;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);

            if (!$response->successful()) {
                \Log::error('Failed to send Telegram message', [
                    'chat_id' => $chatId,
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error sending Telegram message', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Статистика по обращениям
     */
    public function stats()
    {
        $stats = [
            'total' => Ticket::count(),
            'open' => Ticket::where('status', 'open')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'closed' => Ticket::where('status', 'closed')->count(),
            'today' => Ticket::whereDate('created_at', today())->count(),
        ];

        return response()->json($stats);
    }
}

