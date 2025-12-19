<?php $__env->startSection('title', __('admin.users')); ?>

<?php $__env->startSection('content_header'); ?>
    <h1><?php echo e(__('admin.users')); ?></h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <?php if(session('download_bulk_file')): ?>
                <script>
                    window.addEventListener('DOMContentLoaded', function () {
                        window.location.href = "<?php echo e(route('admin.users.bulk-download')); ?>";
                    });
                </script>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(__('admin.users')); ?></h3>
                    <a href="<?php echo e(route('admin.users.create')); ?>" class="btn btn-primary float-right">+ <?php echo e(__('admin.add')); ?></a>
                    <a href="<?php echo e(route('admin.users.bulk-insert')); ?>" class="btn btn-warning mr-2 float-right"><?php echo e(__('admin.bulk_add_users')); ?></a>
                    <div class="btn-group mr-2 float-right" id="bulkActionsGroup" style="display: none;">
                        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php echo e(__('admin.bulk_actions')); ?>

                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#bulkUpdateStatusModal"><?php echo e(__('admin.bulk_update_status')); ?></a>
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#bulkAddFreeDaysModal"><?php echo e(__('admin.bulk_add_free_days')); ?></a>
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
                                <th style="width: 40px"><?php echo e(__('admin.id')); ?></th>
                                <th><?php echo e(__('admin.name')); ?></th>
                                <th><?php echo e(__('admin.email')); ?></th>
                                <th><?php echo e(__('admin.status')); ?></th>
                                <th><?php echo e(__('admin.created_at')); ?></th>
                                <th style="width: 150px"><?php echo e(__('admin.action')); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="user-checkbox" name="user_ids[]" value="<?php echo e($user->id); ?>">
                                    </td>
                                    <td><?php echo e($user->id); ?></td>
                                <td><?php echo e($user->name); ?></td>
                                <td><?php echo e($user->email); ?></td>
                                <td>
                                    <?php if($user->is_blocked): ?>
                                        <span class="badge badge-danger"><?php echo e(__('admin.blocked')); ?></span>
                                    <?php elseif($user->is_pending): ?>
                                        <span class="badge badge-warning"><?php echo e(__('admin.pending')); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-success"><?php echo e(__('admin.active')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td data-order="<?php echo e(strtotime($user->created_at)); ?>">
                                    <?php echo e(\Carbon\Carbon::parse($user->created_at)->format('Y-m-d H:i')); ?>

                                </td>
                                <td>
                                    <a href="<?php echo e(route('admin.users.edit', $user)); ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <a href="<?php echo e(route('admin.users.subscriptions', $user)); ?>"
                                       class="btn btn-sm btn-<?php echo e($user->subscriptions()->count() ? 'success' : 'secondary'); ?>">
                                        <i class="fas fa-credit-card"></i>
                                    </a>

                                    <button class="btn btn-sm btn-dark" data-toggle="modal" data-target="#blockModal<?php echo e($user->id); ?>">
                                        <i class="fas fa-<?php echo e($user->is_blocked ? 'lock-open' : 'lock'); ?>"></i>
                                    </button>

                                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal<?php echo e($user->id); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <div class="modal fade" id="deleteModal<?php echo e($user->id); ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel"><?php echo e(__('admin.confirm_deletion')); ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php echo e(__('admin.are_you_sure_delete')); ?>

                                                </div>
                                                <div class="modal-footer">
                                                    <form action="<?php echo e(route('admin.users.destroy', $user)); ?>" method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="btn btn-danger"><?php echo e(__('admin.yes_delete')); ?></button>
                                                    </form>
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(__('admin.cancel')); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="blockModal<?php echo e($user->id); ?>" tabindex="-1" role="dialog" aria-labelledby="blockModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="blockModalLabel"><?php echo e($user->is_blocked ? __('admin.unblock') : __('admin.block')); ?> <?php echo e(__('admin.users')); ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php echo e($user->is_blocked ? __('admin.are_you_sure_unblock') : __('admin.are_you_sure_block')); ?>

                                                </div>
                                                <div class="modal-footer">
                                                    <form action="<?php echo e(route('admin.users.block', $user)); ?>" method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="btn btn-warning"><?php echo e($user->is_blocked ? __('admin.unblock') : __('admin.block')); ?></button>
                                                    </form>
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(__('admin.cancel')); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Update Status Modal -->
    <div class="modal fade" id="bulkUpdateStatusModal" tabindex="-1" role="dialog" aria-labelledby="bulkUpdateStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="<?php echo e(route('admin.users.bulk-update-status')); ?>" method="POST" id="bulkUpdateStatusForm">
                <?php echo csrf_field(); ?>
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkUpdateStatusModalLabel"><?php echo e(__('admin.bulk_update_status')); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><?php echo e(__('admin.select_users')); ?>: <span id="selectedUsersCount">0</span></p>
                        <div class="form-group">
                            <label for="bulk_status"><?php echo e(__('admin.new_status')); ?></label>
                            <select name="status" id="bulk_status" class="form-control" required>
                                <option value="active"><?php echo e(__('admin.active')); ?></option>
                                <option value="blocked"><?php echo e(__('admin.blocked')); ?></option>
                                <option value="pending"><?php echo e(__('admin.pending')); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><?php echo e(__('admin.save')); ?></button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(__('admin.cancel')); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Add Free Days Modal -->
    <div class="modal fade" id="bulkAddFreeDaysModal" tabindex="-1" role="dialog" aria-labelledby="bulkAddFreeDaysModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="<?php echo e(route('admin.users.bulk-add-free-days')); ?>" method="POST" id="bulkAddFreeDaysForm">
                <?php echo csrf_field(); ?>
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkAddFreeDaysModalLabel"><?php echo e(__('admin.bulk_add_free_days')); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><?php echo e(__('admin.select_users')); ?>: <span id="selectedUsersCount2">0</span></p>
                        <div class="form-group">
                            <label for="free_days"><?php echo e(__('admin.free_days_count')); ?></label>
                            <input type="number" name="days" id="free_days" class="form-control" min="1" max="365" required>
                        </div>
                        <div class="form-group">
                            <label for="service_id"><?php echo e(__('admin.select_service')); ?></label>
                            <select name="service_id" id="service_id" class="form-control">
                                <option value=""><?php echo e(__('admin.all_services')); ?></option>
                                <?php $__currentLoopData = \App\Models\Service::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($service->id); ?>"><?php echo e($service->admin_name ?? $service->code); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><?php echo e(__('admin.save')); ?></button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(__('admin.cancel')); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
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
                    alert('<?php echo e(__('admin.select_users')); ?>');
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
                    alert('<?php echo e(__('admin.select_users')); ?>');
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/subcloudy/backend/resources/views/admin/users/index.blade.php ENDPATH**/ ?>