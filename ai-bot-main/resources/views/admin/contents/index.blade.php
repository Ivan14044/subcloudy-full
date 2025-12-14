@extends('adminlte::page')

@section('title', __('admin.contents'))

@section('content_header')
    <h1>{{ __('admin.contents') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.contents_list') }}</h3>
                    <a href="{{ route('admin.contents.create') }}" class="btn btn-primary float-right">+ {{ __('admin.add') }}</a>
                </div>
                <div class="card-body">
                    <table id="contents-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 40px">{{ __('admin.id') }}</th>
                            <th>{{ __('admin.name') }}</th>
                            <th>{{ __('admin.code') }}</th>
                            <th style="width: 110px">{{ __('admin.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($contents as $content)
                            <tr>
                                <td>{{ $content->id }}</td>
                                <td>{{ $content->name }}</td>
                                <td>{{ $content->code }}</td>
                                <td>
                                    <a href="{{ route('admin.contents.edit', $content) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @if(!$content->is_system)
                                        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal{{ $content->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                        <div class="modal fade" id="deleteModal{{ $content->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel">{{ __('admin.confirm_deletion') }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        {{ __('admin.are_you_sure_delete_content') }}
                                                    </div>
                                                    <div class="modal-footer">
                                                        <form action="{{ route('admin.contents.destroy', $content) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">{{ __('admin.yes_delete') }}</button>
                                                        </form>
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('admin.cancel') }}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('#contents-table').DataTable({
                "order": [[0, "desc"]],
                "columnDefs": [
                    { "orderable": false, "targets": 3 }
                ]
            });
        });
    </script>
@endsection
