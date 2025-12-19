@extends('adminlte::page')

@section('title', 'Редактировать контент: ' . $content->name)

@section('content_header')
    <h1>Редактировать контент: {{ $content->name }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-question-circle mr-2"></i>Управление FAQ
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.contents.update', $content) }}" method="POST" id="faqForm">
                        @csrf
                        @method('PUT')

                        <!-- Вкладки языков -->
                        <ul class="nav nav-tabs" id="langTabs" role="tablist">
                            @foreach(config('langs', []) as $code => $flag)
                                @php
                                    $hasError = $errors->has('value.' . $code);
                                    $isActive = $code == 'ru';
                                @endphp
                                <li class="nav-item">
                                    <a class="nav-link {{ $isActive ? 'active' : '' }} {{ $hasError ? 'text-danger' : '' }}"
                                       id="tab-{{ $code }}"
                                       data-toggle="tab"
                                       href="#content-{{ $code }}"
                                       role="tab"
                                       aria-controls="content-{{ $code }}"
                                       aria-selected="{{ $isActive ? 'true' : 'false' }}">
                                        <span class="flag-icon flag-icon-{{ $flag }} mr-1"></span>
                                        {{ strtoupper($code) }}
                                        @if($hasError)
                                            <span class="badge badge-danger ml-1">!</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <!-- Содержимое вкладок -->
                        <div class="tab-content mt-3" id="langTabContent">
                            @foreach(config('langs', []) as $code => $flag)
                                @php
                                    $isActive = $code == 'ru';
                                    $currentFaqData = $faqData[$code] ?? [];
                                    $currentValue = old('value.' . $code, $contentData[$code]['value'] ?? '[]');
                                @endphp
                                
                                <div class="tab-pane fade {{ $isActive ? 'show active' : '' }}"
                                     id="content-{{ $code }}"
                                     role="tabpanel"
                                     aria-labelledby="tab-{{ $code }}">
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <strong>Язык: {{ strtoupper($code) }}</strong> - Заполните вопросы и ответы для этого языка
                                    </div>

                                    <div class="form-group">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <label class="mb-0 font-weight-bold">
                                                <i class="fas fa-list mr-2"></i>Список вопросов и ответов
                                            </label>
                                            <span class="badge badge-primary badge-lg">
                                                Всего вопросов: <span id="faq-count-{{ $code }}">{{ count($currentFaqData) }}</span>
                                            </span>
                                        </div>
                                        
                                        <input type="hidden" 
                                               name="value[{{ $code }}]" 
                                               id="faq-json-{{ $code }}" 
                                               value="{{ htmlspecialchars($currentValue) }}">
                                        
                                        <div id="faq-container-{{ $code }}" class="faq-items-container">
                                            @if(!empty($currentFaqData))
                                                @foreach($currentFaqData as $index => $item)
                                                    <div class="faq-item-card card mb-3" data-index="{{ $index }}">
                                                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                                            <h5 class="mb-0">
                                                                <i class="fas fa-question-circle mr-2"></i>
                                                                Вопрос #<span class="item-number">{{ $index + 1 }}</span>
                                                            </h5>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-light faq-remove-btn" 
                                                                    data-locale="{{ $code }}"
                                                                    title="Удалить этот вопрос">
                                                                <i class="fas fa-trash"></i> Удалить
                                                            </button>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="form-group">
                                                                <label for="question-{{ $code }}-{{ $index }}" class="font-weight-bold">
                                                                    <i class="fas fa-question text-primary mr-1"></i>Вопрос *
                                                                </label>
                                                                <input type="text" 
                                                                       id="question-{{ $code }}-{{ $index }}"
                                                                       class="form-control faq-question-input" 
                                                                       data-locale="{{ $code }}"
                                                                       value="{{ htmlspecialchars($item['question'] ?? '') }}" 
                                                                       placeholder="Например: Как это работает?"
                                                                       required>
                                                                <small class="form-text text-muted">
                                                                    Введите краткий и понятный вопрос, который интересует пользователей
                                                                </small>
                                                            </div>
                                                            
                                                            <div class="form-group mb-0">
                                                                <label for="answer-{{ $code }}-{{ $index }}" class="font-weight-bold">
                                                                    <i class="fas fa-comment-dots text-success mr-1"></i>Ответ *
                                                                </label>
                                                                <textarea id="answer-{{ $code }}-{{ $index }}"
                                                                          class="form-control faq-answer-input" 
                                                                          data-locale="{{ $code }}"
                                                                          rows="6" 
                                                                          placeholder="Введите подробный и информативный ответ на вопрос"
                                                                          required>{{ htmlspecialchars($item['answer'] ?? '') }}</textarea>
                                                                <small class="form-text text-muted">
                                                                    Подробный ответ, который поможет пользователю понять решение
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="alert alert-warning text-center">
                                                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                                    <p class="mb-0"><strong>Нет вопросов для языка {{ strtoupper($code) }}</strong></p>
                                                    <p class="mb-0">Нажмите кнопку ниже, чтобы добавить первый вопрос</p>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="text-center mt-3 mb-3">
                                            <button type="button" 
                                                    class="btn btn-success btn-lg faq-add-btn" 
                                                    data-locale="{{ $code }}">
                                                <i class="fas fa-plus-circle mr-2"></i>Добавить новый вопрос
                                            </button>
                                        </div>
                                        
                                        @error('value.' . $code)
                                            <div class="alert alert-danger mt-2">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Кнопки действий -->
                        <div class="card-footer bg-white border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.contents.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-2"></i>Назад к списку
                                </a>
                                <div>
                                    <button type="submit" name="save" class="btn btn-info mr-2">
                                        <i class="fas fa-save mr-2"></i>Сохранить и продолжить
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-check mr-2"></i>Сохранить изменения
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @foreach(config('langs', []) as $code => $flag)
        initFaqManager('{{ $code }}');
    @endforeach
});

