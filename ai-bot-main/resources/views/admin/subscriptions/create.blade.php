@extends('adminlte::page')

@section('title', 'Добавить подписку')

@section('content_header')
    <h1>Добавить подписку</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12 col-lg-8 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Данные подписки</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.subscriptions.store') }}">
                        @csrf
                        @if(request()->return_url)
                            <input type="hidden" name="return_url" value="{{ request()->return_url }}">
                        @endif
                        <div class="form-group">
                            <label for="status">{{ __('admin.status') }}</label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                                <option value="{{ \App\Models\Subscription::STATUS_ACTIVE }}" {{ old('status') == \App\Models\Subscription::STATUS_ACTIVE ? 'selected' : '' }}>{{ __('admin.active') }}</option>
                                <option value="{{ \App\Models\Subscription::STATUS_CANCELED }}" {{ old('status') == \App\Models\Subscription::STATUS_CANCELED ? 'selected' : '' }}>{{ __('admin.canceled') }}</option>
                                <option value="{{ \App\Models\Subscription::STATUS_ENDED }}" {{ old('status') == \App\Models\Subscription::STATUS_ENDED ? 'selected' : '' }}>{{ __('admin.ended') }}</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="next_payment_at">{{ __('admin.next_payment_at') }}</label>
                            <input type="datetime-local"
                                   name="next_payment_at"
                                   id="next_payment_at"
                                   class="form-control @error('next_payment_at') is-invalid @enderror"
                                   value="{{ old('next_payment_at') }}">
                            @error('next_payment_at')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="service_id">{{ __('admin.service') }}</label>
                            <select name="service_id" id="service_id" class="form-control @error('service_id') is-invalid @enderror">
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>{{ $service->code }}</option>
                                @endforeach
                            </select>
                            @error('service_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="user_id">User</label>
                            <select name="user_id" id="user_id" class="select2 form-control @error('user_id') is-invalid @enderror">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}"
                                            {{ old('user_id', request()->user) == $user->id ? 'selected' : '' }}>
                                        #{{ $user->id }} | {{ $user->name }} - {{ $user->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary">{{ __('admin.cancel') }}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <style>
        .select2-selection {
            height: 38px!important;
            width: 100%;
        }
    </style>
    <script>
        $(document).ready(function () {
            $('#user_id').select2({
                placeholder: 'Select a user',
                allowClear: true
            });
        });
    </script>
@endsection