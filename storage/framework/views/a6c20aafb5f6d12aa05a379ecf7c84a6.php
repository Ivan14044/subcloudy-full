<?php $__env->startSection('title', 'Добавить подписку'); ?>

<?php $__env->startSection('content_header'); ?>
    <h1>Добавить подписку</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12 col-lg-8 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Данные подписки</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('admin.subscriptions.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <?php if(request()->return_url): ?>
                            <input type="hidden" name="return_url" value="<?php echo e(request()->return_url); ?>">
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="status"><?php echo e(__('admin.status')); ?></label>
                            <select name="status" id="status" class="form-control <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="<?php echo e(\App\Models\Subscription::STATUS_ACTIVE); ?>" <?php echo e(old('status') == \App\Models\Subscription::STATUS_ACTIVE ? 'selected' : ''); ?>><?php echo e(__('admin.active')); ?></option>
                                <option value="<?php echo e(\App\Models\Subscription::STATUS_CANCELED); ?>" <?php echo e(old('status') == \App\Models\Subscription::STATUS_CANCELED ? 'selected' : ''); ?>><?php echo e(__('admin.canceled')); ?></option>
                                <option value="<?php echo e(\App\Models\Subscription::STATUS_ENDED); ?>" <?php echo e(old('status') == \App\Models\Subscription::STATUS_ENDED ? 'selected' : ''); ?>><?php echo e(__('admin.ended')); ?></option>
                            </select>
                            <?php $__errorArgs = ['status'];
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
                            <label for="next_payment_at"><?php echo e(__('admin.next_payment_at')); ?></label>
                            <input type="datetime-local"
                                   name="next_payment_at"
                                   id="next_payment_at"
                                   class="form-control <?php $__errorArgs = ['next_payment_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('next_payment_at')); ?>">
                            <?php $__errorArgs = ['next_payment_at'];
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
                            <label for="service_id"><?php echo e(__('admin.service')); ?></label>
                            <select name="service_id" id="service_id" class="form-control <?php $__errorArgs = ['service_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($service->id); ?>" <?php echo e(old('service_id') == $service->id ? 'selected' : ''); ?>><?php echo e($service->code); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['service_id'];
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
                            <label for="user_id">User</label>
                            <select name="user_id" id="user_id" class="select2 form-control <?php $__errorArgs = ['user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($user->id); ?>"
                                            <?php echo e(old('user_id', request()->user) == $user->id ? 'selected' : ''); ?>>
                                        #<?php echo e($user->id); ?> | <?php echo e($user->name); ?> - <?php echo e($user->email); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['user_id'];
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

                        <button type="submit" class="btn btn-primary"><?php echo e(__('admin.save')); ?></button>
                        <a href="<?php echo e(route('admin.subscriptions.index')); ?>" class="btn btn-secondary"><?php echo e(__('admin.cancel')); ?></a>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <style>
        .select2-selection {
            height: 38px!important;
            width: 100%;
        }
    </style>
    <script>
        $(document).ready(function () {
            $('#user_id').select2({
                placeholder: 'Select a user',
                allowClear: true
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/subcloudy/backend/resources/views/admin/subscriptions/create.blade.php ENDPATH**/ ?>