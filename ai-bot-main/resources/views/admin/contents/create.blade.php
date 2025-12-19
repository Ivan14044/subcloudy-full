@extends('adminlte::page')

@section('title', __('admin.add_content'))

@section('content_header')
    <h1>{{ __('admin.add_content') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.content_data') }}</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Инструкция:</strong> Выберите тип контента из списка ниже или создайте свой. 
                        После создания вы сможете добавить блоки с данными для каждого языка.
                    </div>

                    <form method="POST" action="{{ route('admin.contents.store') }}" enctype="multipart/form-data" id="contentForm">
                        @csrf
                        
                        <div class="form-group">
                            <label for="content_type">Тип контента <span class="text-danger">*</span></label>
                            <select name="content_type" id="content_type" class="form-control @error('content_type') is-invalid @enderror" required>
                                <option value="">-- Выберите тип или создайте свой --</option>
                                @foreach(config('contents') as $code => $config)
                                    <option value="{{ $code }}" data-name="{{ ucfirst(str_replace('_', ' ', $code)) }}">
                                        {{ ucfirst(str_replace('_', ' ', $code)) }} 
                                        <small>({{ count($config['fields']) }} полей)</small>
                                    </option>
                                @endforeach
                                <option value="custom">-- Создать свой тип --</option>
                            </select>
                            <small class="form-text text-muted">
                                Выберите готовый тип или создайте свой. Готовые типы: 
                                <strong>homepage_reviews</strong> (отзывы), 
                                <strong>saving_on_subscriptions</strong> (блоки экономии)
                            </small>
                            @error('content_type')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="name">Название <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" 
                                   placeholder="Например: Отзывы на главной странице"
                                   required>
                            <small class="form-text text-muted">Понятное название для отображения в списке</small>
                            @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="code">Код (уникальный идентификатор) <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="code"
                                   class="form-control @error('code') is-invalid @enderror" 
                                   value="{{ old('code') }}"
                                   placeholder="Например: homepage_reviews"
                                   pattern="[a-z0-9_]+"
                                   required>
                            <small class="form-text text-muted">
                                Только строчные латинские буквы, цифры и подчеркивание. 
                                Этот код используется в API и должен быть уникальным.
                            </small>
                            @error('code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="contentTypeInfo" class="alert alert-secondary" style="display: none;">
                            <strong>Поля этого типа:</strong>
                            <ul id="fieldsList" class="mb-0 mt-2"></ul>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> {{ __('admin.create') }}
                            </button>
                            <a href="{{ route('admin.contents.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> {{ __('admin.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    var contentTypes = {};
    @if(config('contents'))
        @foreach(config('contents') as $code => $config)
            contentTypes['{{ $code }}'] = @json($config);
        @endforeach
    @endif
    
    $('#content_type').on('change', function() {
        var selectedType = $(this).val();
        var $nameInput = $('#name');
        var $codeInput = $('#code');
        var $infoDiv = $('#contentTypeInfo');
        var $fieldsList = $('#fieldsList');
        
        if (selectedType === 'custom') {
            $nameInput.val('').prop('readonly', false);
            $codeInput.val('').prop('readonly', false);
            $infoDiv.hide();
            return;
        }
        
        if (selectedType && contentTypes[selectedType]) {
            var config = contentTypes[selectedType];
            var $option = $(this).find('option:selected');
            var name = $option.data('name') || selectedType.replace(/_/g, ' ');
            
            $nameInput.val(name);
            $codeInput.val(selectedType).prop('readonly', true);
            
            // Показываем информацию о полях
            $fieldsList.empty();
            $.each(config.fields, function(key, field) {
                var typeLabel = field.type === 'file' ? 'Файл' : 
                                 field.type === 'number' ? 'Число' : 
                                 field.type === 'service' ? 'Сервис' : 'Текст';
                $fieldsList.append(
                    '<li><strong>' + field.label + '</strong> (' + typeLabel + ')' + 
                    (field.multiline ? ' <em>многострочное</em>' : '') + '</li>'
                );
            });
            $infoDiv.show();
        } else {
            $nameInput.prop('readonly', false);
            $codeInput.prop('readonly', false);
            $infoDiv.hide();
        }
    });
    
    // Автозаполнение при вводе кода вручную
    $('#code').on('input', function() {
        if (!$('#content_type').val() || $('#content_type').val() === 'custom') {
            var code = $(this).val();
            if (code && !$('#name').val()) {
                var name = code.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
                $('#name').val(name);
            }
        }
    });
});
</script>
@endsection
