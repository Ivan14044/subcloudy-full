<?php $__env->startSection('title', __('admin.service_accounts')); ?>

<?php $__env->startSection('content_header'); ?>
    <h1><?php echo e(__('admin.service_accounts')); ?></h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(__('admin.service_accounts_list')); ?></h3>
                    <a href="<?php echo e(route('admin.service-accounts.create')); ?>" class="btn btn-primary float-right">+ <?php echo e(__('admin.add')); ?></a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="service-accounts-table" class="table table-bordered table-striped table-hover nowrap" style="width:100%">
                        <thead>
                        <tr>
                            <th style="width: 40px"><?php echo e(__('admin.id')); ?></th>
                            <th><?php echo e(__('admin.service')); ?></th>
                            <th class="none"><?php echo e(__('admin.login')); ?></th>
                            <th><?php echo e(__('admin.status')); ?></th>
                            <th style="width: 100px"><?php echo e(__('admin.users')); ?></th>
                            <th style="width: 120px"><?php echo e(__('admin.subscription_expires')); ?></th>
                            <th class="none"><?php echo e(__('admin.used')); ?></th>
                            <th class="none"><?php echo e(__('admin.last_used_at')); ?></th>
                            <th class="none"><?php echo e(__('admin.created_at')); ?></th>
                            <th class="none"><?php echo e(__('admin.expiring_at')); ?></th>
                            <th style="width: 70px"><?php echo e(__('admin.action')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $serviceAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $serviceAccount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $earliestExpiry = $serviceAccount->earliest_subscription_expiry;
                                $isExpiringSoon = $earliestExpiry && $earliestExpiry->isFuture() && $earliestExpiry->diffInDays(now()) <= 7;
                                $isExpiringVerySoon = $earliestExpiry && $earliestExpiry->isFuture() && $earliestExpiry->diffInDays(now()) <= 3;
                                $isExpired = $earliestExpiry && $earliestExpiry->isPast();
                                $rowClass = $isExpiringVerySoon ? 'table-danger' : ($isExpiringSoon ? 'table-warning' : '');
                            ?>
                            <tr class="<?php echo e($rowClass); ?>">
                                <td><?php echo e($serviceAccount->id); ?></td>
                                <td>
                                    <?php if($serviceAccount->service->logo): ?>
                                        <img src="<?php echo e(asset($serviceAccount->service->logo)); ?>"
                                             alt="Logo"
                                             class="mr-1 rounded"
                                             width="36" height="36">
                                    <?php endif; ?>
                                    <?php echo e($serviceAccount->service->admin_name); ?>

                                </td>
                                <td class="none">
                                    <?php if(!empty($serviceAccount->credentials['email'])): ?>
                                        <span class="text-muted"><?php echo e($serviceAccount->credentials['email']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(!$serviceAccount->is_active): ?>
                                        <span class="badge badge-danger"><?php echo e(__('admin.inactive')); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-success"><?php echo e(__('admin.active')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $usersCount = $serviceAccount->users_count ?? 0;
                                        $maxUsers = $serviceAccount->max_users;
                                    ?>
                                    <span class="text-muted">
                                        <strong><?php echo e($usersCount); ?></strong>
                                        <?php if($maxUsers !== null): ?>
                                            <span>/ <?php echo e($maxUsers); ?></span>
                                            <?php if($usersCount >= $maxUsers): ?>
                                                <span class="badge badge-warning ml-1" title="<?php echo e(__('admin.limit_reached')); ?>">!</span>
                                            <?php elseif($usersCount >= ($maxUsers * 0.8)): ?>
                                                <span class="badge badge-info ml-1" title="<?php echo e(__('admin.near_limit')); ?>">~</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span>/ ∞</span>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($earliestExpiry): ?>
                                        <span class="text-muted"><?php echo e($earliestExpiry->format('Y-m-d')); ?></span>
                                        <?php if($isExpiringVerySoon): ?>
                                            <span class="badge badge-danger ml-1" title="<?php echo e(__('admin.expires_very_soon')); ?>">!</span>
                                        <?php elseif($isExpiringSoon): ?>
                                            <span class="badge badge-warning ml-1" title="<?php echo e(__('admin.expires_soon')); ?>">~</span>
                                        <?php elseif($isExpired): ?>
                                            <span class="badge badge-secondary ml-1" title="<?php echo e(__('admin.expired')); ?>">×</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="none"><?php echo e($serviceAccount->used); ?></td>
                                <td class="none" data-order="<?php echo e(strtotime($serviceAccount->last_used_at)); ?>">
                                    <?php echo e(\Carbon\Carbon::parse($serviceAccount->last_used_at)->format('Y-m-d H:i')); ?>

                                </td>
                                <td class="none" data-order="<?php echo e(strtotime($serviceAccount->created_at)); ?>">
                                    <?php echo e(\Carbon\Carbon::parse($serviceAccount->created_at)->format('Y-m-d H:i')); ?>

                                </td>
                                <td class="none" data-order="<?php echo e($serviceAccount->expiring_at ? strtotime($serviceAccount->expiring_at) : 0); ?>">
                                    <?php echo e($serviceAccount->expiring_at ? \Carbon\Carbon::parse($serviceAccount->expiring_at)->format('Y-m-d H:i') : null); ?>

                                </td>
                                <td>
                                    <a href="<?php echo e(route('admin.service-accounts.edit', $serviceAccount)); ?>"
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" data-toggle="modal"
                                            data-target="#deleteModal<?php echo e($serviceAccount->id); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <div class="modal fade" id="deleteModal<?php echo e($serviceAccount->id); ?>" tabindex="-1"
                                         role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel"><?php echo e(__('admin.confirm_deletion')); ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php echo e(__('admin.are_you_sure_delete_service_account')); ?>

                                                </div>
                                                <div class="modal-footer">
                                                    <form
                                                        action="<?php echo e(route('admin.service-accounts.destroy', $serviceAccount)); ?>"
                                                        method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="btn btn-danger"><?php echo e(__('admin.yes_delete')); ?>

                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal"><?php echo e(__('admin.cancel')); ?>

                                                    </button>
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
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        $(document).ready(function () {
            $('#service-accounts-table').DataTable({
                "order": [[0, "desc"]],
                "responsive": true,
                "scrollX": true,
                "scrollCollapse": true,
                "autoWidth": false,
                "columnDefs": [
                    {"orderable": false, "targets": 10},
                    {"responsivePriority": 1, "targets": 0}, // ID
                    {"responsivePriority": 2, "targets": 1}, // Service
                    {"responsivePriority": 3, "targets": 10}, // Actions
                    {"responsivePriority": 4, "targets": 2}, // Login
                    {"responsivePriority": 5, "targets": 3}, // Status
                    {"responsivePriority": 6, "targets": 4}, // Users
                    {"responsivePriority": 7, "targets": 5}, // Subscription Expires
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.19/i18n/Russian.json"
                }
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/subcloudy/backend/resources/views/admin/service-accounts/index.blade.php ENDPATH**/ ?>