@extends('adminlte::page')

@section('title', 'Добавить отзыв')

@section('content_header')
    <h1>Добавить отзыв</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.reviews.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="rating">Рейтинг (1-5)</label>
                            <input type="number" name="rating" id="rating" class="form-control @error('rating') is-invalid @enderror" value="{{ old('rating', 5) }}" min="1" max="5">
                            @error('rating')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="order">Порядок сортировки</label>
                            <input type="number" name="order" id="order" class="form-control @error('order') is-invalid @enderror" value="{{ old('order', 0) }}">
                            @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Активен</label>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header p-0 pt-1">
                                <ul class="nav nav-tabs" id="langs-tabs" role="tablist">
                                    @foreach (config('langs') as $code => $flag)
                                        <li class="nav-item">
                                            <a class="nav-link {{ $code == 'ru' ? 'active' : '' }}" id="tab-{{ $code }}" data-toggle="pill" href="#content-{{ $code }}" role="tab">
                                                <span class="flag-icon flag-icon-{{ $flag }}"></span> {{ strtoupper($code) }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content" id="langs-tabs-content">
                                    @foreach (config('langs') as $code => $flag)
                                        <div class="tab-pane fade {{ $code == 'ru' ? 'show active' : '' }}" id="content-{{ $code }}" role="tabpanel">
                                            <div class="form-group">
                                                <label>Имя</label>
                                                <input type="text" name="name[{{ $code }}]" class="form-control @error("name.$code") is-invalid @enderror" value="{{ old("name.$code") }}">
                                                @error("name.$code")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Текст отзыва</label>
                                                <textarea name="text[{{ $code }}]" class="form-control @error("text.$code") is-invalid @enderror" rows="3">{{ old("text.$code") }}</textarea>
                                                @error("text.$code")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Фото пользователя</label>
                                                <input type="file" name="photo[{{ $code }}]" class="form-control-file @error("photo.$code") is-invalid @enderror">
                                                @error("photo.$code")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Логотип сервиса</label>
                                                <input type="file" name="logo[{{ $code }}]" class="form-control-file @error("logo.$code") is-invalid @enderror">
                                                @error("logo.$code")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Создать</button>
                        <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">Отмена</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

