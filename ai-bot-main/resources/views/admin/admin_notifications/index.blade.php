@extends('adminlte::page')

@section('title', __('admin.notifications'))

@section('content_header')
    <h1>{{ __('admin.notifications') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title mb-0">{{ __('admin.notifications_list') }}</h3>
                    <form action="{{ route('admin.admin_notifications.read-all') }}" method="POST" class="ml-auto">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-check-double mr-1"></i> Отметить все как прочитанные
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    <table id="notifications-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 40px">{{ __('admin.id') }}</th>
                            <th>{{ __('admin.type') }}</th>
                            <th>Текст</th>
                            <th>Прочитано</th>
                            <th>{{ __('admin.created_at') }}</th>
                            <th style="width: 100px">{{ __('admin.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($notifications as $notification)
                            <tr @if(!$notification->read) class="bg-light-primary" @endif>
                                <td>{{ $notification->id }}</td>
                                <td>{{ $notification->type }}</td>
                                <td>
                                    <div><strong>{{ $notification->title }}</strong></div>
                                    <div class="text-muted">{{ $notification->message }}</div>
                                </td>
                                <td>
                                    {{ $notification->read ? 'Да' : 'Нет' }}
                                </td>
                                <td data-order="{{ strtotime($notification->created_at) }}">
                                    {{ \Carbon\Carbon::parse($notification->created_at)->format('Y-m-d H:i') }}
                                </td>
                                <td>
                                    @if(!$notification->read)
                                        <a href="{{ route('admin.admin_notifications.read', $notification->id) }}"
                                           class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    @endif

                                    <button class="btn btn-sm btn-danger" data-toggle="modal"
                                            data-target="#deleteModal{{ $notification->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <!-- Modal -->
                                    <div class="modal fade" id="deleteModal{{ $notification->id }}" tabindex="-1"
                                         role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel">{{ __('admin.confirm_deletion') }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    {{ __('admin.are_you_sure_delete_notification') }}
                                                </div>
                                                <div class="modal-footer">
                                                    <form
                                                        action="{{ route('admin.admin_notifications.destroy', $notification->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">{{ __('admin.yes_delete') }}</button>
                                                    </form>
                                                    <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">{{ __('admin.cancel') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Modal -->
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
        $(document).ready(function() {
            $('#notifications-table').DataTable({
                'order': [[0, 'desc']],
                'columnDefs': [
                    { 'orderable': false, 'targets': [3, 5] }
                ]
            });
        });
    </script>
@endsection