function initFaqManager(locale) {
    const container = document.getElementById('faq-container-' + locale);
    const hiddenInput = document.getElementById('faq-json-' + locale);
    const addButton = document.querySelector('.faq-add-btn[data-locale="' + locale + '"]');
    const countElement = document.getElementById('faq-count-' + locale);
    
    if (!container || !hiddenInput || !addButton) {
        console.error('FAQ manager elements not found for locale:', locale);
        return;
    }
    
    function updateJson() {
        const items = container.querySelectorAll('.faq-item-card');
        const data = Array.from(items).map((item, index) => {
            const questionInput = item.querySelector('.faq-question-input');
            const answerInput = item.querySelector('.faq-answer-input');
            return {
                question: questionInput ? questionInput.value.trim() : '',
                answer: answerInput ? answerInput.value.trim() : ''
            };
        }).filter(item => item.question || item.answer);
        
        hiddenInput.value = JSON.stringify(data);
        updateCount();
    }
    
    function updateCount() {
        const count = container.querySelectorAll('.faq-item-card').length;
        if (countElement) {
            countElement.textContent = count;
        }
    }
    
    function updateNumbers() {
        const items = container.querySelectorAll('.faq-item-card');
        items.forEach((item, index) => {
            const numberSpan = item.querySelector('.item-number');
            if (numberSpan) {
                numberSpan.textContent = index + 1;
            }
            // Обновляем ID полей
            const questionInput = item.querySelector('.faq-question-input');
            const answerInput = item.querySelector('.faq-answer-input');
            if (questionInput) {
                questionInput.id = 'question-' + locale + '-' + index;
            }
            if (answerInput) {
                answerInput.id = 'answer-' + locale + '-' + index;
            }
        });
    }
    
    function addItem() {
        const itemCount = container.querySelectorAll('.faq-item-card').length;
        const itemNumber = itemCount + 1;
        
        // Удаляем предупреждение, если оно есть
        const warningAlert = container.querySelector('.alert-warning');
        if (warningAlert) {
            warningAlert.remove();
        }
        
        const itemHtml = `
            <div class="faq-item-card card mb-3" data-index="${itemCount}">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-question-circle mr-2"></i>
                        Вопрос #<span class="item-number">${itemNumber}</span>
                    </h5>
                    <button type="button" 
                            class="btn btn-sm btn-light faq-remove-btn" 
                            data-locale="${locale}"
                            title="Удалить этот вопрос">
                        <i class="fas fa-trash"></i> Удалить
                    </button>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-question text-primary mr-1"></i>Вопрос *
                        </label>
                        <input type="text" 
                               class="form-control faq-question-input" 
                               data-locale="${locale}"
                               placeholder="Например: Как это работает?"
                               required>
                        <small class="form-text text-muted">
                            Введите краткий и понятный вопрос, который интересует пользователей
                        </small>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">
                            <i class="fas fa-comment-dots text-success mr-1"></i>Ответ *
                        </label>
                        <textarea class="form-control faq-answer-input" 
                                  data-locale="${locale}"
                                  rows="6" 
                                  placeholder="Введите подробный и информативный ответ на вопрос"
                                  required></textarea>
                        <small class="form-text text-muted">
                            Подробный ответ, который поможет пользователю понять решение
                        </small>
                    </div>
                </div>
            </div>
        `;
        
        const div = document.createElement('div');
        div.innerHTML = itemHtml;
        container.appendChild(div.firstElementChild);
        
        attachEvents(div.firstElementChild);
        updateNumbers();
        updateJson();
        
        // Прокрутка к новому элементу
        setTimeout(() => {
            div.firstElementChild.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            const questionInput = div.firstElementChild.querySelector('.faq-question-input');
            if (questionInput) {
                questionInput.focus();
            }
        }, 100);
    }
    
    function attachEvents(item) {
        const removeBtn = item.querySelector('.faq-remove-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                if (confirm('Вы уверены, что хотите удалить этот вопрос? Это действие нельзя отменить.')) {
                    item.remove();
                    updateNumbers();
                    updateJson();
                    
                    // Показываем предупреждение, если не осталось вопросов
                    if (container.querySelectorAll('.faq-item-card').length === 0) {
                        const warningHtml = `
                            <div class="alert alert-warning text-center">
                                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                <p class="mb-0"><strong>Нет вопросов для языка ${locale.toUpperCase()}</strong></p>
                                <p class="mb-0">Нажмите кнопку ниже, чтобы добавить первый вопрос</p>
                            </div>
                        `;
                        container.innerHTML = warningHtml;
                    }
                }
            });
        }
        
        const questionInput = item.querySelector('.faq-question-input');
        const answerInput = item.querySelector('.faq-answer-input');
        
        if (questionInput) {
            questionInput.addEventListener('input', function() {
                updateJson();
                validateField(this);
            });
        }
        
        if (answerInput) {
            answerInput.addEventListener('input', function() {
                updateJson();
                validateField(this);
            });
        }
    }
    
    function validateField(field) {
        if (field.value.trim() === '') {
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    }
    
    // Привязываем события к существующим элементам
    container.querySelectorAll('.faq-item-card').forEach(item => {
        attachEvents(item);
    });
    
    // Обновляем счетчик при загрузке
    updateCount();
    
    // Добавляем новый элемент
    addButton.addEventListener('click', function() {
        addItem();
    });
    
    // Обновляем JSON при отправке формы
    const form = document.getElementById('faqForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            updateJson();
            
            // Проверка на пустые вопросы
            const items = container.querySelectorAll('.faq-item-card');
            let hasErrors = false;
            const errorMessages = [];
            
            items.forEach((item, index) => {
                const questionInput = item.querySelector('.faq-question-input');
                const answerInput = item.querySelector('.faq-answer-input');
                
                if (!questionInput || !questionInput.value.trim()) {
                    hasErrors = true;
                    if (questionInput) {
                        questionInput.classList.add('is-invalid');
                        errorMessages.push(`Вопрос #${index + 1}: поле "Вопрос" обязательно для заполнения`);
                    }
                }
                
                if (!answerInput || !answerInput.value.trim()) {
                    hasErrors = true;
                    if (answerInput) {
                        answerInput.classList.add('is-invalid');
                        errorMessages.push(`Вопрос #${index + 1}: поле "Ответ" обязательно для заполнения`);
                    }
                }
            });
            
            if (hasErrors) {
                e.preventDefault();
                alert('Пожалуйста, заполните все обязательные поля перед сохранением:\n\n' + errorMessages.join('\n'));
                
                // Прокрутка к первой ошибке
                const firstInvalid = container.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
                
                return false;
            }
        });
    }
}
</script>

<style>
.faq-item-card {
    transition: all 0.3s ease;
    border: 2px solid #dee2e6;
}

.faq-item-card:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.2);
}

.faq-item-card .card-header {
    font-weight: 600;
}

.faq-items-container {
    min-height: 100px;
}

.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 1rem;
}

.form-control.is-invalid {
    border-color: #dc3545;
}

.form-control.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
</style>
@endsection
