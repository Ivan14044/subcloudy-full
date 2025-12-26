@extends('adminlte::page')

@section('title', 'Обращения клиентов')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

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
                    <div class="mb-3 d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary mass-action-btn" data-action="close" disabled>
                            <i class="fas fa-check-circle"></i> Закрыть выбранные
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info mass-action-btn" data-action="open" disabled>
                            <i class="fas fa-envelope-open"></i> Открыть выбранные
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger mass-action-btn" data-action="delete" disabled>
                            <i class="fas fa-trash"></i> Удалить выбранные
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="tickets-table" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th style="width: 30px"><input type="checkbox" id="select-all"></th>
                                <th style="width: 40px">ID</th>
                                <th>Пользователь</th>
                                <th>Email</th>
                                <th>Статус</th>
                                <th>Канал</th>
                                <th>Последнее сообщение</th>
                                <th>Обновлено</th>
                                <th style="width: 100px">Действие</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($tickets as $ticket)
                                <tr data-id="{{ $ticket->id }}">
                                    <td><input type="checkbox" class="ticket-checkbox" value="{{ $ticket->id }}"></td>
                                    <td>{{ $ticket->id }}</td>
                                    <td>
                                        @if($ticket->user)
                                            {{ $ticket->user->name }}
                                        @else
                                            <span class="text-muted">Гость</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ticket->user)
                                            {{ $ticket->user->email }}
                                        @elseif($ticket->guest_email)
                                            {{ $ticket->guest_email }}
                                        @elseif($ticket->external_channel === 'telegram' || $ticket->telegram_chat_id)
                                            <span class="text-info"><i class="fab fa-telegram"></i> Telegram Guest</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="status-cell">
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
                                    <td data-order="{{ strtotime($ticket->updated_at) }}">
                                        {{ \Carbon\Carbon::parse($ticket->updated_at)->format('Y-m-d H:i') }}
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            // Звук для уведомлений
            const notificationSound = new Audio('/sounds/notification.mp3');
            let lastTicketCount = {{ $tickets->total() }};
            let lastUpdate = Date.now();

            // Проверяем, не инициализирована ли таблица уже
            if ($.fn.DataTable.isDataTable('#tickets-table')) {
                $('#tickets-table').DataTable().destroy();
            }
            
            const table = $('#tickets-table').DataTable({
                "paging": false,
                "searching": false,
                "info": false,
                "order": [[7, "desc"]],
                "columnDefs": [
                    { "orderable": false, "targets": 0 }
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Russian.json"
                }
            });

            // Поллинг для проверки новых тикетов
            function checkNewTickets() {
                $.ajax({
                    url: '{{ route("admin.support.stats") }}',
                    method: 'GET',
                    success: function(data) {
                        // Проверяем наличие новых тикетов
                        const openCount = data.open || 0;
                        const totalCount = data.total || 0;
                        
                        if (totalCount > lastTicketCount) {
                            // Новый тикет!
                            notificationSound.play().catch(e => console.warn('Audio play failed', e));
                            
                            toastr.options = {
                                "closeButton": true,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "timeOut": "10000"
                            };
                            toastr.info('Новое обращение в техподдержку!', 'Уведомление', {
                                onclick: function() {
                                    location.reload();
                                }
                            });
                            
                            lastTicketCount = totalCount;
                            
                            // Обновляем страницу через 2 секунды
                            setTimeout(() => location.reload(), 2000);
                        }
                        
                        lastUpdate = Date.now();
                    },
                    error: function(xhr) {
                        console.error('Polling error:', xhr);
                    }
                });
            }

            // Запускаем поллинг каждые 10 секунд
            setInterval(checkNewTickets, 10000);

            // Массовое выделение
            $('#select-all').on('click', function() {
                const rows = table.rows({ 'search': 'applied' }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
                updateMassActionButtons();
            });

            $('#tickets-table tbody').on('change', 'input[type="checkbox"]', function() {
                if (!this.checked) {
                    const el = $('#select-all').get(0);
                    if (el && el.checked && ('indeterminate' in el)) {
                        el.indeterminate = true;
                    }
                }
                updateMassActionButtons();
            });

            function updateMassActionButtons() {
                const selectedCount = $('.ticket-checkbox:checked').length;
                $('.mass-action-btn').prop('disabled', selectedCount === 0);
            }

            // Обработка массовых действий
            $('.mass-action-btn').on('click', function() {
                const action = $(this).data('action');
                const selectedIds = $('.ticket-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (!selectedIds.length) return;

                let confirmMessage = 'Вы уверены?';
                if (action === 'delete') confirmMessage = 'Удалить выбранные обращения? Это действие необратимо.';
                if (action === 'close') confirmMessage = 'Закрыть выбранные обращения?';

                if (confirm(confirmMessage)) {
                    $.ajax({
                        url: '{{ route("admin.support.mass-action") }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            ids: selectedIds,
                            action: action
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                toastr.error(response.message || 'Ошибка при выполнении действия');
                            }
                        },
                        error: function(xhr) {
                            const error = xhr.responseJSON ? xhr.responseJSON.message : 'Произошла ошибка';
                            toastr.error(error);
                        }
                    });
                }
            });
        });
    </script>
    @endpush
@stop
