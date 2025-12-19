@extends('adminlte::page')

@section('title', 'Создать отзыв')

@section('content_header')
    <h1>Создать отзыв</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.reviews.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="form-group">
                            <label for="rating">Рейтинг (1-5) *</label>
                            <input type="number" name="rating" id="rating" class="form-control @error('rating') is-invalid @enderror" value="{{ old('rating', 5) }}" min="1" max="5" required>
                            @error('rating')
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

                        <hr>

                        <div class="card">
                            <div class="card-header no-border border-0 p-0">
                                <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                                    @foreach(config('langs') as $code => $flag)
                                        @php($hasError = $errors->has('name.' . $code) || $errors->has('text.' . $code))
                                        <li class="nav-item">
                                            <a class="nav-link @if($hasError) text-danger @endif {{ $code == 'ru' ? 'active' : null }}"
                                               id="tab_{{ $code }}" data-toggle="pill" href="#content_{{ $code }}" role="tab">
                                                <span class="flag-icon flag-icon-{{ $flag }} mr-1"></span> {{ strtoupper($code) }}  @if($hasError)*@endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content">
                                    @foreach(config('langs') as $code => $flag)
                                        <div class="tab-pane fade show {{ $code == 'ru' ? 'active' : null }}" id="content_{{ $code }}" role="tabpanel">
                                            <div class="form-group">
                                                <label for="name_{{ $code }}">Имя автора *</label>
                                                <input type="text" name="name[{{ $code }}]" id="name_{{ $code }}"
                                                       class="form-control @error('name.' . $code) is-invalid @enderror"
                                                       value="{{ old('name.' . $code) }}" required>
                                                @error('name.' . $code)
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="text_{{ $code }}">Текст отзыва *</label>
                                                <textarea name="text[{{ $code }}]" id="text_{{ $code }}"
                                                          class="form-control @error('text.' . $code) is-invalid @enderror"
                                                          rows="5" required>{{ old('text.' . $code) }}</textarea>
                                                @error('text.' . $code)
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="photo_{{ $code }}">Фото автора</label>
                                                <input type="file" name="photo[{{ $code }}]" id="photo_{{ $code }}"
                                                       class="form-control-file @error('photo.' . $code) is-invalid @enderror"
                                                       accept="image/*">
                                                @error('photo.' . $code)
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="logo_{{ $code }}">Логотип компании</label>
                                                <input type="file" name="logo[{{ $code }}]" id="logo_{{ $code }}"
                                                       class="form-control-file @error('logo.' . $code) is-invalid @enderror"
                                                       accept="image/*">
                                                @error('logo.' . $code)
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
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
@stop
