<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DesktopActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class BrowserSessionController extends Controller
{

    public function index(Request $request)
    {
        $perPage = 50;
        $page = $request->get('page', 1);
        
        $query = DesktopActivityLog::with(['user', 'service'])
            ->orderBy('timestamp', 'desc');

        // Фильтры
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('date_from')) {
            $query->where('timestamp', '>=', strtotime($request->date_from) * 1000);
        }
        if ($request->filled('date_to')) {
            $query->where('timestamp', '<=', (strtotime($request->date_to) + 86400) * 1000 - 1);
        }

        $logs = $query->paginate($perPage, ['*'], 'page', $page);

        // Получаем список пользователей и сервисов для фильтров
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        $services = \App\Models\Service::withEnglishName()->orderBy('id', 'desc')->get();

        return view('admin.browser-sessions.index', [
            'logs' => $logs,
            'users' => $users,
            'services' => $services,
            'filters' => $request->only(['user_id', 'service_id', 'action', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Форматирование длительности
     */
    private function formatDuration($seconds): ?string
    {
        if ($seconds === null) return null;
        $seconds = (int)$seconds;
        if ($seconds < 0) $seconds = 0;

        $days = intdiv($seconds, 86400);
        $seconds %= 86400;
        $hours = intdiv($seconds, 3600);
        $seconds %= 3600;
        $minutes = intdiv($seconds, 60);
        $seconds %= 60;

        $parts = [];
        if ($days > 0) {
            $parts[] = $days . ' ' . ($days === 1 ? 'день' : 'дней');
        }
        if ($hours > 0) {
            $parts[] = $hours . ' ' . ($hours === 1 ? 'час' : 'часов');
        }
        if ($minutes > 0) {
            $parts[] = $minutes . ' ' . ($minutes === 1 ? 'мин' : 'мин');
        }
        if ($seconds > 0 || empty($parts)) {
            $parts[] = $seconds . ' ' . ($seconds === 1 ? 'сек' : 'сек');
        }

        return implode(' ', $parts);
    }

}



