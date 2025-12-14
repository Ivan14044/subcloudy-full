@extends('adminlte::page')

@section('title', __('admin.edit_admin') . ' #' . $admin->id)

@section('content_header')
    <h1>{{ __('admin.edit_admin') }} #{{ $admin->id }}</h1>
@stop

@section('content')
    <div class="row">
        @if(session('success'))
            <div class="col-12">
                <div class="alert alert-success">{{ session('success') }}</div>
            </div>
        @endif

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.admin_data') }}</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.admins.update', $admin) }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="name">{{ __('admin.name') }}</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $admin->name) }}">
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">{{ __('admin.email') }}</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $admin->email) }}">
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="is_blocked">{{ __('admin.status') }}</label>
                            <select name="is_blocked" id="is_blocked" class="form-control @error('is_blocked') is-invalid @enderror">
                                <option value="0" {{ old('is_blocked', $admin->is_blocked) == 0 ? 'selected' : '' }}>{{ __('admin.active') }}</option>
                                <option value="1" {{ old('is_blocked', $admin->is_blocked) == 1 ? 'selected' : '' }}>{{ __('admin.blocked') }}</option>
                            </select>
                            @error('is_blocked')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">{{ __('admin.new_password') }}</label>
                            <small>{{ __('admin.leave_empty_to_keep_current') }}</small>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary mr-2">{{ __('admin.save') }}</button>
                        <button type="submit" name="save" class="btn btn-primary mr-2">{{ __('admin.save') }} & {{ __('admin.continue') }}</button>
                        <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary">{{ __('admin.cancel') }}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
