@extends('adminlte::page')

@section('title', 'Редактировать отзыв')

@section('content_header')
    <h1>Редактировать отзыв #{{ $review->id }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.reviews.update', $review) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="rating">Рейтинг (1-5) *</label>
                            <input type="number" name="rating" id="rating" class="form-control @error('rating') is-invalid @enderror" value="{{ old('rating', $review->rating) }}" min="1" max="5" required>
                            @error('rating')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="order">Порядок отображения</label>
                            <input type="number" name="order" id="order" class="form-control @error('order') is-invalid @enderror" value="{{ old('order', $review->order) }}">
                            @error('order')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $review->is_active) ? 'checked' : '' }}>
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
                                                       value="{{ old('name.' . $code, $reviewData[$code]['name'] ?? null) }}" required>
                                                @error('name.' . $code)
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="text_{{ $code }}">Текст отзыва *</label>
                                                <textarea name="text[{{ $code }}]" id="text_{{ $code }}"
                                                          class="form-control @error('text.' . $code) is-invalid @enderror"
                                                          rows="5" required>{{ old('text.' . $code, $reviewData[$code]['text'] ?? null) }}</textarea>
                                                @error('text.' . $code)
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            @php($currentPhoto = $reviewData[$code]['photo'] ?? null)
                                            @if($currentPhoto)
                                                <div class="form-group">
                                                    <label>Текущее фото</label><br>
                                                    <input type="hidden" name="photo_text[{{ $code }}]" value="{{ $currentPhoto }}">
                                                    <img src="{{ url($currentPhoto) }}" alt="Photo" style="max-width: 100px; max-height: 100px;">
                                                    <a href="#" onclick="removePhoto('{{ $code }}', event)" class="d-block mt-1">Удалить</a>
                                                </div>
                                            @endif
                                            <div class="form-group">
                                                <label for="photo_{{ $code }}">Новое фото автора</label>
                                                <input type="file" name="photo[{{ $code }}]" id="photo_{{ $code }}"
                                                       class="form-control-file @error('photo.' . $code) is-invalid @enderror"
                                                       accept="image/*">
                                                @error('photo.' . $code)
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            @php($currentLogo = $reviewData[$code]['logo'] ?? null)
                                            @if($currentLogo)
                                                <div class="form-group">
                                                    <label>Текущий логотип</label><br>
                                                    <input type="hidden" name="logo_text[{{ $code }}]" value="{{ $currentLogo }}">
                                                    <img src="{{ url($currentLogo) }}" alt="Logo" style="max-width: 100px; max-height: 100px;">
                                                    <a href="#" onclick="removeLogo('{{ $code }}', event)" class="d-block mt-1">Удалить</a>
                                                </div>
                                            @endif
                                            <div class="form-group">
                                                <label for="logo_{{ $code }}">Новый логотип компании</label>
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

                        <button type="submit" class="btn btn-primary">Обновить</button>
                        <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">Отмена</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    function removePhoto(locale, event) {
        event.preventDefault();
        const photoInput = document.getElementById('photo_' + locale);
        const photoText = document.querySelector('input[name="photo_text[' + locale + ']"]');
        if (photoText) photoText.remove();
        if (photoInput) photoInput.value = '';
        event.target.closest('.form-group').querySelector('img').style.display = 'none';
        event.target.closest('.form-group').querySelector('a').style.display = 'none';
    }

    function removeLogo(locale, event) {
        event.preventDefault();
        const logoInput = document.getElementById('logo_' + locale);
        const logoText = document.querySelector('input[name="logo_text[' + locale + ']"]');
        if (logoText) logoText.remove();
        if (logoInput) logoInput.value = '';
        event.target.closest('.form-group').querySelector('img').style.display = 'none';
        event.target.closest('.form-group').querySelector('a').style.display = 'none';
    }
</script>
@stop
