<?php $__env->startSection('title', __('admin.edit_service') . ' #' . $service->id); ?>

<?php $__env->startSection('content_header'); ?>
    <h1><?php echo e(__('admin.edit_service')); ?> #<?php echo e($service->id); ?></h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <?php if(session('success')): ?>
            <div class="col-12">
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            </div>
        <?php endif; ?>

        <div class="col-12 col-lg-8 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(__('admin.service_data')); ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('admin.services.update', $service)); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="form-group">
                            <label for="code"><?php echo e(__('admin.code')); ?></label>
                            <input type="text" readonly id="code" class="form-control"
                                   value="<?php echo e($service->code); ?>">
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
                                <option value="1" <?php echo e(old('is_active', $service->is_active) == 1 ? 'selected' : ''); ?>><?php echo e(__('admin.active')); ?></option>
                                <option value="0" <?php echo e(old('is_active', $service->is_active) == 0 ? 'selected' : ''); ?>><?php echo e(__('admin.inactive')); ?></option>
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

                        <div class="form-group">
                            <label for="amount"><?php echo e(__('admin.amount')); ?></label>
                            <input type="number" step="0.1" name="amount" id="amount" class="form-control <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('amount', $service->amount)); ?>">
                            <?php $__errorArgs = ['amount'];
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
                            <label for="trial_amount">Пробная сумма</label>
                            <input type="number" step="0.1" name="trial_amount" id="trial_amount" class="form-control <?php $__errorArgs = ['trial_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('trial_amount', $service->trial_amount)); ?>">
                            <?php $__errorArgs = ['trial_amount'];
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
                            <label for="position">Position</label>
                            <input type="number" step="1" name="position" id="position" class="form-control <?php $__errorArgs = ['position'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('position', $service->position)); ?>">
                            <?php $__errorArgs = ['position'];
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
                            <label for="logo">Logo</label>
                            <input type="hidden" name="logo_text" value="<?php echo e($service->logo); ?>">
                            <input type="file" accept="image/*" class="form-control-file <?php $__errorArgs = ['logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="logo" name="logo">
                            <?php if($service->logo != \App\Models\Service::DEFAULT_LOGO): ?>
                                <div id="logoImage">
                                    <img src="<?php echo e(url($service->logo)); ?>" class="img-fluid img-bordered mt-2" style="width: 150px;">
                                    <a href="#" onclick="removeLogo(event)" class="d-block mt-1">Delete</a>
                                </div>
                            <?php endif; ?>
                            <?php $__errorArgs = ['logo'];
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

                        <hr>
                        <hr>

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Browser settings</h3>
                            </div>
                            <div class="card-body p-3">
                                <div class="form-group">
                                    <label for="params_link">Service link (URL)</label>
                                    <input type="url" name="params[link]" id="params_link" class="form-control <?php $__errorArgs = ['params.link'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('params.link', $service->params['link'] ?? '')); ?>" placeholder="https://example.com">
                                    <?php $__errorArgs = ['params.link'];
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
                                    <label for="params_title">Window title</label>
                                    <input type="text" name="params[title]" id="params_title" class="form-control <?php $__errorArgs = ['params.title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('params.title', $service->params['title'] ?? '')); ?>" placeholder="Title">
                                    <?php $__errorArgs = ['params.title'];
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
                                    <label for="params_icon">Window icon (favicon)</label>
                                    <?php if(!empty($service->params['icon'] ?? '')): ?>
                                        <input type="hidden" name="params_icon_text" value="<?php echo e($service->params['icon']); ?>">
                                    <?php endif; ?>
                                    <input type="file" accept="image/*" class="form-control-file <?php $__errorArgs = ['params_icon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="params_icon" name="params_icon">
                                    <?php if(!empty($service->params['icon'] ?? '')): ?>
                                        <div id="iconImage">
                                            <img src="<?php echo e(url($service->params['icon'])); ?>" class="img-fluid img-bordered mt-2" style="width: 32px; height: 32px;">
                                            <a href="#" onclick="removeIcon(event)" class="d-block mt-1">Delete</a>
                                        </div>
                                    <?php endif; ?>
                                    <?php $__errorArgs = ['params_icon'];
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
                        </div>

                        <div class="card">
                            <div class="card-header no-border border-0 p-0">
                                <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                                    <?php $__currentLoopData = config('langs'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $flag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php ($hasError = $errors->has('name.' . $code) || $errors->has('description.' . $code)); ?>
                                        <li class="nav-item">
                                            <a class="nav-link <?php if($hasError): ?> text-danger <?php endif; ?> <?php echo e($code == 'en' ? 'active' : null); ?>"
                                               id="tab_<?php echo e($code); ?>" data-toggle="pill" href="#content_<?php echo e($code); ?>" role="tab">
                                                <span class="flag-icon flag-icon-<?php echo e($flag); ?> mr-1"></span> <?php echo e(strtoupper($code)); ?>  <?php if($hasError): ?>*<?php endif; ?>
                                            </a>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content">
                                    <?php $__currentLoopData = config('langs'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $flag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="tab-pane fade show <?php echo e($code == 'en' ? 'active' : null); ?>" id="content_<?php echo e($code); ?>" role="tabpanel">
                                            <div class="form-group">
                                                <label for="name_<?php echo e($code); ?>">Name</label>
                                                <input type="text" name="name[<?php echo e($code); ?>]" id="name_<?php echo e($code); ?>"
                                                       class="form-control <?php $__errorArgs = ['name.' . $code];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                       value="<?php echo e(old('name.' . $code, $serviceData[$code]['name'] ?? null)); ?>">
                                                <?php $__errorArgs = ['name.' . $code];
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
                                                <label for="subtitle_<?php echo e($code); ?>">Subtitle</label>
                                                <input type="text" name="subtitle[<?php echo e($code); ?>]" id="subtitle_<?php echo e($code); ?>"
                                                       class="form-control <?php $__errorArgs = ['subtitle.' . $code];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                       value="<?php echo e(old('subtitle.' . $code, $serviceData[$code]['subtitle'] ?? null)); ?>">
                                                <?php $__errorArgs = ['subtitle.' . $code];
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
                                                <label for="short_description_card_<?php echo e($code); ?>">Short description (card)</label>
                                                <textarea style="height: 210px"
                                                          name="short_description_card[<?php echo e($code); ?>]"
                                                          class="ckeditor form-control <?php $__errorArgs = ['short_description_card.' . $code];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                          id="short_description_card_<?php echo e($code); ?>"><?php echo old('short_description_card.' . $code, $serviceData[$code]['short_description_card'] ?? null); ?></textarea>
                                                <?php $__errorArgs = ['short_description_card.' . $code];
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
                                                <label for="short_description_checkout_<?php echo e($code); ?>">Short description (checkout)</label>
                                                <textarea style="height: 210px"
                                                          name="short_description_checkout[<?php echo e($code); ?>]"
                                                          class="ckeditor form-control <?php $__errorArgs = ['short_description_checkout.' . $code];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                          id="short_description_checkout_<?php echo e($code); ?>"><?php echo old('short_description_checkout.' . $code, $serviceData[$code]['short_description_checkout'] ?? null); ?></textarea>
                                                <?php $__errorArgs = ['short_description_checkout.' . $code];
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
                                                <label for="full_description_<?php echo e($code); ?>">Full Description</label>
                                                <textarea style="height: 210px"
                                                          name="full_description[<?php echo e($code); ?>]"
                                                          class="ckeditor form-control <?php $__errorArgs = ['full_description.' . $code];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                          id="full_description_<?php echo e($code); ?>"><?php echo old('full_description.' . $code, $serviceData[$code]['full_description'] ?? null); ?></textarea>
                                                <?php $__errorArgs = ['full_description.' . $code];
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
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mr-2"><?php echo e(__('admin.save')); ?></button>
                        <button type="submit" name="save" class="btn btn-primary mr-2"><?php echo e(__('admin.save')); ?> & <?php echo e(__('admin.continue')); ?></button>
                        <a href="<?php echo e(route('admin.services.index')); ?>" class="btn btn-secondary"><?php echo e(__('admin.cancel')); ?></a>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        document.querySelectorAll('.ckeditor').forEach(function (textarea) {
            ClassicEditor
                .create(textarea)
                .then(editor => {
                    editor.editing.view.change(writer => {
                        writer.setStyle('height', '170px', editor.editing.view.document.getRoot());
                    });
                })
                .catch(error => {
                    console.error(error);
                });
        });

        function removeLogo(event) {
            event.preventDefault();

            const logoImage = document.getElementById('logoImage');
            if (logoImage) {
                logoImage.remove();
            }

            const logoText = document.querySelector('input[name="logo_text"]');
            if (logoText) {
                logoText.remove();
            }

            const logoInput = document.getElementById('logo');
            if (logoInput) {
                logoInput.value = '';
            }
        }

        function removeIcon(event) {
            event.preventDefault();

            const iconImage = document.getElementById('iconImage');
            if (iconImage) {
                iconImage.remove();
            }

            const iconText = document.querySelector('input[name="params_icon_text"]');
            if (iconText) {
                iconText.remove();
            }

            const iconInput = document.getElementById('params_icon');
            if (iconInput) {
                iconInput.value = '';
            }
        }

    </script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/subcloudy/backend/resources/views/admin/services/edit.blade.php ENDPATH**/ ?>