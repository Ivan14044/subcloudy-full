<?php $__env->startSection('title', __('admin.activity_history')); ?>

<?php $__env->startSection('content_header'); ?>
    <h1><?php echo e(__('admin.activity_history')); ?></h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo session('success'); ?>

                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo session('error'); ?>

                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(__('admin.activity_history')); ?></h3>
                </div>
                <div class="card-body">
                    <!-- Фильтры -->
                    <form method="GET" action="<?php echo e(route('admin.browser-sessions.index')); ?>" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo e(__('admin.user')); ?></label>
                                    <select name="user_id" class="form-control">
                                        <option value=""><?php echo e(__('admin.all')); ?></option>
                                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($user->id); ?>" <?php echo e(request('user_id') == $user->id ? 'selected' : ''); ?>>
                                                <?php echo e($user->name ?? $user->email); ?> (ID: <?php echo e($user->id); ?>)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo e(__('admin.service')); ?></label>
                                    <select name="service_id" class="form-control" style="z-index: 1000;">
                                        <option value=""><?php echo e(__('admin.all')); ?></option>
                                        <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $serviceName = $service->name ?? "Service {$service->id}";
                                                if (method_exists($service, 'getTranslation')) {
                                                    try {
                                                        $serviceName = $service->getTranslation('name', app()->getLocale()) 
                                                            ?? $service->getTranslation('name', 'en') 
                                                            ?? $service->name 
                                                            ?? "Service {$service->id}";
                                                    } catch (\Exception $e) {
                                                        $serviceName = $service->name ?? "Service {$service->id}";
                                                    }
                                                }
                                            ?>
                                            <option value="<?php echo e($service->id); ?>" <?php echo e(request('service_id') == $service->id ? 'selected' : ''); ?>>
                                                <?php echo e($serviceName); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label><?php echo e(__('admin.action')); ?></label>
                                    <select name="action" class="form-control">
                                        <option value=""><?php echo e(__('admin.all')); ?></option>
                                        <option value="session_started" <?php echo e(request('action') == 'session_started' ? 'selected' : ''); ?>>Запущен</option>
                                        <option value="session_stopped" <?php echo e(request('action') == 'session_stopped' ? 'selected' : ''); ?>>Остановлен</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label><?php echo e(__('admin.start_date')); ?></label>
                                    <input type="date" name="date_from" class="form-control" value="<?php echo e(request('date_from')); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label><?php echo e(__('admin.end_date')); ?></label>
                                    <input type="date" name="date_to" class="form-control" value="<?php echo e(request('date_to')); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary"><?php echo e(__('admin.filter')); ?></button>
                                <a href="<?php echo e(route('admin.browser-sessions.index')); ?>" class="btn btn-secondary"><?php echo e(__('admin.reset')); ?></a>
                            </div>
                        </div>
                    </form>

                    <!-- Таблица истории -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                            <tr>
                                <th style="width: 180px"><?php echo e(__('admin.date_time')); ?></th>
                                <th style="width: 200px"><?php echo e(__('admin.user')); ?></th>
                                <th style="width: 200px"><?php echo e(__('admin.service')); ?></th>
                                <th style="width: 120px"><?php echo e(__('admin.action')); ?></th>
                                <th style="width: 120px"><?php echo e(__('admin.duration')); ?></th>
                                <th style="width: 120px">IP</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e(\Carbon\Carbon::createFromTimestampMs($log->timestamp)->format('Y-m-d H:i:s')); ?></td>
                                    <td>
                                        <?php if($log->user): ?>
                                            <a href="<?php echo e(route('admin.users.edit', $log->user_id)); ?>" target="_blank">
                                                <?php echo e($log->user->name ?? $log->user->email); ?> (ID: <?php echo e($log->user_id); ?>)
                                            </a>
                                        <?php else: ?>
                                            ID: <?php echo e($log->user_id); ?>

                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                            // Приоритет: service_name из записи лога (переданное из desktop app)
                                            $serviceName = $log->service_name;
                                            
                                            // Если service_name не указан, пытаемся получить из связанной модели Service
                                            if (!$serviceName && $log->service) {
                                                try {
                                                    $serviceName = $log->service->getTranslation('name', 'ru') 
                                                        ?? $log->service->getTranslation('name', 'en') 
                                                        ?? $log->service->name 
                                                        ?? "Service {$log->service_id}";
                                                } catch (\Exception $e) {
                                                    $serviceName = $log->service->name ?? "Service {$log->service_id}";
                                                }
                                            }
                                            
                                            // Если все еще нет названия, используем дефолтное
                                            if (!$serviceName) {
                                                $serviceName = "Service {$log->service_id}";
                                            }
                                        ?>
                                        <?php echo e($serviceName); ?>

                                    </td>
                                    <td>
                                        <?php if($log->action === 'session_started'): ?>
                                            <span class="badge badge-success">Запущен</span>
                                        <?php elseif($log->action === 'session_stopped'): ?>
                                            <span class="badge badge-secondary">Остановлен</span>
                                        <?php else: ?>
                                            <span class="badge badge-info"><?php echo e($log->action); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($log->action === 'session_stopped' && $log->duration !== null && $log->duration >= 0): ?>
                                            <?php
                                                $seconds = $log->duration;
                                                $days = intdiv($seconds, 86400);
                                                $seconds %= 86400;
                                                $hours = intdiv($seconds, 3600);
                                                $seconds %= 3600;
                                                $minutes = intdiv($seconds, 60);
                                                $seconds %= 60;
                                                $parts = [];
                                                if ($days > 0) $parts[] = $days . 'д';
                                                if ($hours > 0) $parts[] = $hours . 'ч';
                                                if ($minutes > 0) $parts[] = $minutes . 'м';
                                                if ($seconds > 0 || empty($parts)) $parts[] = $seconds . 'с';
                                            ?>
                                            <?php echo e(implode(' ', $parts)); ?>

                                        <?php elseif($log->action === 'session_started'): ?>
                                            <span class="text-muted">—</span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($log->ip ?? '—'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <?php echo e(__('admin.no_records')); ?>

                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div class="mt-3">
                        <?php echo e($logs->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/subcloudy/backend/resources/views/admin/browser-sessions/index.blade.php ENDPATH**/ ?>