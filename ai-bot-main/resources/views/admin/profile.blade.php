@extends('adminlte::page')

@section('title', __('admin.profile'))

@section('content_header')
    <h1>{{ __('admin.profile') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
        </div>
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">Учетные данные</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.profile.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>{{ __('admin.email') }}</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>{{ __('admin.new_password') }}</label>
                            <small>{{ __('admin.leave_empty_to_keep_current') }}</small>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>{{ __('admin.confirm_password') }}</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
