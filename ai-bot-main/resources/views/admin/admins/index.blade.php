@extends('adminlte::page')

@section('title', __('admin.admins'))

@section('content_header')
    <h1>{{ __('admin.admins') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.admins_list') }}</h3>
                    <a href="{{ route('admin.admins.create') }}" class="btn btn-primary float-right">+ {{ __('admin.add') }}</a>
                </div>
                <div class="card-body">
                    <table id="admins-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 40px">{{ __('admin.id') }}</th>
                            <th>{{ __('admin.name') }}</th>
                            <th>{{ __('admin.email') }}</th>
                            <th>{{ __('admin.status') }}</th>
                            <th>{{ __('admin.created_at') }}</th>
                            <th style="width: 110px">{{ __('admin.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->is_blocked)
                                        <span class="badge badge-danger">{{ __('admin.blocked') }}</span>
                                    @else
                                        <span class="badge badge-success">{{ __('admin.active') }}</span>
                                    @endif
                                </td>
                                <td data-order="{{ strtotime($user->created_at) }}">
                                    {{ \Carbon\Carbon::parse($user->created_at)->format('Y-m-d H:i') }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.admins.edit', $user) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <button class="btn btn-sm btn-secondary" data-toggle="modal" data-target="#blockModal{{ $user->id }}">
                                        <i class="fas fa-{{ $user->is_blocked ? 'lock-open' : 'lock' }}"></i>
                                    </button>

                                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal{{ $user->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel">{{ __('admin.confirm_deletion') }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    {{ __('admin.are_you_sure_delete_admin') }}
                                                </div>
                                                <div class="modal-footer">
                                                    <form action="{{ route('admin.admins.destroy', $user) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">{{ __('admin.yes_delete') }}</button>
                                                    </form>
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('admin.cancel') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="blockModal{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="blockModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="blockModalLabel">{{ $user->is_blocked ? __('admin.unblock') : __('admin.block') }} {{ __('admin.admins') }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    {{ $user->is_blocked ? __('admin.are_you_sure_unblock') : __('admin.are_you_sure_block') }}
                                                </div>
                                                <div class="modal-footer">
                                                    <form action="{{ route('admin.admins.block', $user) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-warning">{{ $user->is_blocked ? __('admin.unblock') : __('admin.block') }}</button>
                                                    </form>
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('admin.cancel') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
            $('#admins-table').DataTable({
                "order": [[0, "desc"]],
                "columnDefs": [
                    { "orderable": false, "targets": 5 }
                ]
            });
        });
    </script>
@endsection
