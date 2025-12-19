<?php $__env->startSection('title', __('admin.edit_service_account') . ' #' . $serviceAccount->id); ?>

<?php $__env->startSection('content_header'); ?>
    <h1><?php echo e(__('admin.edit_service_account')); ?> #<?php echo e($serviceAccount->id); ?></h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <?php if(session('success')): ?>
            <div class="col-12">
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            </div>
        <?php endif; ?>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(__('admin.service_account_data')); ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('admin.service-accounts.update', $serviceAccount)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <div class="form-group">
                            <label for="type"><?php echo e(__('admin.service')); ?></label>
                            <select name="service_id" id="service_id"
                                    class="form-control <?php $__errorArgs = ['service_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($service->id); ?>" <?php echo e(old('service_id', $serviceAccount->service_id) == $service->id ? 'selected' : ''); ?>>
                                        <?php echo e($service->admin_name); ?>

                                    </option>
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
                            <label for="is_active"><?php echo e(__('admin.status')); ?></label>
                            <select name="is_active" id="is_active"
                                    class="form-control <?php $__errorArgs = ['is_active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option
                                    value="1" <?php echo e(old('is_active', $serviceAccount->is_active) == 1 ? 'selected' : ''); ?>>
                                    <?php echo e(__('admin.active')); ?>

                                </option>
                                <option
                                    value="0" <?php echo e(old('is_active', $serviceAccount->is_active) == 0 ? 'selected' : ''); ?>>
                                    <?php echo e(__('admin.inactive')); ?>

                                </option>
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
                            <label for="expiring_at">Expiring at</label>
                            <input type="datetime-local" name="expiring_at" id="expiring_at"
                                   class="form-control <?php $__errorArgs = ['expiring_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('expiring_at', $serviceAccount->expiring_at)); ?>">
                            <?php $__errorArgs = ['expiring_at'];
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
                            <label for="max_users"><?php echo e(__('admin.max_users_per_account')); ?></label>
                            <input type="number" name="max_users" id="max_users" min="1" max="1000"
                                   class="form-control <?php $__errorArgs = ['max_users'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   value="<?php echo e(old('max_users', $serviceAccount->max_users)); ?>"
                                   placeholder="<?php echo e(__('admin.unlimited')); ?>">
                            <small class="form-text text-muted">
                                <?php echo e(__('admin.max_users_per_account_hint')); ?>

                            </small>
                            <?php $__errorArgs = ['max_users'];
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

                        <div class="form-group">
                            <label style="font-size: 125%">Credentials</label>

                            <!-- Импорт cookies для Desktop App автологина -->
                            <div class="alert alert-info">
                                <strong>💡 Для автоматического входа в Desktop App:</strong>
                                <ol class="mb-0 pl-3">
                                    <li>Установите расширение <a href="https://chrome.google.com/webstore/detail/editthiscookie/fngmhnnpilhplaeedifhccceomclgfbg" target="_blank">EditThisCookie</a></li>
                                    <li>Откройте сервис и войдите в premium аккаунт</li>
                                    <li>Кликните на иконку расширения → Export</li>
                                    <li>Скопируйте JSON и вставьте ниже</li>
                                </ol>
                            </div>

                            <div class="form-group">
                                <label for="cookies_import">
                                    Cookies Import (JSON)
                                    <small class="text-muted">- для автологина в Desktop приложении</small>
                                </label>
                                <?php
                                    $existingCookies = old('cookies_import', 
                                        isset($serviceAccount->credentials['cookies']) 
                                            ? json_encode($serviceAccount->credentials['cookies'], JSON_PRETTY_PRINT) 
                                            : ''
                                    );
                                ?>
                                <textarea 
                                    name="cookies_import" 
                                    id="cookies_import"
                                    class="form-control <?php $__errorArgs = ['cookies_import'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    rows="8"
                                    placeholder='[{"name":"__Secure-next-auth.session-token","value":"...","domain":".chatgpt.com","path":"/","secure":true,"httpOnly":true}]'
                                ><?php echo e($existingCookies); ?></textarea>
                                <small class="form-text text-muted">
                                    Вставьте экспортированные cookies в формате JSON. Пример:
                                    <code>[{"name":"cookie_name","value":"cookie_value","domain":".example.com"}]</code>
                                </small>
                                <?php $__errorArgs = ['cookies_import'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <div id="cookies-status" class="mt-2"></div>
                            </div>

                            <?php if(isset($serviceAccount->credentials['cookies']) && is_array($serviceAccount->credentials['cookies']) && count($serviceAccount->credentials['cookies']) > 0): ?>
                                <div class="alert alert-success">
                                    ✅ Сохранено <?php echo e(count($serviceAccount->credentials['cookies'])); ?> cookie(s)
                                </div>
                            <?php endif; ?>

                            <hr class="my-3">

                            <div class="form-group">
                                <label for="email">Email <small class="text-muted">(опционально, для справки)</small></label>
                                <input type="text" name="credentials[email]" id="email"
                                       class="form-control <?php $__errorArgs = ['credentials.email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       value="<?php echo e(old('credentials.email', $serviceAccount->credentials['email'] ?? '')); ?>">
                                <?php $__errorArgs = ['credentials.email'];
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
                                <label for="password">Password <small class="text-muted">(опционально, для справки)</small></label>
                                <input type="text" name="credentials[password]" id="password"
                                       class="form-control <?php $__errorArgs = ['credentials.password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       value="<?php echo e(old('credentials.password', $serviceAccount->credentials['password'] ?? '')); ?>">
                                <?php $__errorArgs = ['credentials.password'];
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

                        <button type="submit" class="btn btn-primary mr-2"><?php echo e(__('admin.save')); ?></button>
                        <button type="submit" name="save" class="btn btn-primary mr-2"><?php echo e(__('admin.save')); ?> & <?php echo e(__('admin.continue')); ?></button>
                        <a href="<?php echo e(route('admin.service-accounts.index')); ?>" class="btn btn-secondary"><?php echo e(__('admin.cancel')); ?></a>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
    <script>
        // Валидация и обработка импорта cookies
        (function() {
            const cookiesTextarea = document.getElementById('cookies_import');
            const statusDiv = document.getElementById('cookies-status');
            const form = document.querySelector('form');

            if (cookiesTextarea) {
                // Валидация при вводе
                cookiesTextarea.addEventListener('blur', function() {
                    const value = this.value.trim();
                    if (!value) {
                        statusDiv.innerHTML = '';
                        return;
                    }

                    try {
                        const cookies = JSON.parse(value);
                        
                        if (!Array.isArray(cookies)) {
                            throw new Error('Cookies должны быть массивом');
                        }

                        const count = cookies.length;
                        statusDiv.innerHTML = `<div class="alert alert-success">✅ Найдено ${count} cookie(s). Формат корректен!</div>`;
                        
                        // Показать первые несколько для проверки
                        const preview = cookies.slice(0, 3).map(c => `<li><strong>${c.name}</strong> для домена <code>${c.domain || 'N/A'}</code></li>`).join('');
                        if (count > 0) {
                            statusDiv.innerHTML += `<small class="text-muted">Примеры:<ul class="mb-0">${preview}</ul></small>`;
                        }
                    } catch (e) {
                        statusDiv.innerHTML = `<div class="alert alert-danger">❌ Ошибка формата: ${e.message}</div>`;
                    }
                });

                // При отправке формы - объединяем cookies с credentials
                form && form.addEventListener('submit', function(e) {
                    const cookiesValue = cookiesTextarea.value.trim();
                    
                    if (cookiesValue) {
                        try {
                            const cookies = JSON.parse(cookiesValue);
                            
                            // Создаем скрытое поле с cookies для отправки
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'credentials[cookies]';
                            input.value = JSON.stringify(cookies);
                            form.appendChild(input);
                            
                            console.log('Cookies will be saved:', cookies.length);
                        } catch (e) {
                            e.preventDefault();
                            alert('Ошибка в формате cookies! Проверьте JSON.');
                            return false;
                        }
                    }
                });
            }
        })();

    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/subcloudy/backend/resources/views/admin/service-accounts/edit.blade.php ENDPATH**/ ?>