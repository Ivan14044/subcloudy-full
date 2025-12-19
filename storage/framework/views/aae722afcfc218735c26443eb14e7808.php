<?php $__env->startSection('title', __('admin.add_promocode')); ?>

<?php $__env->startSection('content_header'); ?>
    <h1><?php echo e(__('admin.add_promocode')); ?></h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12 col-lg-8 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(__('admin.promocode_data')); ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('admin.promocodes.store')); ?>">
                        <?php echo csrf_field(); ?>

                        <div class="form-group">
                            <label for="quantity"><?php echo e(__('admin.quantity')); ?></label>
                            <input type="number" min="1" max="1000" name="quantity" id="quantity" class="form-control <?php $__errorArgs = ['quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('quantity', 1)); ?>">
                            <?php $__errorArgs = ['quantity'];
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

                        <div class="form-group" id="batch_id_group">
                            <label for="batch_id"><?php echo e(__('admin.batch')); ?></label>
                            <input type="text" name="batch_id" id="batch_id" class="form-control <?php $__errorArgs = ['batch_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('batch_id')); ?>" placeholder="<?php echo e(__('admin.leave_empty_for_auto_generation')); ?>">
                            <?php $__errorArgs = ['batch_id'];
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
                            <label for="prefix"><?php echo e(__('admin.prefix')); ?></label>
                            <input type="text" name="prefix" id="prefix" class="form-control <?php $__errorArgs = ['prefix'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('prefix')); ?>">
                            <?php $__errorArgs = ['prefix'];
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
                            <label for="code"><?php echo e(__('admin.code')); ?></label>
                            <div class="input-group">
                                <input type="text" name="code" id="code" class="form-control <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('code')); ?>">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-secondary" id="generate-code" title="<?php echo e(__('admin.generate')); ?>"><?php echo e(__('admin.generate')); ?></button>
                                </div>
                            </div>
                            <?php $__errorArgs = ['code'];
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
                            <label for="type"><?php echo e(__('admin.type_filter')); ?></label>
                            <select name="type" id="type" class="form-control <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="discount" <?php echo e(old('type', 'discount') == 'discount' ? 'selected' : ''); ?>><?php echo e(__('admin.discount')); ?></option>
                                <option value="free_access" <?php echo e(old('type') == 'free_access' ? 'selected' : ''); ?>><?php echo e(__('admin.free_access')); ?></option>
                            </select>
                            <?php $__errorArgs = ['type'];
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
                            <label for="percent_discount"><?php echo e(__('admin.percent_discount')); ?></label>
                            <input type="number" min="0" max="100" name="percent_discount" id="percent_discount" class="form-control <?php $__errorArgs = ['percent_discount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('percent_discount', 0)); ?>">
                            <?php $__errorArgs = ['percent_discount'];
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

                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                <tr>
                                    <th style="width: 40px">
                                        <input type="checkbox" id="select-all-services">
                                    </th>
                                    <th><?php echo e(__('admin.service')); ?></th>
                                    <th style="width: 160px"><?php echo e(__('admin.free_days')); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="<?php echo e($service->is_active ? '' : 'table-warning'); ?>">
                                        <td>
                                            <input type="checkbox" name="services[<?php echo e($service->id); ?>][selected]" value="1" <?php echo e(old('services.'.$service->id.'.selected') ? 'checked' : ''); ?>>
                                            <input type="hidden" name="services[<?php echo e($service->id); ?>][id]" value="<?php echo e($service->id); ?>">
                                        </td>
                                        <td>
                                            <?php echo e($service->getTranslation('name', 'en') ?? $service->admin_name ?? (__('admin.service') . ' #'.$service->id)); ?>

                                            <?php if (! ($service->is_active)): ?>
                                                <span class="badge badge-secondary ml-2"><?php echo e(__('admin.inactive')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php ($errKey = 'services.' . $service->id . '.free_days'); ?>
                                            <input type="number" min="0" class="form-control <?php echo e($errors->has($errKey) ? 'is-invalid' : ''); ?>" name="services[<?php echo e($service->id); ?>][free_days]" value="<?php echo e(old('services.'.$service->id.'.free_days', 0)); ?>">
                                            <?php if($errors->has($errKey)): ?>
                                                <div class="invalid-feedback d-block"><?php echo e($errors->first($errKey)); ?></div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="form-group">
                            <label for="per_user_limit"><?php echo e(__('admin.per_user_limit')); ?></label>
                            <input type="number" min="0" name="per_user_limit" id="per_user_limit" class="form-control <?php $__errorArgs = ['per_user_limit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('per_user_limit', 1)); ?>">
                            <?php $__errorArgs = ['per_user_limit'];
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
                            <label for="usage_limit"><?php echo e(__('admin.usage_limit')); ?></label>
                            <input type="number" min="0" name="usage_limit" id="usage_limit" class="form-control <?php $__errorArgs = ['usage_limit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('usage_limit', 0)); ?>">
                            <?php $__errorArgs = ['usage_limit'];
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

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="starts_at"><?php echo e(__('admin.starts_at')); ?></label>
                                <input type="datetime-local" name="starts_at" id="starts_at" class="form-control <?php $__errorArgs = ['starts_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('starts_at')); ?>">
                                <?php $__errorArgs = ['starts_at'];
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
                            <div class="form-group col-md-6">
                                <label for="expires_at"><?php echo e(__('admin.expires_at')); ?></label>
                                <input type="datetime-local" name="expires_at" id="expires_at" class="form-control <?php $__errorArgs = ['expires_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('expires_at')); ?>">
                                <?php $__errorArgs = ['expires_at'];
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
                        </div>

                        <div class="form-group">
                            <label for="is_active"><?php echo e(__('admin.status')); ?></label>
                            <select name="is_active" id="is_active" class="form-control <?php $__errorArgs = ['is_active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="1" <?php echo e(old('is_active', 1) == 1 ? 'selected' : ''); ?>><?php echo e(__('admin.active')); ?></option>
                                <option value="0" <?php echo e(old('is_active', 1) == 0 ? 'selected' : ''); ?>><?php echo e(__('admin.inactive')); ?></option>
                            </select>
                            <?php $__errorArgs = ['is_active'];
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

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary"><?php echo e(__('admin.create')); ?></button>
                            <a href="<?php echo e(route('admin.promocodes.index')); ?>" class="btn btn-secondary"><?php echo e(__('admin.cancel')); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        $(function () {
            $('#select-all-services').on('change', function () {
                const checked = this.checked;
                $('input[type="checkbox"][name$="[selected]"]').prop('checked', checked);
            });

            function generateCode(length) {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                let out = '';
                for (let i = 0; i < length; i++) {
                    out += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                return out;
            }

            $('#generate-code').on('click', function () {
                $('#code').val(generateCode(8));
            });

            function toggleByType() {
                const type = $('#type').val();
                const isDiscount = type === 'discount';
                // Percent field visible only for discount
                $('#percent_discount').closest('.form-group').toggle(isDiscount);
                // Services matrix visible only for free_access
                const showServices = !isDiscount;
                $('#select-all-services').closest('table').closest('.table-responsive').toggle(showServices);
            }

            function toggleByQuantity() {
                const qty = parseInt($('#quantity').val() || '1', 10);
                const isSingle = qty <= 1;
                $('#code').closest('.form-group').toggle(isSingle);
                $('#prefix').closest('.form-group').toggle(!isSingle);
                $('#batch_id_group').toggle(!isSingle);
            }

            toggleByType();
            toggleByQuantity();
            $('#type').on('change', toggleByType);
            $('#quantity').on('input change', toggleByQuantity);
        });
    </script>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/subcloudy/backend/resources/views/admin/promocodes/create.blade.php ENDPATH**/ ?>