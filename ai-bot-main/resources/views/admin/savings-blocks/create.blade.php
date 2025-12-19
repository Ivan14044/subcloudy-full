@extends('adminlte::page')

@section('title', 'Создать блок экономии')

@section('content_header')
    <h1>Создать блок экономии</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.savings-blocks.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="service_id">Сервис</label>
                            <select name="service_id" id="service_id" class="form-control">
                                <option value="">-- Не выбран --</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                        {{ $service->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="title">Заголовок *</label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="text">Текст</label>
                            <textarea name="text" id="text" class="form-control @error('text') is-invalid @enderror" rows="3">{{ old('text') }}</textarea>
                            @error('text')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="logo">Логотип</label>
                            <input type="file" name="logo" id="logo" class="form-control-file @error('logo') is-invalid @enderror">
                            @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="our_price">Наша цена</label>
                            <input type="text" name="our_price" id="our_price" class="form-control @error('our_price') is-invalid @enderror" value="{{ old('our_price') }}">
                            @error('our_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="normal_price">Обычная цена</label>
                            <input type="text" name="normal_price" id="normal_price" class="form-control @error('normal_price') is-invalid @enderror" value="{{ old('normal_price') }}">
                            @error('normal_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="advantage">Преимущество</label>
                            <textarea name="advantage" id="advantage" class="form-control @error('advantage') is-invalid @enderror" rows="2">{{ old('advantage') }}</textarea>
                            @error('advantage')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="locale">Язык *</label>
                            <select name="locale" id="locale" class="form-control @error('locale') is-invalid @enderror" required>
                                <option value="ru" {{ old('locale', 'ru') == 'ru' ? 'selected' : '' }}>Русский</option>
                                <option value="en" {{ old('locale') == 'en' ? 'selected' : '' }}>English</option>
                                <option value="uk" {{ old('locale') == 'uk' ? 'selected' : '' }}>Українська</option>
                                <option value="es" {{ old('locale') == 'es' ? 'selected' : '' }}>Español</option>
                                <option value="zh" {{ old('locale') == 'zh' ? 'selected' : '' }}>中文</option>
                            </select>
                            @error('locale')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="order">Порядок отображения</label>
                            <input type="number" name="order" id="order" class="form-control @error('order') is-invalid @enderror" value="{{ old('order', 0) }}">
                            @error('order')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Активен</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Создать</button>
                        <a href="{{ route('admin.savings-blocks.index') }}" class="btn btn-secondary">Отмена</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

