<?php $__env->startSection('title', __('admin.proxies')); ?>

<?php $__env->startSection('content_header'); ?>
    <h1><?php echo e(__('admin.proxies')); ?></h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(__('admin.proxies_list')); ?></h3>
                    <a href="<?php echo e(route('admin.proxies.create')); ?>" class="btn btn-primary float-right">+ <?php echo e(__('admin.add')); ?></a>
                </div>
                <div class="card-body">
                    <table id="proxies-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 40px"><?php echo e(__('admin.id')); ?></th>
                            <th><?php echo e(__('admin.full_proxy')); ?></th>
                            <th><?php echo e(__('admin.status')); ?></th>
                            <th><?php echo e(__('admin.country')); ?></th>
                            <th><?php echo e(__('admin.created_at')); ?></th>
                            <th><?php echo e(__('admin.expiring_at')); ?></th>
                            <th style="width: 70px"><?php echo e(__('admin.action')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $proxies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proxy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($proxy->id); ?></td>
                                <td><?php echo e($proxy->getFullProxy()); ?></td>
                                <td>
                                    <?php if(!$proxy->is_active): ?>
                                        <span class="badge badge-danger"><?php echo e(__('admin.inactive')); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-success"><?php echo e(__('admin.active')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e(strtoupper($proxy->country)); ?></td>
                                <td data-order="<?php echo e(strtotime($proxy->created_at)); ?>">
                                    <?php echo e(\Carbon\Carbon::parse($proxy->created_at)->format('Y-m-d H:i')); ?>

                                </td>
                                <td data-order="<?php echo e($proxy->expiring_at ? strtotime($proxy->expiring_at) : 0); ?>">
                                    <?php echo e($proxy->expiring_at ? \Carbon\Carbon::parse($proxy->expiring_at)->format('Y-m-d H:i') : null); ?>

                                </td>
                                <td>
                                    <a href="<?php echo e(route('admin.proxies.edit', $proxy)); ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal<?php echo e($proxy->id); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <div class="modal fade" id="deleteModal<?php echo e($proxy->id); ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel"><?php echo e(__('admin.confirm_deletion')); ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php echo e(__('admin.are_you_sure_delete_proxy')); ?>

                                                </div>
                                                <div class="modal-footer">
                                                    <form action="<?php echo e(route('admin.proxies.destroy', $proxy)); ?>" method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="btn btn-danger"><?php echo e(__('admin.yes_delete')); ?></button>
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        $(document).ready(function () {
            $('#proxies-table').DataTable({
                "order": [[0, "desc"]],
                "columnDefs": [
                    { "orderable": false, "targets": 6 }
                ]
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/subcloudy/backend/resources/views/admin/proxies/index.blade.php ENDPATH**/ ?>