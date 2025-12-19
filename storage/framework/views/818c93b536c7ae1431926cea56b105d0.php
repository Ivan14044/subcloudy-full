<?php $__env->startSection('title', __('admin.subscriptions') . (!empty($user) ? ' ' . __('admin.subscriptions_for') . ' ' . $user->email : '')); ?>

<?php $__env->startSection('content_header'); ?>
    <h1><?php echo e(__('admin.subscriptions')); ?> <?php echo e(!empty($user) ? __('admin.subscriptions_for') . ' ' . $user->email : ''); ?></h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(__('admin.subscriptions_list')); ?></h3>
                    <a href="<?php echo e(route('admin.subscriptions.create')); ?>" class="btn btn-primary float-right">+ <?php echo e(__('admin.add')); ?></a>
                    <a href="<?php echo e(route('admin.subscriptions.extend')); ?>" class="btn btn-success float-right mr-2"><?php echo e(__('admin.mass_extend')); ?></a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="subscriptions-table" class="table table-bordered table-striped table-sm">
                        <thead>
                        <tr>
                            <th style="width: 30px"><?php echo e(__('admin.id')); ?></th>
                            <?php if(empty($user)): ?>
                                <th><?php echo e(__('admin.user')); ?></th>
                            <?php endif; ?>
                            <th><?php echo e(__('admin.service')); ?></th>
                            <th><?php echo e(__('admin.status')); ?></th>
                            <th><?php echo e(__('admin.amount')); ?></th>
                            <th><?php echo e(__('admin.payment_info')); ?></th>
                            <th><?php echo e(__('admin.created_at')); ?></th>
                            <th><?php echo e(__('admin.action')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $subscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subscription): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($subscription->id); ?></td>
                                <?php if(empty($user)): ?>
                                    <td><?php echo e($subscription->user->email); ?></td>
                                <?php endif; ?>
                                <td>
                                    <div class="d-flex" style="gap: 5px">
                                        <img src="<?php echo e(url($subscription->service->logo)); ?>"
                                             title="<?php echo e($subscription->service->code); ?>"
                                            class="img-fluid img-bordered" style="width: 35px;">
                                    </div>
                                </td>
                                <td>
                                    <?php
                                        $status = $subscription->status;
                                    ?>

                                    <?php if($status === \App\Models\Subscription::STATUS_ACTIVE): ?>
                                        <span class="badge badge-success"><?php echo e(__('admin.active')); ?></span>
                                    <?php elseif($status === \App\Models\Subscription::STATUS_CANCELED): ?>
                                        <span class="badge badge-danger"><?php echo e(__('admin.canceled')); ?></span>
                                    <?php elseif($status === \App\Models\Subscription::STATUS_ENDED): ?>
                                        <span class="badge badge-secondary"><?php echo e(__('admin.ended')); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-light"><?php echo e(ucfirst($status)); ?></span>
                                    <?php endif; ?>
                                    <?php if($subscription->is_trial): ?>
                                        <br>
                                        <span class="badge badge-primary"><?php echo e(__('admin.trial')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <?php
                                    $last = $subscription->transactions->sortByDesc('created_at')->first();
                                ?>
                                <td>
                                    <?php echo e($last?->amount ?? '-'); ?> <?php echo e(strtoupper($last?->currency ?? '')); ?>

                                    <br>
                                    <small><?php echo e($subscription->payment_method_label); ?></small>
                                </td>
                                <td data-order="<?php echo e(strtotime($subscription->next_payment_at)); ?>">
                                    <i class="fas fa-calendar-plus text-secondary mr-1" title="<?php echo e(__('admin.next_payment_at')); ?>"></i> <?php echo e(\Carbon\Carbon::parse($subscription->next_payment_at)->format('Y-m-d H:i')); ?> <br>
                                    <i class="fas fa-receipt text-secondary mr-1" title="<?php echo e(__('admin.last_payment_at')); ?>"></i> <?php echo e($last?->created_at?->format('Y-m-d H:i') ?? '-'); ?>

                                </td>
                                <td data-order="<?php echo e(strtotime($subscription->created_at)); ?>">
                                    <?php echo e(\Carbon\Carbon::parse($subscription->created_at)->format('Y-m-d H:i')); ?>

                                </td>
                                <td class="d-flex flex-wrap align-items-center" style="gap: 5px; max-width: 110px; overflow: hidden;">
                                <a href="<?php echo e(route('admin.subscriptions.edit', $subscription) . (!empty($user) ? '?back_url=' . url()->current() : '')); ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <a href="<?php echo e(route('admin.subscriptions.transactions', $subscription) . (!empty($user) ? '?back_url=' . url()->current() : '')); ?>"
                                       class="btn btn-sm btn-<?php echo e($subscription->transactions()->count() ? 'success' : 'secondary'); ?>">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </a>

                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#nextPaymentModal<?php echo e($subscription->id); ?>">
                                        <i class="far fa-clock"></i>
                                    </button>

                                    <?php if($subscription->status == \App\Models\Subscription::STATUS_ACTIVE): ?>
                                        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#toggleStatusModal<?php echo e($subscription->id); ?>" title="<?php echo e(__('admin.cancel_subscription')); ?>">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    <?php elseif($subscription->status == \App\Models\Subscription::STATUS_CANCELED): ?>
                                        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#toggleStatusModal<?php echo e($subscription->id); ?>" title="<?php echo e(__('admin.activate_subscription')); ?>">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    <?php endif; ?>

                                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal<?php echo e($subscription->id); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <div class="modal fade" id="toggleStatusModal<?php echo e($subscription->id); ?>" tabindex="-1" role="dialog" aria-labelledby="toggleStatusModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <form action="<?php echo e(route('admin.subscriptions.toggle-status', $subscription)); ?>" method="POST" class="modal-content">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PUT'); ?>

                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="toggleStatusModalLabel">
                                                        <?php echo e($subscription->status === 'active' ? __('admin.cancel_subscription') : __('admin.activate_subscription')); ?>

                                                    </h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>

                                                <div class="modal-body">
                                                    <?php echo e($subscription->status === 'active' ? __('admin.are_you_sure_cancel_subscription') : __('admin.are_you_sure_activate_subscription')); ?>

                                                </div>

                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-<?php echo e($subscription->status === 'active' ? 'danger' : 'success'); ?>">
                                                        <?php echo e($subscription->status === 'active' ? __('admin.cancel_subscription') : __('admin.activate_subscription')); ?>

                                                    </button>
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(__('admin.cancel')); ?></button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="nextPaymentModal<?php echo e($subscription->id); ?>" tabindex="-1" role="dialog" aria-labelledby="nextPaymentModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <form action="<?php echo e(route('admin.subscriptions.update-next-payment', $subscription)); ?>" method="POST" class="modal-content">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PUT'); ?>

                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="nextPaymentModalLabel"><?php echo e(__('admin.set_next_payment_date')); ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>

                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="next_payment_at_<?php echo e($subscription->id); ?>"><?php echo e(__('admin.next_payment_at')); ?></label>
                                                        <input type="datetime-local" name="next_payment_at" id="next_payment_at_<?php echo e($subscription->id); ?>"
                                                               class="form-control"
                                                               value="<?php echo e(\Carbon\Carbon::parse($subscription->next_payment_at)->format('Y-m-d\TH:i')); ?>">
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-primary"><?php echo e(__('admin.save')); ?></button>
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo e(__('admin.cancel')); ?></button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="deleteModal<?php echo e($subscription->id); ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel"><?php echo e(__('admin.confirm_deletion')); ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php echo e(__('admin.are_you_sure_delete_subscription')); ?>

                                                </div>
                                                <div class="modal-footer">
                                                    <form action="<?php echo e(route('admin.subscriptions.destroy', $subscription)); ?>" method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="btn btn-danger"><?php echo e(__('admin.yes_delete')); ?></button>

                                                        <?php if(!empty($user)): ?>
                                                            <input type="hidden" name="return_url" value="<?php echo e(url()->current()); ?>">
                                                        <?php endif; ?>
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
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        $(document).ready(function () {
            $('#subscriptions-table').DataTable({
                "order": [[0, "desc"]],
                "columnDefs": [
                    { "orderable": false, "targets": <?php echo e(empty($user) ? 6 : 7); ?> }
                ]
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/subcloudy/backend/resources/views/admin/subscriptions/index.blade.php ENDPATH**/ ?>