<?php $__env->startSection('title', __('admin.promocodes')); ?>

<?php $__env->startSection('content_header'); ?>
    <h1><?php echo e(__('admin.promocodes')); ?></h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(__('admin.promocodes_list')); ?></h3>
                    <a href="<?php echo e(route('admin.promocodes.create')); ?>" class="btn btn-primary float-right">+ <?php echo e(__('admin.add')); ?></a>
                    <a href="<?php echo e(route('admin.promocode-usages.index')); ?>"
                       class="btn btn-outline-secondary float-right mr-2"><?php echo e(__('admin.usages')); ?></a>
                </div>
                <div class="card-body">
                    <div class="mb-3 d-flex flex-wrap align-items-center">
                        <div class="form-inline mr-3 mb-2">
                            <label for="batch" class="mr-2"><?php echo e(__('admin.batch')); ?>:</label>
                            <select id="batch" class="select2 filter-select form-control form-control-sm">
                                <option value=""><?php echo e(__('admin.all')); ?></option>
                            </select>
                        </div>
                        <div class="form-inline mr-3 mb-2">
                            <label for="statusFilter" class="mr-2"><?php echo e(__('admin.status_filter')); ?>:</label>
                            <select id="statusFilter" class="select2 filter-select form-control form-control-sm">
                                <option value=""><?php echo e(__('admin.all')); ?></option>
                                <option value="Active"><?php echo e(__('admin.active')); ?></option>
                                <option value="Paused"><?php echo e(__('admin.paused')); ?></option>
                                <option value="Scheduled"><?php echo e(__('admin.scheduled')); ?></option>
                                <option value="Expired"><?php echo e(__('admin.expired')); ?></option>
                                <option value="Exhausted"><?php echo e(__('admin.exhausted')); ?></option>
                            </select>
                        </div>
                        <div class="form-inline mr-3 mb-2">
                            <label for="typeFilter" class="mr-2"><?php echo e(__('admin.type_filter')); ?>:</label>
                            <select id="typeFilter" class="select2 filter-select form-control form-control-sm">
                                <option value=""><?php echo e(__('admin.all')); ?></option>
                                <option value="Discount"><?php echo e(__('admin.discount')); ?></option>
                                <option value="Free access"><?php echo e(__('admin.free_access')); ?></option>
                            </select>
                        </div>
                        <button id="resetFilters" type="button" class="btn btn-sm btn-outline-secondary mb-2"><?php echo e(__('admin.reset_filters')); ?>

                        </button>
                    </div>
                    <div class="mb-3">
                        <button id="bulkDelete" class="btn btn-sm btn-danger" disabled><?php echo e(__('admin.delete_selected')); ?></button>
                    </div>
                    <table id="promocodes-table" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width: 36px"><input type="checkbox" id="selectAll"></th>
                            <th style="width: 60px"><?php echo e(__('admin.id')); ?></th>
                            <th>Код</th>
                            <th>Префикс</th>
                            <th><?php echo e(__('admin.batch')); ?></th>
                            <th><?php echo e(__('admin.type_filter')); ?></th>
                            <th><?php echo e(__('admin.discount')); ?></th>
                            <th>Использование</th>
                            <th>Действителен с</th>
                            <th>Действителен до</th>
                            <th><?php echo e(__('admin.status')); ?></th>
                            <th style="width: 60px"><?php echo e(__('admin.action')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $promocodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promocode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><input type="checkbox" class="row-select" value="<?php echo e($promocode->id); ?>"></td>
                                <td><?php echo e($promocode->id); ?></td>
                                <td><code><?php echo e($promocode->code); ?></code></td>
                                <td><code><?php echo e($promocode->prefix ?: '-'); ?></code></td>
                                <td><?php echo e($promocode->batch_id ?: '—'); ?></td>
                                <td>
                                    <?php if($promocode->type === 'free_access'): ?>
                                        <span class="badge badge-info"><?php echo e(__('admin.free_access')); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-primary"><?php echo e(__('admin.discount')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($promocode->percent_discount); ?>%</td>
                                <td>
                                    <?php if($promocode->usage_limit === 0): ?>
                                        <?php echo e($promocode->usage_count); ?> / ∞
                                    <?php else: ?>
                                        <?php echo e($promocode->usage_count); ?> / <?php echo e($promocode->usage_limit); ?>

                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($promocode->starts_at): ?>
                                        <?php echo e($promocode->starts_at->format('Y-m-d')); ?>

                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($promocode->expires_at): ?>
                                        <?php echo e($promocode->expires_at->format('Y-m-d')); ?>

                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $now = now();
                                        $paused = !$promocode->is_active;
                                        $expired = $promocode->expires_at && $promocode->expires_at->lt($now);
                                        $scheduled = $promocode->starts_at && $promocode->starts_at->gt($now);
                                        $exhausted = ($promocode->usage_limit ?? 0) > 0 && ($promocode->usage_count ?? 0) >= $promocode->usage_limit;
                                    ?>
                                    <?php if($paused): ?>
                                        <span class="badge badge-secondary"><?php echo e(__('admin.paused')); ?></span>
                                    <?php elseif($expired): ?>
                                        <span class="badge badge-dark"><?php echo e(__('admin.expired')); ?></span>
                                    <?php elseif($exhausted): ?>
                                        <span class="badge badge-warning"><?php echo e(__('admin.exhausted')); ?></span>
                                    <?php elseif($scheduled): ?>
                                        <span class="badge badge-info"><?php echo e(__('admin.scheduled')); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-success"><?php echo e(__('admin.active')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap">
                                    <a href="<?php echo e(route('admin.promocodes.edit', $promocode)); ?>"
                                       class="btn btn-sm btn-warning" title="<?php echo e(__('admin.edit')); ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo e(route('admin.promocode-usages.index', ['promocode' => $promocode->code])); ?>"
                                       class="btn btn-sm <?php echo e(($promocode->usage_count ?? 0) > 0 ? 'btn-info' : 'btn-secondary disabled'); ?>"
                                       title="<?php echo e(__('admin.usages')); ?>">
                                        <i class="fas fa-clipboard-list"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger" data-toggle="modal"
                                            data-target="#deleteModal-<?php echo e($promocode->id); ?>" title="<?php echo e(__('admin.delete')); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <div class="modal fade" id="deleteModal-<?php echo e($promocode->id); ?>" tabindex="-1"
                                         role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel"><?php echo e(__('admin.confirm_deletion')); ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php echo e(__('admin.are_you_sure_delete_promocode')); ?>

                                                </div>
                                                <div class="modal-footer">
                                                    <form action="<?php echo e(route('admin.promocodes.destroy', $promocode)); ?>"
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
    <style>
        .filter-select + .select2 .select2-selection {
            height: 38px !important;
        }

        .filter-select + .select2 .select2-selection__rendered {
            line-height: 35px !important;
        }

        .filter-select + .select2 .select2-selection__arrow {
            height: 38px !important;
            top: 0px;
        }

        #batch.filter-select + .select2 {
            min-width: 380px;
        }

        #statusFilter.filter-select + .select2,
        #typeFilter.filter-select + .select2 {
            min-width: 180px;
        }

        .badge-batch {
            font-size: 11px;
            margin-left: 5px;
            display: inline;
        }

        #resetFilters {
            height: 38px;
            display: inline-flex;
            align-items: center;
        }
    </style>
    <script>
        function formatBatch(option) {
            if (!option.id) return option.text;
            var active = $(option.element).data('active');
            if (active && active > 0) {
                return $(
                    '<span>' + option.text + ' <span class="badge badge-success badge-batch">' + active + '</span></span>'
                );
            }
            return option.text;
        }

        $(document).ready(function() {
            var table = $('#promocodes-table').DataTable({
                initComplete: function() {
                    var api = this.api();
                    var batches = {};
                    api.rows().every(function() {
                        var row = this.data();
                        var batchHtml = row[4];
                        var statusHtml = row[10];
                        var batchText = $('<div>').html(batchHtml).text().trim();
                        var statusText = $('<div>').html(statusHtml).text();
                        var statusClean = statusText.replace(/\s+/g, ' ').trim();
                        var isActiveOrScheduled = /\bActive\b/i.test(statusClean) || /\bScheduled\b/i.test(statusClean);
                        if (batchText && batchText !== '—' && isActiveOrScheduled) {
                            batches[batchText] = (batches[batchText] || 0) + 1;
                        }
                    });
                    var $batch = $('#batch');
                    Object.keys(batches).sort().forEach(function(b) {
                        var count = batches[b];
                        var $opt = $('<option/>', { value: b, text: b }).attr('data-active', count);
                        $batch.append($opt);
                    });
                }
            });

            var $batch = $('#batch');
            $batch.select2({
                placeholder: 'Select batch',
                allowClear: true,
                width: 'resolve',
                templateResult: formatBatch,
                templateSelection: formatBatch
            });

            $batch.on('change', function() {
                var val = $(this).val();
                // Batch column index is now 4 (0-based => 4) due to checkbox column
                table.column(4).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
            });

            // Status filter: filter by text in Status column
            $('#statusFilter').select2({ width: 'resolve', minimumResultsForSearch: -1 });
            $('#statusFilter').on('change', function() {
                var val = $(this).val();
                // Status column index is now 10
                table.column(10).search(val ? '^' + val + '$' : '', true, false).draw();
            });

            // Type filter: filter by text in Type column
            $('#typeFilter').select2({ width: 'resolve', minimumResultsForSearch: -1 });
            $('#typeFilter').on('change', function() {
                var val = $(this).val();
                // Type column index is now 5
                table.column(5).search(val ? '^' + val + '$' : '', true, false).draw();
            });

            // Reset all filters
            $('#resetFilters').on('click', function() {
                // Clear selects
                $('#batch').val('').trigger('change');
                $('#statusFilter').val('').trigger('change');
                $('#typeFilter').val('').trigger('change');
                // Clear table searches explicitly
                table.column(4).search('');
                table.column(5).search('');
                table.column(10).search('');
                table.search('');
                table.draw();
            });

            function selectedIds() {
                var ids = [];
                table.rows({ search: 'applied' }).every(function() {
                    var $node = $(this.node());
                    var $checkbox = $node.find('input.row-select');
                    if ($checkbox.prop('checked')) {
                        ids.push(parseInt($checkbox.val(), 10));
                    }
                });
                return ids;
            }

            // Select all toggle
            $('#selectAll').on('change', function() {
                var checked = this.checked;
                $('#promocodes-table tbody input.row-select').prop('checked', checked);
                $('#bulkDelete').prop('disabled', selectedIds().length === 0);
            });

            // Enable/disable bulk delete based on selection
            $('#promocodes-table').on('change', 'input.row-select', function() {
                $('#bulkDelete').prop('disabled', selectedIds().length === 0);
            });

            // Keep selection across pagination redraws
            table.on('draw', function() {
                if ($('#selectAll').prop('checked')) {
                    $('#promocodes-table tbody input.row-select').prop('checked', true);
                }
                $('#bulkDelete').prop('disabled', selectedIds().length === 0);
            });

            // Bulk delete action
            $('#bulkDelete').on('click', function() {
                var ids = selectedIds();
                if (!ids.length) return;
                if (!confirm('Удалить выбранные промокоды?')) return;
                $.ajax({
                    url: '<?php echo e(route('admin.promocodes.bulk-destroy')); ?>',
                    method: 'DELETE',
                    data: { ids: ids, _token: '<?php echo e(csrf_token()); ?>' },
                    success: function() {
                        location.reload();
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error');
                    }
                });
            });
        });
    </script>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/subcloudy/backend/resources/views/admin/promocodes/index.blade.php ENDPATH**/ ?>