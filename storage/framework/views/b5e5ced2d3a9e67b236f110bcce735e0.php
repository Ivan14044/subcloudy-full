<?php $__env->startSection('title', __('admin.settings')); ?>

<?php $__env->startSection('content_header'); ?>
    <h1><?php echo e(__('admin.settings')); ?></h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header no-border border-0 p-0">
                    <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active"
                               id="tab_subscriptions" data-toggle="pill" href="#content_subscriptions" role="tab">
                                Настройки подписок
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link"
                               id="tab_header_menu" data-toggle="pill" href="#content_header_menu" role="tab">
                                Меню шапки
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link"
                               id="tab_footer_menu" data-toggle="pill" href="#content_footer_menu" role="tab">
                                Меню подвала
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link"
                               id="tab_smtp" data-toggle="pill" href="#content_smtp" role="tab">
                                SMTP
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link"
                               id="tab_cookie" data-toggle="pill" href="#content_cookie" role="tab">
                                Cookie
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="content_subscriptions" role="tabpanel">
                            <form method="POST" action="<?php echo e(route('admin.settings.store')); ?>">
                                <input type="hidden" name="form" value="subscriptions">
                                <?php echo csrf_field(); ?>
                                <div class="form-group">
                                    <label for="trial_days">Пробные дни</label>
                                    <input type="text" name="trial_days" id="trial_days" class="form-control <?php $__errorArgs = ['trial_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           value="<?php echo e(old('trial_days', \App\Models\Option::get('trial_days'))); ?>">
                                    <?php $__errorArgs = ['trial_days'];
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
                                    <label for="currency">Валюта</label>
                                    <select name="currency" id="currency" class="form-control <?php $__errorArgs = ['currency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                        <option value="usd" <?php echo e(old('currency', $currency) == 'usd' ? 'selected' : ''); ?>>USD</option>
                                        <option value="eur" <?php echo e(old('currency', $currency) == 'eur' ? 'selected' : ''); ?>>EUR</option>
                                        <option value="uah" <?php echo e(old('currency', $currency) == 'uah' ? 'selected' : ''); ?>>UAH</option>
                                        <option value="rub" <?php echo e(old('currency', $currency) == 'rub' ? 'selected' : ''); ?>>RUB</option>
                                        <option value="byn" <?php echo e(old('currency', $currency) == 'byn' ? 'selected' : ''); ?>>BYN</option>
                                        <option value="kzt" <?php echo e(old('currency', $currency) == 'kzt' ? 'selected' : ''); ?>>KZT</option>
                                        <option value="gel" <?php echo e(old('currency', $currency) == 'gel' ? 'selected' : ''); ?>>GEL</option>
                                        <option value="mdl" <?php echo e(old('currency', $currency) == 'mdl' ? 'selected' : ''); ?>>MDL</option>
                                        <option value="pln" <?php echo e(old('currency', $currency) == 'pln' ? 'selected' : ''); ?>>PLN</option>
                                        <option value="chf" <?php echo e(old('currency', $currency) == 'chf' ? 'selected' : ''); ?>>CHF</option>
                                        <option value="sek" <?php echo e(old('currency', $currency) == 'sek' ? 'selected' : ''); ?>>SEK</option>
                                        <option value="czk" <?php echo e(old('currency', $currency) == 'czk' ? 'selected' : ''); ?>>CZK</option>
                                    </select>
                                    <?php $__errorArgs = ['currency'];
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
                                    <label for="discount_2">Скидка за 2 сервиса (%)</label>
                                    <input type="number" step="1" min="0" max="99" name="discount_2" id="discount_2" class="form-control <?php $__errorArgs = ['discount_2'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           value="<?php echo e(old('discount_2', \App\Models\Option::get('discount_2'))); ?>">
                                    <?php $__errorArgs = ['discount_2'];
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
                                    <label for="discount_3">Скидка за 3 сервиса (%)</label>
                                    <input type="number" step="1" min="0" max="99" name="discount_3" id="discount_3" class="form-control <?php $__errorArgs = ['discount_3'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           value="<?php echo e(old('discount_3', \App\Models\Option::get('discount_3'))); ?>">
                                    <?php $__errorArgs = ['discount_3'];
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
                            </form>
                        </div>
                        <div class="tab-pane" id="content_header_menu" role="tabpanel">
                            <div class="card">
                                <div class="card-header no-border border-0 p-0">
                                    <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                                        <?php $__currentLoopData = config('langs'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $flag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo e($code == 'en' ? 'active' : null); ?>"
                                                   id="tab_<?php echo e($code); ?>" data-toggle="pill" href="#tab_content_<?php echo e($code); ?>" role="tab">
                                                    <span class="flag-icon flag-icon-<?php echo e($flag); ?> mr-1"></span> <?php echo e(strtoupper($code)); ?>

                                                </a>
                                            </li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <form class="save-menu-form" method="POST" action="<?php echo e(route('admin.settings.store')); ?>">
                                        <input type="hidden" name="form" value="header_menu">
                                        <?php echo csrf_field(); ?>
                                        <div class="tab-content">
                                            <?php $__currentLoopData = config('langs'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $flag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <input type="hidden" name="header_menu[<?php echo e($code); ?>]" value="">
                                                <div class="tab-pane fade show <?php echo e($code == 'en' ? 'active' : null); ?>" id="tab_content_<?php echo e($code); ?>" role="tabpanel">
                                                    <div class="mb-4">
                                                        <div class="row g-1 align-items-center">
                                                            <div class="col-md-4">
                                                                <input type="text" class="form-control" name="title" placeholder="Title">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <input type="text" class="form-control" name="link" placeholder="Link">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" id="isBlank<?php echo e($code); ?>" name="is_blank">
                                                                    <label class="form-check-label" for="isBlank<?php echo e($code); ?>">_blank</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <button type="button" data-lang="<?php echo e($code); ?>"
                                                                        data-type="header"
                                                                        class="btn btn-primary w-100 add-item"><i class="fas fa-plus"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <ul class="list-group mb-3 menu-list" data-type="header" data-lang="<?php echo e($code); ?>"></ul>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <button type="submit" class="btn btn-primary"><?php echo e(__('admin.save')); ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="content_footer_menu" role="tabpanel">
                            <div class="card">
                                <div class="card-header no-border border-0 p-0">
                                    <ul class="nav nav-tabs" id="custom-tabs-one-tab-footer" role="tablist">
                                        <?php $__currentLoopData = config('langs'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $flag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo e($code == 'en' ? 'active' : null); ?>"
                                                   id="tab_<?php echo e($code); ?>_footer" data-toggle="pill" href="#tab_content_<?php echo e($code); ?>_footer" role="tab">
                                                    <span class="flag-icon flag-icon-<?php echo e($flag); ?> mr-1"></span> <?php echo e(strtoupper($code)); ?>

                                                </a>
                                            </li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <form class="save-menu-form" method="POST" action="<?php echo e(route('admin.settings.store')); ?>">
                                        <input type="hidden" name="form" value="footer_menu">
                                        <?php echo csrf_field(); ?>
                                        <div class="tab-content">
                                            <?php $__currentLoopData = config('langs'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $flag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <input type="hidden" name="footer_menu[<?php echo e($code); ?>]" value="">
                                                <div class="tab-pane fade show <?php echo e($code == 'en' ? 'active' : null); ?>" id="tab_content_<?php echo e($code); ?>_footer" role="tabpanel">
                                                    <div class="mb-4">
                                                        <div class="row g-1 align-items-center">
                                                            <div class="col-md-4">
                                                                <input type="text" class="form-control" name="title" placeholder="Название">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <input type="text" class="form-control" name="link" placeholder="Ссылка">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" id="isBlank<?php echo e($code); ?>Footer" name="is_blank">
                                                                    <label class="form-check-label" for="isBlank<?php echo e($code); ?>Footer">_blank</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <button type="button" data-lang="<?php echo e($code); ?>"
                                                                        data-type="footer"
                                                                        class="btn btn-primary w-100 add-item"><i class="fas fa-plus"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <ul class="list-group mb-3 menu-list" data-type="footer" data-lang="<?php echo e($code); ?>"></ul>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <button type="submit" class="btn btn-primary"><?php echo e(__('admin.save')); ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="content_cookie" role="tabpanel">
                            <form method="POST" action="<?php echo e(route('admin.settings.store')); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="form" value="cookie">
                                <label for="">Отображать согласие на использование cookie для этих стран</label>
                                <div class="row">
                                    <?php $__currentLoopData = config('countries'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-md-4">
                                            <div class="form-check mb-2">
                                                <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        id="cookie_country_<?php echo e($code); ?>"
                                                        name="cookie_countries[]"
                                                        value="<?php echo e($code); ?>"
                                                        <?php echo e(in_array($code, old('cookie_countries', json_decode(\App\Models\Option::get('cookie_countries', '[]'), true))) ? 'checked' : ''); ?>

                                                >
                                                <label class="form-check-label" for="cookie_country_<?php echo e($code); ?>">
                                                    <span class="flag-icon flag-icon-<?php echo e(strtolower($code)); ?>"></span>
                                                    <?php echo e($name); ?>

                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php $__errorArgs = ['cookie_countries'];
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

                                <button type="submit" class="btn btn-primary mt-3"><?php echo e(__('admin.save')); ?></button>
                            </form>
                        </div>
                        <div class="tab-pane" id="content_smtp" role="tabpanel">
                            <form method="POST" action="<?php echo e(route('admin.settings.store')); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="form" value="smtp">
                                <div class="form-group">
                                    <label for="from_address">Адрес отправителя</label>
                                    <input type="email" name="from_address" id="from_address"
                                           class="form-control <?php $__errorArgs = ['from_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           value="<?php echo e(old('from_address', \App\Models\Option::get('from_address'))); ?>">
                                    <?php $__errorArgs = ['from_address'];
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
                                    <label for="from_name">Имя отправителя</label>
                                    <input type="text" name="from_name" id="from_name"
                                           class="form-control <?php $__errorArgs = ['from_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           value="<?php echo e(old('from_name', \App\Models\Option::get('from_name'))); ?>">
                                    <?php $__errorArgs = ['from_name'];
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
                                    <label for="host">Хост</label>
                                    <input type="text" name="host" id="host"
                                           class="form-control <?php $__errorArgs = ['host'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           value="<?php echo e(old('host', \App\Models\Option::get('host'))); ?>">
                                    <?php $__errorArgs = ['host'];
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
                                    <label for="port">Порт</label>
                                    <input type="text" name="port" id="port"
                                           class="form-control <?php $__errorArgs = ['port'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           value="<?php echo e(old('port', \App\Models\Option::get('port'))); ?>">
                                    <?php $__errorArgs = ['port'];
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
                                    <label for="encryption">Шифрование</label>
                                    <input type="text" name="encryption" id="encryption"
                                           class="form-control <?php $__errorArgs = ['encryption'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           value="<?php echo e(old('encryption', \App\Models\Option::get('encryption'))); ?>">
                                    <?php $__errorArgs = ['encryption'];
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
                                    <label for="username">Имя пользователя</label>
                                    <input type="text" name="username" id="username"
                                           class="form-control <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           value="<?php echo e(old('username', \App\Models\Option::get('username'))); ?>">
                                    <?php $__errorArgs = ['username'];
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
                                    <label for="password">Пароль</label>
                                    <input type="text" name="password" id="password"
                                           class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           value="<?php echo e(old('password', \App\Models\Option::get('password'))); ?>">
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

                                <button type="submit" class="btn btn-primary mt-3"><?php echo e(__('admin.save')); ?></button>
                            </form>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <style>
        .menu-list li {
            list-style: none!important;
        }
    </style>
    <script>
        $(function () {
            let $menuLists = $('.menu-list');
            let $addItems = $('.add-item');
            $menuLists.sortable({
                placeholder: "ui-state-highlight"
            });

            $addItems.on('click', function () {
                const $box = $(this).parent().parent();
                const title = $box.find('input[name="title"]').val();
                const link = $box.find('input[name="link"]').val();
                const isBlank = $box.find('input[name="is_blank"]').is(':checked');

                const itemHtml = `
            <li class="list-group-item d-flex justify-content-between align-items-center menu-item">
              <div>
                <strong>${title}</strong> - ${link}
                ${isBlank ? '<span class="mr-1 badge bg-secondary">blank</span>' : ''}
              </div>
              <button class="btn btn-sm btn-danger remove-item"><i class="fas fa-trash"></i></button>
              <input type="hidden" name="title[]" value="${title}">
              <input type="hidden" name="link[]" value="${link}">
              <input type="hidden" name="is_blank[]" value="${isBlank}">
            </li>
          `;

                $menuLists.filter('[data-type="' + $(this).data('type') + '"][data-lang="' + $(this).data('lang') + '"]').first().append(itemHtml);

                $box.find('input[name="title"]').val('');
                $box.find('input[name="link"]').val('');
                $box.find('input[name="is_blank"]').prop('checked', false);
            });

            $menuLists.on('click', '.remove-item', function () {
                $(this).closest('li').remove();
            });

            $('.save-menu-form').on('submit', function (e) {
                e.preventDefault();
                let $form = $(this);

                $(this).find('.menu-list').each(function () {
                    const data = [];
                    let lang = $(this).closest('ul').data('lang');
                    let type = $(this).closest('ul').data('type');

                    $(this).find('li').each(function () {
                        data.push({
                            title: $(this).find('input[name="title[]"]').val(),
                            link: $(this).find('input[name="link[]"]').val(),
                            is_blank: $(this).find('input[name="is_blank[]"]').val() === 'true',
                        });
                    });

                    $form.find('[name="' + type + '_menu[' + lang + ']"]').val(JSON.stringify(data));
                });

                this.submit();
            });

            // Load data
            let headerMenu = <?php echo json_encode(\App\Models\Option::get('header_menu'), 15, 512) ?>;
            let footerMenu = <?php echo json_encode(\App\Models\Option::get('footer_menu'), 15, 512) ?>;
            loadData('header', headerMenu);
            loadData('footer', footerMenu);

            function loadData(type, menu) {
                let menuData = JSON.parse(menu);

                for (const lang in menuData) {
                    const raw = menuData[lang];
                    if (!raw) continue;

                    const items = JSON.parse(raw);

                    items.forEach(item => {
                        const itemHtml = `
    <li class="list-group-item d-flex justify-content-between align-items-center menu-item">
      <div>
        <strong>${item.title}</strong> - ${item.link}
        ${item.is_blank ? '<span class="mr-1 badge bg-secondary">blank</span>' : ''}
      </div>
      <button class="btn btn-sm btn-danger remove-item"><i class="fas fa-trash"></i></button>
      <input type="hidden" name="title[]" value="${item.title}">
      <input type="hidden" name="link[]" value="${item.link}">
      <input type="hidden" name="is_blank[]" value="${item.is_blank}">
    </li>
  `;

                        $('.menu-list[data-type="' + type + '"][data-lang="' + lang + '"]').append(itemHtml);
                    });
                }
            }

            const activeTab = <?php echo json_encode(old('form', session('active_tab')), 512) ?>;
            if (activeTab) {
                const tabId = '#tab_' + activeTab;
                const paneId = '#content_' + activeTab;

                $('a.nav-link').removeClass('active');
                $('.tab-pane').removeClass('show active');

                $(tabId).addClass('active');
                $(paneId).addClass('show active');
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/subcloudy/backend/resources/views/admin/settings/index.blade.php ENDPATH**/ ?>