<?php $__env->startSection('title', __('admin.notifications')); ?>

<?php $__env->startSection('content_header'); ?>
    <h1><?php echo e(__('admin.notifications')); ?></h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title mb-0"><?php echo e(__('admin.notifications_list')); ?></h3>
                    <form action="<?php echo e(route('admin.admin_notifications.read-all')); ?>" method="POST" class="ml-auto">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-check-double mr-1"></i> Отметить все как прочитанные
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    <table id="notifications-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 40px"><?php echo e(__('admin.id')); ?></th>
                            <th><?php echo e(__('admin.type')); ?></th>
                            <th>Текст</th>
                            <th>Прочитано</th>
                            <th><?php echo e(__('admin.created_at')); ?></th>
                            <th style="width: 100px"><?php echo e(__('admin.action')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr <?php if(!$notification->read): ?> class="bg-light-primary" <?php endif; ?>>
                                <td><?php echo e($notification->id); ?></td>
                                <td><?php echo e($notification->type); ?></td>
                                <td>
                                    <div><strong><?php echo e($notification->title); ?></strong></div>
                                    <div class="text-muted"><?php echo e($notification->message); ?></div>
                                </td>
                                <td>
                                    <?php echo e($notification->read ? 'Да' : 'Нет'); ?>

                                </td>
                                <td data-order="<?php echo e(strtotime($notification->created_at)); ?>">
                                    <?php echo e(\Carbon\Carbon::parse($notification->created_at)->format('Y-m-d H:i')); ?>

                                </td>
                                <td>
                                    <?php if(!$notification->read): ?>
                                        <a href="<?php echo e(route('admin.admin_notifications.read', $notification->id)); ?>"
                                           class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>

                                    <button class="btn btn-sm btn-danger" data-toggle="modal"
                                            data-target="#deleteModal<?php echo e($notification->id); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <!-- Modal -->
                                    <div class="modal fade" id="deleteModal<?php echo e($notification->id); ?>" tabindex="-1"
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
                                                    <?php echo e(__('admin.are_you_sure_delete_notification')); ?>

                                                </div>
                                                <div class="modal-footer">
                                                    <form
                                                        action="<?php echo e(route('admin.admin_notifications.destroy', $notification->id)); ?>"
                                                        method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="btn btn-danger"><?php echo e(__('admin.yes_delete')); ?></button>
                                                    </form>
                                                    <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal"><?php echo e(__('admin.cancel')); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Modal -->
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/subcloudy/backend/resources/views/admin/admin_notifications/index.blade.php ENDPATH**/ ?>