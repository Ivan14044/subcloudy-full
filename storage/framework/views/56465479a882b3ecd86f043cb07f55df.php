<?php $__env->startSection('title', __('admin.contents')); ?>

<?php $__env->startSection('content_header'); ?>
    <h1><?php echo e(__('admin.contents')); ?></h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(__('admin.contents_list')); ?></h3>
                    <a href="<?php echo e(route('admin.contents.create')); ?>" class="btn btn-primary float-right">+ <?php echo e(__('admin.add')); ?></a>
                </div>
                <div class="card-body">
                    <table id="contents-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 40px"><?php echo e(__('admin.id')); ?></th>
                            <th><?php echo e(__('admin.name')); ?></th>
                            <th><?php echo e(__('admin.code')); ?></th>
                            <th style="width: 110px"><?php echo e(__('admin.action')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $contents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $content): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($content->id); ?></td>
                                <td><?php echo e($content->name); ?></td>
                                <td><?php echo e($content->code); ?></td>
                                <td>
                                    <a href="<?php echo e(route('admin.contents.edit', $content)); ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <?php if(!$content->is_system): ?>
                                        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal<?php echo e($content->id); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                        <div class="modal fade" id="deleteModal<?php echo e($content->id); ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel"><?php echo e(__('admin.confirm_deletion')); ?></h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php echo e(__('admin.are_you_sure_delete_content')); ?>

                                                    </div>
                                                    <div class="modal-footer">
                                                        <form action="<?php echo e(route('admin.contents.destroy', $content)); ?>" method="POST" class="d-inline">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                            <button type="submit" class="btn btn-danger"><?php echo e(__('admin.yes_delete')); ?></button>
                                                        </form>
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(__('admin.cancel')); ?></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
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
            $('#contents-table').DataTable({
                "order": [[0, "desc"]],
                "columnDefs": [
                    { "orderable": false, "targets": 3 }
                ]
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/subcloudy/backend/resources/views/admin/contents/index.blade.php ENDPATH**/ ?>