@extends('adminlte::page')

@section('title', 'Users')

@section('content_header')
    <h1>Users</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('download_bulk_file'))
                <script>
                    window.addEventListener('DOMContentLoaded', function () {
                        window.location.href = "{{ route('admin.users.bulk-download') }}";
                    });
                </script>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Users list</h3>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary float-right">+ Add</a>
                    <a href="{{ route('admin.users.bulk-insert') }}" class="btn btn-warning mr-2 float-right">Bulk Add Users</a>
                </div>
                <div class="card-body">
                    <table id="users-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 40px">ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Created at</th>
                            <th style="width: 150px">Action</th>
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
                                        <span class="badge badge-danger">Blocked</span>
                                    @elseif($user->is_pending)
                                        <span class="badge badge-warning">Pending</span>
                                    @else
                                        <span class="badge badge-success">Active</span>
                                    @endif
                                </td>
                                <td data-order="{{ strtotime($user->created_at) }}">
                                    {{ \Carbon\Carbon::parse($user->created_at)->format('Y-m-d H:i') }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <a href="{{ route('admin.users.subscriptions', $user) }}"
                                       class="btn btn-sm btn-{{ $user->subscriptions()->count() ? 'success' : 'secondary' }}">
                                        <i class="fas fa-credit-card"></i>
                                    </a>

                                    <button class="btn btn-sm btn-dark" data-toggle="modal" data-target="#blockModal{{ $user->id }}">
                                        <i class="fas fa-{{ $user->is_blocked ? 'lock-open' : 'lock' }}"></i>
                                    </button>

                                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal{{ $user->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete this user?
                                                </div>
                                                <div class="modal-footer">
                                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                                    </form>
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="blockModal{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="blockModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="blockModalLabel">{{ $user->is_blocked ? 'Unblock' : 'Block' }} User</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to {{ $user->is_blocked ? 'unblock' : 'block' }} this user?
                                                </div>
                                                <div class="modal-footer">
                                                    <form action="{{ route('admin.users.block', $user) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-warning">{{ $user->is_blocked ? 'Unblock' : 'Block' }}</button>
                                                    </form>
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
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
            $('#users-table').DataTable({
                "order": [[0, "desc"]],
                "columnDefs": [
                    { "orderable": false, "targets": 5 }
                ]
            });
        });
    </script>
@endsection
