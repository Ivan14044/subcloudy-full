<?php $__env->startSection('title', __('admin.articles')); ?>

<?php $__env->startSection('content_header'); ?>
    <h1><?php echo e(__('admin.articles')); ?></h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(__('admin.articles_list')); ?></h3>
                    <a href="<?php echo e(route('admin.articles.create')); ?>" class="btn btn-primary float-right">+ <?php echo e(__('admin.add')); ?></a>
                    <a href="<?php echo e(route('admin.categories.index')); ?>" class="btn btn-secondary float-right mr-2"><?php echo e(__('admin.categories')); ?></a>
                </div>
                <div class="card-body">
                    <table id="articles-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 40px"><?php echo e(__('admin.id')); ?></th>
                            <th><?php echo e(__('admin.name')); ?></th>
                            <th>Категория</th>
                            <th>Изображение</th>
                            <th><?php echo e(__('admin.status')); ?></th>
                            <th><?php echo e(__('admin.created_at')); ?></th>
                            <th style="width: 90px"><?php echo e(__('admin.action')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($article->id); ?></td>
                                <td>
                                    <?php echo e($article->admin_name); ?>

                                </td>
                                <td>
                                    <?php $__currentLoopData = $article->categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="badge badge-info"><?php echo e($category->admin_name ?? '-'); ?></div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </td>
                                <td>
                                    <?php if($article->img): ?>
                                        <?php ($imgSrc = \Illuminate\Support\Str::startsWith($article->img, ['http://', 'https://', '/storage/']) ? $article->img : asset('img/articles/' . $article->img)); ?>
                                        <img src="<?php echo e($imgSrc); ?>"
                                             alt=""
                                             class="mr-1 rounded"
                                             width="36" height="36">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($article->status === 'published'): ?>
                                        <span class="badge badge-success">Опубликовано</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning text-dark">Черновик</span>
                                    <?php endif; ?>
                                </td>
                                <td data-order="<?php echo e(strtotime($article->created_at)); ?>">
                                    <?php echo e(\Carbon\Carbon::parse($article->created_at)->format('Y-m-d H:i')); ?>

                                </td>
                                <td>
                                    <a href="<?php echo e(route('admin.articles.edit', $article)); ?>"
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <button class="btn btn-sm btn-danger" data-toggle="modal"
                                            data-target="#deleteModal<?php echo e($article->id); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <div class="modal fade" id="deleteModal<?php echo e($article->id); ?>" tabindex="-1"
                                         role="dialog" aria-labelledby="deleteModalLabel<?php echo e($article->id); ?>"
                                         aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo e($article->id); ?>">
                                                        <?php echo e(__('admin.confirm_deletion')); ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php echo e(__('admin.are_you_sure_delete_article')); ?>

                                                </div>
                                                <div class="modal-footer">
                                                    <form
                                                        action="<?php echo e(route('admin.articles.destroy', $article->id)); ?>"
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
<?php $__env->stopSection(); ?>


<?php $__env->startSection('js'); ?>
    <script>
        $(document).ready(function() {
            $('#articles-table').DataTable({
                'order': [[0, 'desc']],
                'columnDefs': [
                    { 'orderable': false, 'targets': 6 }
                ]
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/subcloudy/backend/resources/views/admin/articles/index.blade.php ENDPATH**/ ?>