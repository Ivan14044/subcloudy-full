@extends('adminlte::page')

@section('title', 'Редактировать блок экономии')

@section('content_header')
    <h1>Редактировать блок экономии #{{ $savingsBlock->id }}</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.savings-blocks.update', $savingsBlock) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="service_id">Сервис (необязательно)</label>
                            <select name="service_id" id="service_id" class="form-control @error('service_id') is-invalid @enderror">
                                <option value="">Не выбран</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" {{ old('service_id', $savingsBlock->service_id) == $service->id ? 'selected' : '' }}>{{ $service->admin_name }}</option>
                                @endforeach
                            </select>
                            @error('service_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="logo">Логотип</label>
                            @if($savingsBlock->logo)
                                <div class="mb-2">
                                    <img src="{{ $savingsBlock->logo }}" alt="" style="max-height: 100px;">
                                </div>
                            @endif
                            <input type="file" name="logo" id="logo" class="form-control-file @error('logo') is-invalid @enderror">
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="order">Порядок сортировки</label>
                            <input type="number" name="order" id="order" class="form-control @error('order') is-invalid @enderror" value="{{ old('order', $savingsBlock->order) }}">
                            @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" {{ old('is_active', $savingsBlock->is_active) ? 'checked' : '' }}>
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
                                                <label>Заголовок</label>
                                                <input type="text" name="title[{{ $code }}]" class="form-control @error("title.$code") is-invalid @enderror" value="{{ old("title.$code", $blockData[$code]['title'] ?? '') }}">
                                                @error("title.$code")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Текст</label>
                                                <textarea name="text[{{ $code }}]" class="form-control @error("text.$code") is-invalid @enderror" rows="3">{{ old("text.$code", $blockData[$code]['text'] ?? '') }}</textarea>
                                                @error("text.$code")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Наша цена</label>
                                                <input type="text" name="our_price[{{ $code }}]" class="form-control @error("our_price.$code") is-invalid @enderror" value="{{ old("our_price.$code", $blockData[$code]['our_price'] ?? '') }}">
                                                @error("our_price.$code")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Обычная цена</label>
                                                <input type="text" name="normal_price[{{ $code }}]" class="form-control @error("normal_price.$code") is-invalid @enderror" value="{{ old("normal_price.$code", $blockData[$code]['normal_price'] ?? '') }}">
                                                @error("normal_price.$code")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label>Преимущество</label>
                                                <input type="text" name="advantage[{{ $code }}]" class="form-control @error("advantage.$code") is-invalid @enderror" value="{{ old("advantage.$code", $blockData[$code]['advantage'] ?? '') }}">
                                                @error("advantage.$code")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Сохранить</button>
                        <a href="{{ route('admin.savings-blocks.index') }}" class="btn btn-secondary">Отмена</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

