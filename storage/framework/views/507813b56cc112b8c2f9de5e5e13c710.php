<?php $__env->startSection('title', __('admin.edit_user') . ' #' . $user->id); ?>

<?php $__env->startSection('content_header'); ?>
    <h1><?php echo e(__('admin.edit_user')); ?> #<?php echo e($user->id); ?></h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <?php if(session('success')): ?>
            <div class="col-12">
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            </div>
        <?php endif; ?>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(__('admin.user_data')); ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('admin.users.update', $user)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <div class="form-group">
                            <label for="name"><?php echo e(__('admin.name')); ?></label>
                            <input type="text" name="name" id="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('name', $user->name)); ?>">
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="email"><?php echo e(__('admin.email')); ?></label>
                            <input type="email" name="email" id="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('email', $user->email)); ?>">
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="is_blocked"><?php echo e(__('admin.status')); ?></label>
                            <select name="is_blocked" id="is_blocked" class="form-control <?php $__errorArgs = ['is_blocked'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="0" <?php echo e(old('is_blocked', $user->is_blocked) == 0 ? 'selected' : ''); ?>><?php echo e(__('admin.active')); ?></option>
                                <option value="1" <?php echo e(old('is_blocked', $user->is_blocked) == 1 ? 'selected' : ''); ?>><?php echo e(__('admin.blocked')); ?></option>
                                <option value="2" <?php echo e(old('is_blocked', $user->is_pending && !$user->is_blocked ? 2 : 0) == 2 ? 'selected' : ''); ?>><?php echo e(__('admin.pending')); ?></option>
                            </select>
                            <?php $__errorArgs = ['is_blocked'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="password"><?php echo e(__('admin.new_password')); ?></label>
                            <small><?php echo e(__('admin.leave_empty_to_keep_current')); ?></small>
                            <input type="password" name="password" id="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation"><?php echo e(__('admin.confirm_password')); ?></label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                        </div>

                        <button type="submit" name="save" class="btn btn-primary"><?php echo e(__('admin.save_continue')); ?></button>
                        <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-secondary"><?php echo e(__('admin.cancel')); ?></a>
                        <button type="submit" class="btn btn-primary"><?php echo e(__('admin.save')); ?></button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(__('admin.subscriptions_list')); ?></h3>
                    <a href="<?php echo e(route('admin.subscriptions.create', ['user' => $user->id, 'return_url' => url()->current()])); ?>"
                       class="btn btn-primary float-right">+ <?php echo e(__('admin.add')); ?></a>
                </div>
                <div class="card-body">
                    <table id="subscriptions-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 30px"><?php echo e(__('admin.id')); ?></th>
                            <th><?php echo e(__('admin.service')); ?></th>
                            <th><?php echo e(__('admin.status')); ?></th>
                            <th><?php echo e(__('admin.amount')); ?></th>
                            <th><?php echo e(__('admin.payment_info')); ?></th>
                            <th><?php echo e(__('admin.action')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $subscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subscription): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($subscription->id); ?></td>
                            <td>
                                <div class="d-flex" style="gap: 5px">
                                    <img src="<?php echo e(url($subscription->service->logo)); ?>"
                                         title="<?php echo e($subscription->service->code); ?>"
                                         class="img-fluid img-bordered" style="width: 35px;">
                                </div>
                            </td>
                            <td>
                                <?php if($subscription->status != \App\Models\Subscription::STATUS_ACTIVE): ?>
                                    <span class="badge badge-danger"><?php echo e(__('admin.canceled')); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-success"><?php echo e(__('admin.active')); ?></span>
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
                                <i class="fas fa-calendar-plus text-secondary mr-1" title="Next payment at"></i> <?php echo e(\Carbon\Carbon::parse($subscription->next_payment_at)->format('Y-m-d H:i')); ?> <br>
                                <i class="fas fa-receipt text-secondary mr-1" title="Last payment at"></i> <?php echo e($last?->created_at?->format('Y-m-d H:i') ?? '-'); ?>

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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/subcloudy/backend/resources/views/admin/users/edit.blade.php ENDPATH**/ ?>