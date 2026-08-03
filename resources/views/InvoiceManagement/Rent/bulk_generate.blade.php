@extends('Partials.app', ['activeMenu' => 'invoices'])

@section('title') Generate Monthly Rent Invoices @endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/jspreadsheet/jsuites.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/jspreadsheet/jexcel.css') }}">
    <style>
        .bulk-summary-bar {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 12px 18px;
            min-height: 52px;
        }
        .bulk-summary-item {
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .jexcel_container {
            width: 100% !important;
            font-family: inherit;
        }
        .jexcel {
            width: 100% !important;
        }
        .jexcel > tbody > tr > td {
            white-space: nowrap !important;
        }
        .btn-rent-details {
            padding: 1px 5px !important;
            font-size: 0.72rem !important;
            line-height: 1.2 !important;
            border-radius: 4px;
        }
        #header-select-all {
            cursor: pointer;
            margin: 0;
            vertical-align: middle;
        }
        .jexcel thead td:nth-child(2) {
            text-align: center;
        }
    </style>
@endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">
                <i class="fa fa-calendar-plus me-2 text-primary"></i> Bulk Generate Monthly Rent Invoices
            </h3>
            <a href="{{ route('invoices.rent.index') }}" class="btn btn-secondary btn-sm float-end">
                <i class="fa fa-arrow-left me-1"></i> Back to Rent Invoices
            </a>
        </div>

        <div class="block-content fs-sm">
            <!-- Filter Toolbar -->
            <div class="row align-items-end mb-4 bg-body-light p-3 border rounded">
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label fw-bold text-muted fs-xs text-uppercase mb-1" for="select_month">Select Month</label>
                    <select class="form-select form-select-sm" id="select_month">
                        @foreach(range(1, 12) as $m)
                            @php $monthVal = str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                            <option value="{{ $monthVal }}" {{ date('m') == $monthVal ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }} ({{ $monthVal }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label fw-bold text-muted fs-xs text-uppercase mb-1" for="select_year">Select Year</label>
                    <select class="form-select form-select-sm" id="select_year">
                        @foreach(range(date('Y') - 1, date('Y') + 2) as $y)
                            <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary w-100" id="btn-load-agreements">
                        <i class="fa fa-search me-1"></i> Load Agreements
                    </button>
                </div>
            </div>

            <!-- Summary Bar -->
            <div class="bulk-summary-bar mb-3 d-none" id="summary-bar">
                <div class="row align-items-center">
                    <div class="col-lg-2 col-md-3 mb-2 mb-lg-0 bulk-summary-item text-nowrap">
                        <i class="fa fa-list me-1 text-primary"></i> Total: <span id="summary-total" class="fw-bold text-dark">0</span>
                    </div>
                    <div class="col-lg-3 col-md-3 mb-2 mb-lg-0 bulk-summary-item text-success text-nowrap">
                        <i class="fa fa-check-circle me-1"></i> Selected Pending: <span id="summary-selected-count" class="fw-bold">0</span>
                    </div>
                    <div class="col-lg-3 col-md-3 mb-2 mb-lg-0 bulk-summary-item text-secondary text-nowrap">
                        <i class="fa fa-file-invoice me-1"></i> Already Invoiced: <span id="summary-already-count" class="fw-bold">0</span>
                    </div>
                    <div class="col-lg-2 col-md-3 mb-2 mb-lg-0 bulk-summary-item text-warning d-none text-nowrap" id="summary-missing-wrapper">
                        <i class="fa fa-exclamation-triangle me-1"></i> Missing Vendor: <span id="summary-missing-count" class="fw-bold">0</span>
                    </div>
                    <div class="col-lg-4 col-md-12 text-lg-end bulk-summary-item text-primary fs-base ms-auto text-nowrap">
                        Total: <span id="summary-selected-amount" class="fw-bold fs-5 text-primary ms-1">৳ 0.00</span>
                    </div>
                </div>
            </div>

            <!-- jspreadsheet Grid Container -->
            <div class="mb-4">
                <div id="rent-invoice-grid" class="w-100"></div>
            </div>

            <!-- Actions Footer -->
            <div class="d-none justify-content-between align-items-center border-top pt-3 pb-3" id="grid-actions-footer">
                <div>
                    <button type="button" class="btn btn-sm btn-alt-primary me-2" id="btn-select-all">
                        <i class="fa fa-check-square me-1"></i> Select All Pending
                    </button>
                    <button type="button" class="btn btn-sm btn-alt-secondary" id="btn-deselect-all">
                        <i class="fa fa-square me-1"></i> Deselect All
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" id="btn-generate-invoices" disabled>
                        <i class="fa fa-paper-plane me-1"></i> Generate Invoices
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rent Breakdown Modal -->
<div class="modal fade" id="modal-rent-details" tabindex="-1" aria-labelledby="modalRentDetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-popout">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title fs-sm text-white mb-0" id="modalRentDetailsLabel">
                    <i class="fa fa-info-circle me-1"></i> Rent Breakdown & Information
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" id="modal-rent-details-body">
                <div class="text-center py-4 text-muted">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2 mb-0">Loading breakdown details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Alert & Confirm Dialog Modal -->
<div class="modal fade" id="modal-custom-dialog" tabindex="-1" aria-labelledby="modalCustomDialogLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header border-0 py-3 text-white" id="dialog-header-bg">
                <h5 class="modal-title fs-sm fw-bold text-white mb-0" id="dialog-title">
                    <i class="fa me-2" id="dialog-icon"></i><span id="dialog-title-text">Notification</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center fs-sm" id="dialog-body-text">
                <!-- Dynamic Message -->
            </div>
            <div class="modal-footer border-0 bg-body-light justify-content-center py-2" id="dialog-footer">
                <button type="button" class="btn btn-sm btn-secondary me-2 d-none" id="dialog-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" id="dialog-btn-confirm">OK</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/plugins/jspreadsheet/jsuites.js') }}"></script>
    <script src="{{ asset('js/plugins/jspreadsheet/jexcel.js') }}"></script>

    <script>
    (function() {
        'use strict';

        let grid = null;
        let rowMeta = [];
        let currentBillingMonth = '';

        const COL = {
            SELECT: 0,
            AGREEMENT: 1,
            SITE_CODE: 2,
            BUILDING_NAME: 3,
            FLOOR_INFO: 4,
            VENDOR: 5,
            DETAILS: 6,
            TYPE: 7,
            BASE_RENT: 8,
            INCREMENT: 9,
            EFFECTIVE: 10,
            VAT: 11,
            TAX: 12,
            SUBTOTAL: 13,
            DISCOUNT: 14,
            TOTAL: 15,
            INV_DATE: 16,
            DUE_DATE: 17,
            REMARKS: 18
        };

        function showAlert(title, message, type = 'info', onOk = null) {
            const dialogEl = document.getElementById('modal-custom-dialog');
            const iconClass = type === 'success' ? 'fa-check-circle' :
                              type === 'danger'  ? 'fa-exclamation-triangle' :
                              type === 'warning' ? 'fa-exclamation-circle' : 'fa-info-circle';

            const bgClass   = type === 'success' ? 'bg-success' :
                              type === 'danger'  ? 'bg-danger' :
                              type === 'warning' ? 'bg-warning' : 'bg-primary';

            const btnClass  = type === 'danger'  ? 'btn-danger' :
                              type === 'warning' ? 'btn-warning' :
                              type === 'success' ? 'btn-success' : 'btn-primary';

            const triggerShow = function() {
                $('#dialog-header-bg').attr('class', 'modal-header border-0 py-3 text-white ' + bgClass);
                $('#dialog-icon').attr('class', 'fa me-2 ' + iconClass);
                $('#dialog-title-text').text(title);
                $('#dialog-body-text').html(message);

                $('#dialog-btn-cancel').addClass('d-none');
                $('#dialog-btn-confirm').attr('class', 'btn btn-sm ' + btnClass).text('OK').off('click').on('click', function() {
                    const inst = bootstrap.Modal.getInstance(dialogEl);
                    if (inst) inst.hide();
                    if (typeof onOk === 'function') onOk();
                });

                const bsModal = bootstrap.Modal.getOrCreateInstance(dialogEl);
                bsModal.show();
            };

            const existingModal = bootstrap.Modal.getInstance(dialogEl);
            if (existingModal && $(dialogEl).hasClass('show')) {
                $(dialogEl).one('hidden.bs.modal', function() {
                    setTimeout(triggerShow, 50);
                });
                existingModal.hide();
            } else {
                triggerShow();
            }
        }

        function showConfirm(title, message, onConfirm, type = 'primary') {
            const dialogEl = document.getElementById('modal-custom-dialog');
            $('#dialog-header-bg').attr('class', 'modal-header border-0 py-3 text-white bg-primary');
            $('#dialog-icon').attr('class', 'fa me-2 fa-question-circle');
            $('#dialog-title-text').text(title);
            $('#dialog-body-text').html(message);

            $('#dialog-btn-cancel').removeClass('d-none');
            $('#dialog-btn-confirm').attr('class', 'btn btn-sm btn-primary').text('Yes, Generate').off('click').on('click', function() {
                const inst = bootstrap.Modal.getInstance(dialogEl);
                if (inst) {
                    $(dialogEl).one('hidden.bs.modal', function() {
                        if (typeof onConfirm === 'function') onConfirm();
                    });
                    inst.hide();
                } else {
                    if (typeof onConfirm === 'function') onConfirm();
                }
            });

            const bsModal = bootstrap.Modal.getOrCreateInstance(dialogEl);
            bsModal.show();
        }

        function initGrid(data) {
            if (grid) {
                try {
                    jspreadsheet.destroy(document.getElementById('rent-invoice-grid'));
                } catch(e) {
                    $('#rent-invoice-grid').html('');
                }
                grid = null;
            }

            const container = document.getElementById('rent-invoice-grid');
            container.innerHTML = '';

            grid = jspreadsheet(container, {
                data: data,
                rowHeaders: true,
                columns: [
                    { type: 'checkbox', title: '☐', width: 40, sorting: false },
                    { type: 'text', title: 'Agreement Ref', width: 140, readOnly: true },
                    { type: 'text', title: 'Site Code', width: 110, readOnly: true },
                    { type: 'text', title: 'Building Name', width: 160, readOnly: true },
                    { type: 'text', title: 'Floor Info', width: 130, readOnly: true },
                    { type: 'text', title: 'Vendor', width: 180, readOnly: true },
                    { type: 'html', title: 'Details', width: 55, readOnly: true, align: 'center', sorting: false },
                    { type: 'text', title: 'Rent Type', width: 100, readOnly: true },
                    { type: 'numeric', title: 'Base Rent (৳)', width: 140, readOnly: true, mask: '#,##0.00' },
                    { type: 'numeric', title: 'Increment (৳)', width: 120, readOnly: true, mask: '#,##0.00' },
                    { type: 'numeric', title: 'Effective Rent (৳)', width: 140, readOnly: true, mask: '#,##0.00' },
                    { type: 'numeric', title: 'VAT (৳)', width: 100, readOnly: true, mask: '#,##0.00' },
                    { type: 'numeric', title: 'Tax (৳)', width: 100, readOnly: true, mask: '#,##0.00' },
                    { type: 'numeric', title: 'Subtotal (৳)', width: 140, readOnly: true, mask: '#,##0.00' },
                    { type: 'numeric', title: 'Discount (৳)', width: 120, mask: '#,##0.00' },
                    { type: 'numeric', title: 'Total (৳)', width: 150, readOnly: true, mask: '#,##0.00' },
                    { type: 'calendar', title: 'Invoice Date', width: 120, options: { format: 'YYYY-MM-DD' } },
                    { type: 'calendar', title: 'Due Date', width: 120, options: { format: 'YYYY-MM-DD' } },
                    { type: 'text', title: 'Remarks', width: 220 },
                ],
                columnSorting: true,
                columnResize: true,
                freezeColumns: 7,
                tableOverflow: true,
                tableWidth: '100%',
                tableHeight: '480px',
                allowInsertRow: false,
                allowInsertColumn: false,
                allowDeleteRow: false,
                allowDeleteColumn: false,
                allowRenameColumn: false,

                onchange: function(instance, cell, x, y, value) {
                    const colIdx = parseInt(x);
                    const rowIdx = parseInt(y);

                    if (colIdx === COL.SELECT && rowMeta[rowIdx] && rowMeta[rowIdx].status !== 'pending') {
                        if (grid.options && grid.options.data && grid.options.data[rowIdx]) {
                            grid.options.data[rowIdx][COL.SELECT] = false;
                        }
                        updateSummaryBar();
                        return;
                    }

                    if (colIdx === COL.DISCOUNT) {
                        recalculateRow(rowIdx);
                    }
                    if (colIdx === COL.SELECT) {
                        updateSummaryBar();
                    }
                },

                onload: function() {
                    injectHeaderCheckbox();
                    setTimeout(function() {
                        applyRowStyles();
                        updateSummaryBar();
                    }, 50);
                }
            });

            injectHeaderCheckbox();
            applyRowStyles();
            updateSummaryBar();

            $('#summary-bar').removeClass('d-none');
            $('#grid-actions-footer').removeClass('d-none').addClass('d-flex');
        }

        function injectHeaderCheckbox() {
            const container = document.getElementById('rent-invoice-grid');
            if (!container) return;
            const headerTd = container.querySelector('.jexcel thead td:nth-child(2)');
            if (headerTd && !headerTd.querySelector('#header-select-all')) {
                headerTd.innerHTML = '<input type="checkbox" id="header-select-all" title="Select / Deselect All Pending">';
            }
        }

        function recalculateRow(y) {
            if (!grid) return;
            const subtotalVal = parseFloat(grid.getValueFromCoords(COL.SUBTOTAL, y)) || 0;
            let discountVal = parseFloat(grid.getValueFromCoords(COL.DISCOUNT, y)) || 0;

            if (discountVal < 0) discountVal = 0;
            if (discountVal > subtotalVal) discountVal = subtotalVal;

            const totalVal = Math.max(0, subtotalVal - discountVal);
            grid.setValueFromCoords(COL.TOTAL, y, totalVal.toFixed(2), true);
            updateSummaryBar();
        }

        function applyRowStyles() {
            if (!grid || !rowMeta) return;

            const container = document.getElementById('rent-invoice-grid');
            const rows = container ? container.querySelectorAll('.jexcel > tbody > tr') : [];

            rowMeta.forEach(function(meta, i) {
                if (meta.status !== 'pending') {
                    if (grid.options && grid.options.data && grid.options.data[i]) {
                        grid.options.data[i][COL.SELECT] = false;
                    }

                    if (rows && rows[i]) {
                        const chk = rows[i].querySelector('td input[type="checkbox"]');
                        if (chk) {
                            chk.checked = false;
                            chk.disabled = true;
                        }
                    }

                    const bg = (meta.status === 'already_invoiced') ? '#f1f3f5' : '#fff9db';
                    const fg = (meta.status === 'already_invoiced') ? '#adb5bd' : '#d9480f';

                    for (let c = 0; c < 19; c++) {
                        const cellName = jspreadsheet.getColumnNameFromId([c, i]);
                        grid.setStyle(cellName, 'background-color', bg);
                        grid.setStyle(cellName, 'color', fg);
                    }
                }
            });
        }

        function updateSummaryBar() {
            if (!grid) return;
            let selectedCount = 0;
            let selectedTotal = 0;
            let pendingTotalCount = 0;
            let pendingCheckedCount = 0;

            const container = document.getElementById('rent-invoice-grid');
            const rows = container ? container.querySelectorAll('.jexcel > tbody > tr') : [];

            rows.forEach(function(tr, i) {
                if (!rowMeta[i]) return;

                const chk = tr.querySelector('td input[type="checkbox"]');

                if (rowMeta[i].status !== 'pending') {
                    if (chk) {
                        chk.checked = false;
                        chk.disabled = true;
                    }
                    return;
                }

                pendingTotalCount++;
                const isChecked = chk ? chk.checked : (grid.options.data[i] && (grid.options.data[i][COL.SELECT] === true || grid.options.data[i][COL.SELECT] === 'true' || grid.options.data[i][COL.SELECT] == 1));

                if (isChecked) {
                    selectedCount++;
                    pendingCheckedCount++;
                    const totalVal = parseFloat(grid.getValueFromCoords(COL.TOTAL, i)) || 0;
                    selectedTotal += totalVal;
                }
            });

            // Update Summary Text
            $('#summary-selected-count').text(selectedCount);
            $('#summary-selected-amount').text('৳ ' + selectedTotal.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));

            // Sync Header Checkbox State
            const headerChk = document.getElementById('header-select-all');
            if (headerChk) {
                if (pendingTotalCount > 0 && pendingCheckedCount === pendingTotalCount) {
                    headerChk.checked = true;
                    headerChk.indeterminate = false;
                } else if (pendingCheckedCount > 0) {
                    headerChk.checked = false;
                    headerChk.indeterminate = true;
                } else {
                    headerChk.checked = false;
                    headerChk.indeterminate = false;
                }
            }

            const btn = $('#btn-generate-invoices');
            btn.html('<i class="fa fa-paper-plane me-1"></i> Generate ' + selectedCount + ' Invoice(s)');
            btn.prop('disabled', selectedCount === 0);
        }

        function setRowSelected(i, isSelected) {
            if (!grid || !rowMeta[i] || rowMeta[i].status !== 'pending') return;

            if (grid.options && grid.options.data && grid.options.data[i]) {
                grid.options.data[i][COL.SELECT] = isSelected;
            }

            try {
                const container = document.getElementById('rent-invoice-grid');
                const rows = container ? container.querySelectorAll('.jexcel > tbody > tr') : [];
                if (rows && rows[i]) {
                    const chk = rows[i].querySelector('td input[type="checkbox"]');
                    if (chk && !chk.disabled) {
                        chk.checked = isSelected;
                    }
                }
            } catch(e) {}
        }

        $('#btn-load-agreements').on('click', function() {
            const m = $('#select_month').val();
            const y = $('#select_year').val();
            currentBillingMonth = y + '-' + m;

            const $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Loading...');

            $.ajax({
                url: '{{ route("invoices.rent.bulk-generate.preview") }}',
                type: 'POST',
                data: {
                    billing_month: currentBillingMonth,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    $btn.prop('disabled', false).html('<i class="fa fa-search me-1"></i> Load Agreements');
                    rowMeta = res.meta;

                    $('#summary-total').text(res.summary.total);
                    $('#summary-already-count').text(res.summary.already_invoiced);

                    if (res.summary.missing_vendor > 0) {
                        $('#summary-missing-count').text(res.summary.missing_vendor);
                        $('#summary-missing-wrapper').removeClass('d-none');
                    } else {
                        $('#summary-missing-wrapper').addClass('d-none');
                    }

                    initGrid(res.data);
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html('<i class="fa fa-search me-1"></i> Load Agreements');
                    const err = xhr.responseJSON?.message || 'Failed to load agreements.';
                    showAlert('Error Loading Agreements', err, 'danger');
                }
            });
        });

        $('#btn-select-all').on('click', function() {
            if (!grid || !rowMeta) return;
            rowMeta.forEach(function(meta, i) {
                if (meta.status === 'pending') {
                    setRowSelected(i, true);
                }
            });
            updateSummaryBar();
        });

        $('#btn-deselect-all').on('click', function() {
            if (!grid || !rowMeta) return;
            rowMeta.forEach(function(meta, i) {
                if (meta.status === 'pending') {
                    setRowSelected(i, false);
                }
            });
            updateSummaryBar();
        });

        // Header Checkbox Click / Toggle Handler
        $(document).on('change', '#header-select-all', function() {
            const isChecked = $(this).is(':checked');
            if (!rowMeta || rowMeta.length === 0) return;

            rowMeta.forEach(function(meta, i) {
                if (meta.status === 'pending') {
                    setRowSelected(i, isChecked);
                }
            });
            updateSummaryBar();
        });

        // Prevent checking unselectable row checkboxes on individual click
        $(document).on('click', '.jexcel > tbody > tr > td input[type="checkbox"]', function(e) {
            const tr = $(this).closest('tr');
            const rowIndex = tr.index();

            if (rowMeta[rowIndex] && rowMeta[rowIndex].status !== 'pending') {
                e.preventDefault();
                e.stopPropagation();
                this.checked = false;
                this.disabled = true;
                return false;
            }
            updateSummaryBar();
        });

        // Delegate click for Details button in grid
        $(document).on('click', '.btn-rent-details', function(e) {
            e.preventDefault();
            const rentId = $(this).data('rent-id');
            const modalEl = document.getElementById('modal-rent-details');
            const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);

            $('#modal-rent-details-body').html(`
                <div class="text-center py-4 text-muted">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2 mb-0">Loading breakdown details...</p>
                </div>
            `);

            bsModal.show();

            $.get('{{ url("invoices/rent/rent-breakdown-modal") }}/' + rentId)
                .done(function(html) {
                    $('#modal-rent-details-body').html(html);
                })
                .fail(function() {
                    $('#modal-rent-details-body').html(`
                        <div class="alert alert-danger mb-0">
                            <i class="fa fa-exclamation-triangle me-1"></i> Failed to load rent breakdown details.
                        </div>
                    `);
                });
        });

        function submitBulkGenerate(payloadRows) {
            const $btn = $('#btn-generate-invoices');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Generating...');

            $.ajax({
                url: '{{ route("invoices.rent.bulk-generate.store") }}',
                type: 'POST',
                data: {
                    billing_month: currentBillingMonth,
                    invoices: payloadRows,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    showAlert('Invoices Generated', res.message, 'success', function() {
                        window.location.href = res.redirect || "{{ route('invoices.rent.index') }}";
                    });
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i> Generate Invoices');
                    const err = xhr.responseJSON?.message || 'Invoice generation failed.';
                    showAlert('Generation Failed', err, 'danger');
                }
            });
        }

        $('#btn-generate-invoices').on('click', function() {
            if (!grid) return;
            const payloadRows = [];

            const container = document.getElementById('rent-invoice-grid');
            const rows = container ? container.querySelectorAll('.jexcel > tbody > tr') : [];

            rows.forEach(function(tr, i) {
                if (!rowMeta[i] || rowMeta[i].status !== 'pending') return;

                const chk = tr.querySelector('td input[type="checkbox"]');
                const isChecked = chk ? chk.checked : (grid.options.data[i] && (grid.options.data[i][COL.SELECT] === true || grid.options.data[i][COL.SELECT] === 'true' || grid.options.data[i][COL.SELECT] == 1));

                if (isChecked) {
                    const rowData = grid.getData()[i];
                    payloadRows.push({
                        rent_base_id: rowMeta[i].rent_base_id,
                        discount:     parseFloat(rowData[COL.DISCOUNT]) || 0,
                        invoice_date: rowData[COL.INV_DATE],
                        due_date:     rowData[COL.DUE_DATE] || null,
                        remarks:      rowData[COL.REMARKS] || null,
                    });
                }
            });

            if (payloadRows.length === 0) {
                showAlert('No Pending Selection', 'Please select at least one pending invoice to generate.', 'warning');
                return;
            }

            const confirmMsg = 'Are you sure you want to generate <strong>' + payloadRows.length + '</strong> rent requisition invoice(s) for billing month <strong>' + currentBillingMonth + '</strong>?';

            showConfirm(
                'Confirm Invoice Generation',
                confirmMsg,
                function() {
                    submitBulkGenerate(payloadRows);
                }
            );
        });

        // Trigger load on page load
        $(document).ready(function() {
            $('#btn-load-agreements').trigger('click');
        });
    })();
    </script>
@endsection
