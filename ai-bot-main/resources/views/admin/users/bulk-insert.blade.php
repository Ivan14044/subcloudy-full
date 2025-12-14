@extends('adminlte::page')

@section('title', __('admin.bulk_add_users'))

@section('content_header')
    <h1>{{ __('admin.bulk_add_users') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Данные пользователей</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.bulk-insert.store') }}">
                        @csrf
                        <div class="form-group">
                            <label for="prefix">{{ __('admin.prefix') }}</label>
                            <input type="text" name="prefix" id="prefix" class="form-control @error('prefix') is-invalid @enderror" value="{{ old('prefix') }}">
                            @error('prefix')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="number">Количество аккаунтов</label>
                            <input type="number" step="1" min="1" name="number" id="number" class="form-control @error('number') is-invalid @enderror" value="{{ old('number') }}">
                            @error('number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="days">{{ __('admin.subscription_duration_days') }}</label>
                            <input type="number" step="1" min="1" name="days" id="days" class="form-control @error('days') is-invalid @enderror" value="{{ old('number') }}">
                            @error('days')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>{{ __('admin.services') }}</label>
                            @foreach($services as $service)
                                <div class="form-check d-flex align-items-center mb-2">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="services[]"
                                           value="{{ $service->id }}"
                                           id="service_{{ $service->id }}">
                                    <label class="form-check-label ms-2" for="service_{{ $service->id }}">
                                        <img src="{{ asset($service->logo) }}" alt="{{ $service->code }}"
                                             style="width:20px; height:20px; object-fit:contain; margin-right:5px;">
                                        {{ $service->code }}
                                    </label>
                                </div>
                            @endforeach
                            @error('services')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('admin.create') }}</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">{{ __('admin.cancel') }}</a>

                        @php
                            $bulkFileExists = \Illuminate\Support\Facades\Storage::disk('local')->exists('bulk_users.txt');
                        @endphp

                        @if ($bulkFileExists)
                            <a href="{{ route('admin.users.bulk-download') }}" class="btn btn-success">
                                Скачать последние учетные данные пользователей
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
