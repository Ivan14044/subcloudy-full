@extends('adminlte::page')

@section('title', __('admin.add_content'))

@section('content_header')
    <h1>{{ __('admin.add_content') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.content_data') }}</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.contents.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="name">{{ __('admin.name') }}</label>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                            @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        @csrf
                        <div class="form-group">
                            <label for="code">{{ __('admin.code') }}</label>
                            <input type="text" name="code" id="code"
                                   class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}">
                            @error('code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('admin.create') }}</button>
                        <a href="{{ route('admin.contents.index') }}" class="btn btn-secondary">{{ __('admin.cancel') }}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
