@extends('adminlte::page')

@section('title', 'Редактировать email шаблон #' . $emailTemplate->id)

@section('content_header')
    <h1>Редактировать email шаблон #{{ $emailTemplate->id }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Данные шаблона</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.email-templates.update', $emailTemplate) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="name">{{ __('admin.name') }}</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $emailTemplate->name) }}">
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="slug">{{ __('admin.code') }}</label>
                            <input type="text" id="code" readonly class="form-control" value="{{ $emailTemplate->code }}">
                        </div>

                        <div class="card">
                            <div class="card-header no-border border-0 p-0">
                                <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                                    @foreach(config('langs') as $code => $flag)
                                        @php($hasError = $errors->has('title.' . $code) || $errors->has('message.' . $code))
                                        <li class="nav-item">
                                            <a class="nav-link @if($hasError) text-danger @endif {{ $code == 'en' ? 'active' : null }}"
                                               id="tab_{{ $code }}" data-toggle="pill" href="#tab_message_{{ $code }}" role="tab">
                                                <span class="flag-icon flag-icon-{{ $flag }} mr-1"></span> {{ strtoupper($code) }}  @if($hasError)*@endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content">
                                    @foreach(config('langs') as $code => $flag)
                                        <div class="tab-pane fade show {{ $code == 'en' ? 'active' : null }}" id="tab_message_{{ $code }}" role="tabpanel">
                                            <div class="form-group">
                                                <label for="title_{{ $code }}">{{ __('admin.title') }}</label>
                                                <input type="text" name="title[{{ $code }}]" id="title_{{ $code }}"
                                                       class="form-control @error('title.' . $code) is-invalid @enderror"
                                                       value="{{ old('title.' . $code, $emailTemplateData[$code]['title'] ?? null) }}">
                                                @error('title.' . $code)
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="message_{{ $code }}">Сообщение</label>
                                                @if ($emailTemplate->code === 'purchase')
                                                    <div class="alert alert-dark">
                                                        Вы можете использовать следующие теги: <code>:service</code>, <code>:date</code>
                                                    </div>
                                                @endif
                                                <textarea style="height: 210px"
                                                          name="message[{{ $code }}]"
                                                          class="ckeditor form-control @error('message.' . $code) is-invalid @enderror"
                                                          id="message_{{ $code }}">{!! old('message.' . $code, $emailTemplateData[$code]['message'] ?? null) !!}</textarea>
                                                @error('message.' . $code)
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mr-2">{{ __('admin.save') }}</button>
                        <button type="submit" name="save" class="btn btn-primary mr-2">{{ __('admin.save') }} & {{ __('admin.continue') }}</button>
                        <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary">{{ __('admin.cancel') }}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        document.querySelectorAll('.ckeditor').forEach(function (textarea) {
            ClassicEditor
                .create(textarea)
                .then(editor => {
                    editor.editing.view.change(writer => {
                        writer.setStyle('height', '500px', editor.editing.view.document.getRoot());
                    });
                })
                .catch(error => {
                    console.error(error);
                });
        });
    </script>
@endsection

