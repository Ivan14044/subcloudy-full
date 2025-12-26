@extends('adminlte::page')

@section('title', 'Обращение #' . $ticket->id)

@section('content_header')
    <h1>Обращение #{{ $ticket->id }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Диалог</h3>
                        </div>
                        <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                            @foreach($ticket->messages as $message)
                                <div class="mb-3">
                                    <div class="d-flex {{ $message->sender_type === 'admin' ? 'justify-content-end' : 'justify-content-start' }}">
                                        <div class="message-bubble {{ $message->sender_type === 'admin' ? 'bg-primary text-white' : 'bg-light' }}" style="max-width: 70%; padding: 10px; border-radius: 10px;">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <strong>
                                                    @if($message->sender_type === 'admin')
                                                        Администратор
                                                    @else
                                                        {{ $ticket->user ? $ticket->user->name : 'Гость' }}
                                                    @endif
                                                </strong>
                                                <small class="ml-2">
                                                    {{ \Carbon\Carbon::parse($message->created_at)->format('d.m.Y H:i') }}
                                                    @if($message->source === 'telegram')
                                                        <i class="fab fa-telegram" title="Telegram"></i>
                                                    @else
                                                        <i class="fas fa-globe" title="Web"></i>
                                                    @endif
                                                </small>
                                            </div>
                                            <div>
                                                {{ $message->text }}
                                                @if($message->image_path)
                                                    <div class="mt-2">
                                                        <a href="{{ asset('storage/' . $message->image_path) }}" target="_blank">
                                                            <img src="{{ asset('storage/' . $message->image_path) }}" class="img-fluid rounded" style="max-height: 200px;">
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="card-footer">
                            <form method="POST" action="{{ route('admin.support.send-message', $ticket->id) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="template-select">Быстрый ответ:</label>
                                    <select id="template-select" class="form-control mb-2">
                                        <option value="">-- Выбрать шаблон --</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <textarea name="text" id="message-text" class="form-control" rows="3" placeholder="Введите ваш ответ..."></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="image">Прикрепить изображение:</label>
                                    <input type="file" name="image" class="form-control-file" accept="image/*">
                                </div>
                                <div class="alert alert-info py-2 px-3 small">
                                    <i class="fas fa-info-circle"></i> 
                                    Ответ будет автоматически отправлен по каналу: 
                                    <strong>
                                        @php
                                            $lastClientMsg = $ticket->messages()->where('sender_type', 'client')->latest()->first();
                                            $source = $lastClientMsg ? $lastClientMsg->source : ($ticket->external_channel ?? 'web');
                                        @endphp
                                        @if($source === 'telegram')
                                            <i class="fab fa-telegram text-primary"></i> Telegram
                                        @else
                                            <i class="fas fa-globe text-muted"></i> На сайт (Web)
                                        @endif
                                    </strong>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Отправить ответ
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Информация</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>ID:</strong> {{ $ticket->id }}</p>
                            <p><strong>Пользователь:</strong> 
                                @if($ticket->user)
                                    {{ $ticket->user->name }} ({{ $ticket->user->email }})
                                @elseif($ticket->guest_email)
                                    Гость ({{ $ticket->guest_email }})
                                @elseif($ticket->external_channel === 'telegram' || $ticket->telegram_chat_id)
                                    <span class="text-info"><i class="fab fa-telegram"></i> Telegram Guest</span>
                                @else
                                    Гость (N/A)
                                @endif
                            </p>
                            <p><strong>Статус:</strong> 
                                @if($ticket->status === 'open')
                                    <span class="badge badge-warning">Открыто</span>
                                @elseif($ticket->status === 'in_progress')
                                    <span class="badge badge-info">В работе</span>
                                @else
                                    <span class="badge badge-success">Закрыто</span>
                                @endif
                            </p>
                            <p><strong>Канал:</strong> 
                                @if($ticket->telegram_chat_id && ($ticket->external_channel === 'web' || !$ticket->external_channel))
                                    <span class="badge badge-primary">Web + Telegram</span>
                                @elseif($ticket->external_channel === 'telegram')
                                    <span class="badge badge-info">Telegram</span>
                                @else
                                    <span class="badge badge-secondary">Web</span>
                                @endif
                            </p>
                            @if($ticket->telegram_chat_id)
                                <p><strong>Telegram Chat ID:</strong> {{ $ticket->telegram_chat_id }}</p>
                            @endif
                            <p><strong>Создано:</strong> {{ \Carbon\Carbon::parse($ticket->created_at)->format('d.m.Y H:i') }}</p>
                            <p><strong>Обновлено:</strong> {{ \Carbon\Carbon::parse($ticket->updated_at)->format('d.m.Y H:i') }}</p>
                            <p><strong>Сообщений:</strong> {{ $ticket->messages->count() }}</p>
                            
                            <hr>
                            
                            <form method="POST" action="{{ route('admin.support.update-status', $ticket->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label>Изменить статус:</label>
                                    <select name="status" class="form-control">
                                        <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Открыто</option>
                                        <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>В работе</option>
                                        <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Закрыто</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-sm btn-warning">
                                    <i class="fas fa-save"></i> Сохранить
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Действия</h3>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('admin.support.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Назад к списку
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
    $(document).ready(function() {
        console.log('Support Chat: Polling initialized');
        let lastMessageId = {{ $ticket->messages->last() ? $ticket->messages->last()->id : 0 }};
        const audio = new Audio('/sounds/notification.mp3');
        
        // --- Шаблоны быстрых ответов ---
        $.get('{{ route('admin.support-templates.api') }}', { lang: 'ru' }, function(data) {
            if (data.success && data.templates) {
                data.templates.forEach(function(template) {
                    $('#template-select').append(`<option value="${template.id}">${template.title}</option>`);
                });
            }
        });
        
        $('#template-select').on('change', function() {
            const selectedId = $(this).val();
            if (!selectedId) return;
            
            $.get('{{ route('admin.support-templates.api') }}', { lang: 'ru' }, function(data) {
                if (data.success && data.templates) {
                    const template = data.templates.find(t => t.id == selectedId);
                    if (template) {
                        $('#message-text').val(template.text);
                        $('#template-select').val('');
                    }
                }
            });
        });
        // ------------------------------

        const messagesContainer = $('#chat-messages-container').length 
            ? $('#chat-messages-container') 
            : $('.card-body').first();
        
        function pollMessages() {
            $.get('{{ route('admin.support.new-messages', $ticket->id) }}', { 
                last_message_id: lastMessageId,
                channel: 'web' 
            })
            .done(function(data) {
                if (data.success && data.messages && data.messages.length > 0) {
                    console.log('Support Chat: New messages received', data.messages.length);
                    let hasClientMessage = false;
                    
                    data.messages.forEach(function(message) {
                        const isAdmin = message.sender_type === 'admin';
                        if (!isAdmin) hasClientMessage = true;
                        
                        const alignment = isAdmin ? 'justify-content-end' : 'justify-content-start';
                        const bgClass = isAdmin ? 'bg-primary text-white' : 'bg-light';
                        const userName = isAdmin ? 'Администратор' : '{{ $ticket->user ? $ticket->user->name : "Гость" }}';
                        
                        const dateObj = new Date(message.created_at);
                        const formattedDate = dateObj.toLocaleString('ru-RU', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        
                        const sourceIcon = message.source === 'telegram' 
                            ? '<i class="fab fa-telegram" title="Telegram"></i>' 
                            : '<i class="fas fa-globe" title="Web"></i>';
                        
                        const imageHtml = message.image_url 
                            ? `<div class="mt-2"><a href="${message.image_url}" target="_blank"><img src="${message.image_url}" class="img-fluid rounded" style="max-height: 200px;"></a></div>`
                            : '';
                        
                        const messageHtml = `
                            <div class="mb-3">
                                <div class="d-flex ${alignment}">
                                    <div class="message-bubble ${bgClass}" style="max-width: 70%; padding: 10px; border-radius: 10px;">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <strong>${userName}</strong>
                                            <small class="ml-2">${formattedDate} ${sourceIcon}</small>
                                        </div>
                                        <div>${message.text || ''}${imageHtml}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        messagesContainer.append(messageHtml);
                        lastMessageId = message.id;
                    });
                    
                    if (hasClientMessage) {
                        audio.play().catch(e => console.warn('Audio play failed', e));
                        if (typeof toastr !== 'undefined') {
                            toastr.success('Новое сообщение от клиента', 'Поддержка');
                        }
                    }
                    
                    messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
                }
                
                if (data.status) {
                    const badge = $('.ticket-status-badge');
                    if (badge.length) {
                        badge.removeClass('badge-warning badge-info badge-success');
                        let statusClass = data.status === 'in_progress' ? 'badge-info' : (data.status === 'closed' ? 'badge-success' : 'badge-warning');
                        let statusTexts = {'open': 'Открыто', 'in_progress': 'В работе', 'closed': 'Закрыто'};
                        badge.addClass(statusClass).text(statusTexts[data.status] || data.status);
                    }
                }
            })
            .fail(function(err) {
                console.error('Support Chat: Polling failed', err);
            });
        }
        
        // Поллинг каждые 3 секунды
        setInterval(pollMessages, 3000);
    });
</script>
@endpush

