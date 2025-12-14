@extends('adminlte::page')

@section('title', __('admin.activity_history'))

@section('content_header')
    <h1>{{ __('admin.activity_history') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {!! session('success') !!}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {!! session('error') !!}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.activity_history') }}</h3>
                </div>
                <div class="card-body">
                    <!-- Фильтры -->
                    <form method="GET" action="{{ route('admin.browser-sessions.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('admin.user') }}</label>
                                    <select name="user_id" class="form-control">
                                        <option value="">{{ __('admin.all') }}</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name ?? $user->email }} (ID: {{ $user->id }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>{{ __('admin.service') }}</label>
                                    <select name="service_id" class="form-control" style="z-index: 1000;">
                                        <option value="">{{ __('admin.all') }}</option>
                                        @foreach($services as $service)
                                            @php
                                                $serviceName = $service->name ?? "Service {$service->id}";
                                                if (method_exists($service, 'getTranslation')) {
                                                    try {
                                                        $serviceName = $service->getTranslation('name', app()->getLocale()) 
                                                            ?? $service->getTranslation('name', 'en') 
                                                            ?? $service->name 
                                                            ?? "Service {$service->id}";
                                                    } catch (\Exception $e) {
                                                        $serviceName = $service->name ?? "Service {$service->id}";
                                                    }
                                                }
                                            @endphp
                                            <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                                {{ $serviceName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>{{ __('admin.action') }}</label>
                                    <select name="action" class="form-control">
                                        <option value="">{{ __('admin.all') }}</option>
                                        <option value="session_started" {{ request('action') == 'session_started' ? 'selected' : '' }}>Запущен</option>
                                        <option value="session_stopped" {{ request('action') == 'session_stopped' ? 'selected' : '' }}>Остановлен</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>{{ __('admin.start_date') }}</label>
                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>{{ __('admin.end_date') }}</label>
                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">{{ __('admin.filter') }}</button>
                                <a href="{{ route('admin.browser-sessions.index') }}" class="btn btn-secondary">{{ __('admin.reset') }}</a>
                            </div>
                        </div>
                    </form>

                    <!-- Таблица истории -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                            <tr>
                                <th style="width: 180px">{{ __('admin.date_time') }}</th>
                                <th style="width: 200px">{{ __('admin.user') }}</th>
                                <th style="width: 200px">{{ __('admin.service') }}</th>
                                <th style="width: 120px">{{ __('admin.action') }}</th>
                                <th style="width: 120px">{{ __('admin.duration') }}</th>
                                <th style="width: 120px">IP</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ \Carbon\Carbon::createFromTimestampMs($log->timestamp)->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        @if($log->user)
                                            <a href="{{ route('admin.users.edit', $log->user_id) }}" target="_blank">
                                                {{ $log->user->name ?? $log->user->email }} (ID: {{ $log->user_id }})
                                            </a>
                                        @else
                                            ID: {{ $log->user_id }}
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            // Приоритет: service_name из записи лога (переданное из desktop app)
                                            $serviceName = $log->service_name;
                                            
                                            // Если service_name не указан, пытаемся получить из связанной модели Service
                                            if (!$serviceName && $log->service) {
                                                try {
                                                    $serviceName = $log->service->getTranslation('name', 'ru') 
                                                        ?? $log->service->getTranslation('name', 'en') 
                                                        ?? $log->service->name 
                                                        ?? "Service {$log->service_id}";
                                                } catch (\Exception $e) {
                                                    $serviceName = $log->service->name ?? "Service {$log->service_id}";
                                                }
                                            }
                                            
                                            // Если все еще нет названия, используем дефолтное
                                            if (!$serviceName) {
                                                $serviceName = "Service {$log->service_id}";
                                            }
                                        @endphp
                                        {{ $serviceName }}
                                    </td>
                                    <td>
                                        @if($log->action === 'session_started')
                                            <span class="badge badge-success">Запущен</span>
                                        @elseif($log->action === 'session_stopped')
                                            <span class="badge badge-secondary">Остановлен</span>
                                        @else
                                            <span class="badge badge-info">{{ $log->action }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->action === 'session_stopped' && $log->duration !== null && $log->duration >= 0)
                                            @php
                                                $seconds = $log->duration;
                                                $days = intdiv($seconds, 86400);
                                                $seconds %= 86400;
                                                $hours = intdiv($seconds, 3600);
                                                $seconds %= 3600;
                                                $minutes = intdiv($seconds, 60);
                                                $seconds %= 60;
                                                $parts = [];
                                                if ($days > 0) $parts[] = $days . 'д';
                                                if ($hours > 0) $parts[] = $hours . 'ч';
                                                if ($minutes > 0) $parts[] = $minutes . 'м';
                                                if ($seconds > 0 || empty($parts)) $parts[] = $seconds . 'с';
                                            @endphp
                                            {{ implode(' ', $parts) }}
                                        @elseif($log->action === 'session_started')
                                            <span class="text-muted">—</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->ip ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        {{ __('admin.no_records') }}
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div class="mt-3">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
