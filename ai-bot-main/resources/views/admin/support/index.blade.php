@extends('adminlte::page')

@section('title', 'Обращения клиентов')

@section('content_header')
    <h1>Обращения клиентов</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Список обращений</h3>
                    <div class="card-tools">
                        <form method="GET" class="d-inline-flex align-items-center gap-2">
                            <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="">Все статусы</option>
                                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Открыто</option>
                                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>В работе</option>
                                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Закрыто</option>
                            </select>
                            
                            <select name="channel" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="">Все каналы</option>
                                <option value="web" {{ request('channel') === 'web' ? 'selected' : '' }}>Web</option>
                                <option value="telegram" {{ request('channel') === 'telegram' ? 'selected' : '' }}>Telegram</option>
                                <option value="both" {{ request('channel') === 'both' ? 'selected' : '' }}>Web + Telegram</option>
                            </select>
                            
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Поиск..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-sm btn-primary">Поиск</button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tickets-table" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th style="width: 40px">ID</th>
                                <th>Пользователь</th>
                                <th>Email</th>
                                <th>Статус</th>
                                <th>Канал</th>
                                <th>Последнее сообщение</th>
                                <th>Создано</th>
                                <th style="width: 100px">Действие</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($tickets as $ticket)
                                <tr>
                                    <td>{{ $ticket->id }}</td>
                                    <td>
                                        @if($ticket->user)
                                            {{ $ticket->user->name }}
                                        @else
                                            <span class="text-muted">Гость</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $ticket->user ? $ticket->user->email : ($ticket->guest_email ?? 'N/A') }}
                                    </td>
                                    <td>
                                        @if($ticket->status === 'open')
                                            <span class="badge badge-warning">Открыто</span>
                                        @elseif($ticket->status === 'in_progress')
                                            <span class="badge badge-info">В работе</span>
                                        @else
                                            <span class="badge badge-success">Закрыто</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ticket->telegram_chat_id && ($ticket->external_channel === 'web' || !$ticket->external_channel))
                                            <span class="badge badge-primary">Web + Telegram</span>
                                        @elseif($ticket->external_channel === 'telegram')
                                            <span class="badge badge-info">Telegram</span>
                                        @else
                                            <span class="badge badge-secondary">Web</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ticket->lastMessage)
                                            <small class="text-muted">
                                                {{ Str::limit($ticket->lastMessage->text, 50) }}
                                                <br>
                                                <span class="text-xs">{{ \Carbon\Carbon::parse($ticket->lastMessage->created_at)->diffForHumans() }}</span>
                                            </small>
                                        @else
                                            <span class="text-muted">Нет сообщений</span>
                                        @endif
                                    </td>
                                    <td data-order="{{ strtotime($ticket->created_at) }}">
                                        {{ \Carbon\Carbon::parse($ticket->created_at)->format('Y-m-d H:i') }}
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.support.show', $ticket->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Открыть
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $tickets->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('js')
    <script>
        $(document).ready(function() {
            // Проверяем, не инициализирована ли таблица уже
            if ($.fn.DataTable.isDataTable('#tickets-table')) {
                $('#tickets-table').DataTable().destroy();
            }
            
            $('#tickets-table').DataTable({
                "paging": false,
                "searching": false,
                "info": false,
                "order": [[6, "desc"]],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Russian.json"
                }
            });
        });
    </script>
    @endpush
@stop

