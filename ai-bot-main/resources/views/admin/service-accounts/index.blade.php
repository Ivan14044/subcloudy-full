@extends('adminlte::page')

@section('title', 'Service accounts')

@section('content_header')
    <h1>Service accounts</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Service account list</h3>
                    <a href="{{ route('admin.service-accounts.create') }}" class="btn btn-primary float-right">+ Add</a>
                </div>
                <div class="card-body">
                    <table id="service-accounts-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 40px">ID</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Used</th>
                            <th>Last used at</th>
                            <th>Created at</th>
                            <th>Expiring at</th>
                            <th style="width: 70px">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($serviceAccounts as $serviceAccount)
                            <tr>
                                <td>{{ $serviceAccount->id }}</td>
                                <td>
                                    @if($serviceAccount->service->logo)
                                        <img src="{{ asset($serviceAccount->service->logo) }}"
                                             alt="Logo"
                                             class="mr-1 rounded"
                                             width="36" height="36">
                                    @endif
                                    {{ $serviceAccount->service->admin_name }}
                                </td>
                                <td>
                                    @if(!$serviceAccount->is_active)
                                        <span class="badge badge-danger">Inactive</span>
                                    @else
                                        <span class="badge badge-success">Active</span>
                                    @endif
                                </td>
                                <td>{{ $serviceAccount->used }}</td>
                                <td data-order="{{ strtotime($serviceAccount->last_used_at) }}">
                                    {{ \Carbon\Carbon::parse($serviceAccount->last_used_at)->format('Y-m-d H:i') }}
                                </td>
                                <td data-order="{{ strtotime($serviceAccount->created_at) }}">
                                    {{ \Carbon\Carbon::parse($serviceAccount->created_at)->format('Y-m-d H:i') }}
                                </td>
                                <td data-order="{{ $serviceAccount->expiring_at ? strtotime($serviceAccount->expiring_at) : 0 }}">
                                    {{ $serviceAccount->expiring_at ? \Carbon\Carbon::parse($serviceAccount->expiring_at)->format('Y-m-d H:i') : null }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.service-accounts.edit', $serviceAccount) }}"
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" data-toggle="modal"
                                            data-target="#deleteModal{{ $serviceAccount->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <div class="modal fade" id="deleteModal{{ $serviceAccount->id }}" tabindex="-1"
                                         role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete this account?
                                                </div>
                                                <div class="modal-footer">
                                                    <form
                                                        action="{{ route('admin.service-accounts.destroy', $serviceAccount) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Yes, Delete
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cancel
                                                    </button>
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
            $('#service-accounts-table').DataTable({
                "order": [[0, "desc"]],
                "columnDefs": [
                    {"orderable": false, "targets": 6}
                ]
            });
        });
    </script>
@endsection
