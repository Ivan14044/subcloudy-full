@extends('adminlte::page')

@section('title', __('admin.users'))

@section('content_header')
    <h1>{{ __('admin.users') }}</h1>
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
                    <h3 class="card-title">{{ __('admin.users') }}</h3>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary float-right">+ {{ __('admin.add') }}</a>
                    <a href="{{ route('admin.users.bulk-insert') }}" class="btn btn-warning mr-2 float-right">{{ __('admin.bulk_add_users') }}</a>
                    <div class="btn-group mr-2 float-right" id="bulkActionsGroup" style="display: none;">
                        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ __('admin.bulk_actions') }}
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#bulkUpdateStatusModal">{{ __('admin.bulk_update_status') }}</a>
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#bulkAddFreeDaysModal">{{ __('admin.bulk_add_free_days') }}</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="users-table" class="table table-bordered table-striped table-sm">
                            <thead>
                            <tr>
                                <th style="width: 40px">
                                    <input type="checkbox" id="select-all-users">
                                </th>
                                <th style="width: 40px">{{ __('admin.id') }}</th>
                                <th>{{ __('admin.name') }}</th>
                                <th>{{ __('admin.email') }}</th>
                                <th>{{ __('admin.status') }}</th>
                                <th>{{ __('admin.created_at') }}</th>
                                <th style="width: 150px">{{ __('admin.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="user-checkbox" name="user_ids[]" value="{{ $user->id }}">
                                    </td>
                                    <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->is_blocked)
                                        <span class="badge badge-danger">{{ __('admin.blocked') }}</span>
                                    @elseif($user->is_pending)
                                        <span class="badge badge-warning">{{ __('admin.pending') }}</span>
                                    @else
                                        <span class="badge badge-success">{{ __('admin.active') }}</span>
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
                                                    <h5 class="modal-title" id="deleteModalLabel">{{ __('admin.confirm_deletion') }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    {{ __('admin.are_you_sure_delete') }}
                                                </div>
                                                <div class="modal-footer">
                                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
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
                                                    <h5 class="modal-title" id="blockModalLabel">{{ $user->is_blocked ? __('admin.unblock') : __('admin.block') }} {{ __('admin.users') }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    {{ $user->is_blocked ? __('admin.are_you_sure_unblock') : __('admin.are_you_sure_block') }}
                                                </div>
                                                <div class="modal-footer">
                                                    <form action="{{ route('admin.users.block', $user) }}" method="POST" class="d-inline">
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

    <!-- Bulk Update Status Modal -->
    <div class="modal fade" id="bulkUpdateStatusModal" tabindex="-1" role="dialog" aria-labelledby="bulkUpdateStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('admin.users.bulk-update-status') }}" method="POST" id="bulkUpdateStatusForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkUpdateStatusModalLabel">{{ __('admin.bulk_update_status') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('admin.select_users') }}: <span id="selectedUsersCount">0</span></p>
                        <div class="form-group">
                            <label for="bulk_status">{{ __('admin.new_status') }}</label>
                            <select name="status" id="bulk_status" class="form-control" required>
                                <option value="active">{{ __('admin.active') }}</option>
                                <option value="blocked">{{ __('admin.blocked') }}</option>
                                <option value="pending">{{ __('admin.pending') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('admin.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Add Free Days Modal -->
    <div class="modal fade" id="bulkAddFreeDaysModal" tabindex="-1" role="dialog" aria-labelledby="bulkAddFreeDaysModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('admin.users.bulk-add-free-days') }}" method="POST" id="bulkAddFreeDaysForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkAddFreeDaysModalLabel">{{ __('admin.bulk_add_free_days') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('admin.select_users') }}: <span id="selectedUsersCount2">0</span></p>
                        <div class="form-group">
                            <label for="free_days">{{ __('admin.free_days_count') }}</label>
                            <input type="number" name="days" id="free_days" class="form-control" min="1" max="365" required>
                        </div>
                        <div class="form-group">
                            <label for="service_id">{{ __('admin.select_service') }}</label>
                            <select name="service_id" id="service_id" class="form-control">
                                <option value="">{{ __('admin.all_services') }}</option>
                                @foreach(\App\Models\Service::all() as $service)
                                    <option value="{{ $service->id }}">{{ $service->admin_name ?? $service->code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('admin.cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('#users-table').DataTable({
                "order": [[1, "desc"]],
                "columnDefs": [
                    { "orderable": false, "targets": [0, 6] }
                ]
            });

            // Select all checkbox
            $('#select-all-users').on('change', function () {
                $('.user-checkbox').prop('checked', this.checked);
                updateBulkActionsVisibility();
            });

            // Individual checkbox change
            $(document).on('change', '.user-checkbox', function () {
                var total = $('.user-checkbox').length;
                var checked = $('.user-checkbox:checked').length;
                $('#select-all-users').prop('checked', total === checked);
                updateBulkActionsVisibility();
            });

            function updateBulkActionsVisibility() {
                var checked = $('.user-checkbox:checked').length;
                if (checked > 0) {
                    $('#bulkActionsGroup').show();
                    $('#selectedUsersCount').text(checked);
                    $('#selectedUsersCount2').text(checked);
                } else {
                    $('#bulkActionsGroup').hide();
                }
            }

            // Bulk update status form
            $('#bulkUpdateStatusForm').on('submit', function (e) {
                var checked = $('.user-checkbox:checked');
                if (checked.length === 0) {
                    e.preventDefault();
                    alert('{{ __('admin.select_users') }}');
                    return false;
                }
                checked.each(function () {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'user_ids[]',
                        value: $(this).val()
                    }).appendTo('#bulkUpdateStatusForm');
                });
            });

            // Bulk add free days form
            $('#bulkAddFreeDaysForm').on('submit', function (e) {
                var checked = $('.user-checkbox:checked');
                if (checked.length === 0) {
                    e.preventDefault();
                    alert('{{ __('admin.select_users') }}');
                    return false;
                }
                checked.each(function () {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'user_ids[]',
                        value: $(this).val()
                    }).appendTo('#bulkAddFreeDaysForm');
                });
            });
        });
    </script>
@endsection
