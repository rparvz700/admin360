@extends('Partials.app', ['activeMenu' => 'agreements'])

@section('title')
    {{ config('app.name') }} - Agreement Details: {{ $agreement->agreement_ref_no }}
@endsection

@section('page_title')
    Agreement Details <small class="text-muted">({{ $agreement->agreement_ref_no }})</small>
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Agreement: <span class="fw-bold">{{ $agreement->agreement_ref_no }}</span></h3>
                <div class="block-options">
                    {{-- Assuming an 'edit' route for agreements --}}
                    <a href="{{ route('agreements.edit', $agreement) }}" class="btn btn-sm btn-primary me-2">
                        <i class="fa fa-pencil-alt me-1"></i> Edit Agreement
                    </a>
                    <a href="{{ route('agreements.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="block-content">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs nav-tabs-alt" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-pane"
                            type="button" role="tab" aria-controls="details-pane" aria-selected="true">
                            <i class="fa fa-info-circle me-1"></i> Details
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-pane"
                            type="button" role="tab" aria-controls="history-pane" aria-selected="false"
                            data-agreement-id="{{ $agreement->id }}">
                            <i class="fa fa-history me-1"></i> History
                        </button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content pb-4">
                    <!-- Details Tab Pane -->
                    <div class="tab-pane fade show active" id="details-pane" role="tabpanel" aria-labelledby="details-tab"
                        tabindex="0">
                        <h4 class="fw-light mt-4 mb-3">Agreement Information</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-striped table-bordered fs-sm">
                                    <tbody>
                                        <tr>
                                            <th>Reference No</th>
                                            <td>{{ $agreement->agreement_ref_no ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Vendor</th>
                                            <td>{{ $agreement->vendor->name ?? 'N/A' }} {{ $agreement->vendor ? '(' . $agreement->vendor->vendor_code . ')' : '' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Agreement Date</th>
                                            <td>{{ $agreement->agreement_date ? \Carbon\Carbon::parse($agreement->agreement_date)->format('Y-m-d') : 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Payment Start Date</th>
                                            <td>{{ ($agreement->payment_start_date ?? $agreement->from_date) ? \Carbon\Carbon::parse($agreement->payment_start_date ?? $agreement->from_date)->format('Y-m-d') : 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Expiry Date</th>
                                            <td>{{ ($agreement->expiry_date ?? $agreement->to_date) ? \Carbon\Carbon::parse($agreement->expiry_date ?? $agreement->to_date)->format('Y-m-d') : 'N/A' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-striped table-bordered fs-sm">
                                    <tbody>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                @if ($agreement->status == 1)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Remarks</th>
                                            <td>{{ $agreement->remarks ?? 'N/A' }}</td>
                                        </tr>
                                        {{-- Add any other relevant details here --}}
                                        {{-- Example: Creator or Last Updater if available --}}
                                        {{-- <tr><th>Created By</th><td>{{ $agreement->creator->name ?? 'System' }}</td></tr> --}}
                                        {{-- <tr><th>Created At</th><td>{{ $agreement->created_at ? \Carbon\Carbon::parse($agreement->created_at)->format('Y-m-d H:i:s') : 'N/A' }}</td></tr> --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- NEW SECTION FOR ATTACHED DOCUMENTS --}}
                        <h4 class="fw-light mt-4 mb-3">Attached Documents</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-vcenter fs-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;">ID</th>
                                        <th style="width: 25%;">File Name</th>
                                        <th style="width: 15%;">Category</th>
                                        <th style="width: 15%;">Issue Date</th>
                                        <th style="width: 15%;">Expiry Date</th>
                                        <th style="width: 15%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($agreement->documents as $doc)
                                        <tr>
                                            <td>{{ $doc->id }}</td>
                                            <td>
                                                {{-- Assuming file_path stores the path like 'documents/unique_hash.pdf' --}}
                                                {{-- We use pathinfo to get the base filename, or you might store original_file_name --}}
                                                {{ pathinfo($doc->file_path, PATHINFO_BASENAME) ?? 'N/A' }}
                                            </td>
                                            <td>{{ $doc->category->category_name ?? 'N/A' }}</td>
                                            <td>{{ $doc->issue_date ? \Carbon\Carbon::parse($doc->issue_date)->format('Y-m-d') : 'N/A' }}
                                            </td>
                                            <td>{{ $doc->expiry_date ? \Carbon\Carbon::parse($doc->expiry_date)->format('Y-m-d') : 'N/A' }}
                                            </td>
                                            <td>
                                                {{-- Assuming documents are stored in public storage and accessible via Storage::url() --}}
                                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank"
                                                    class="btn btn-sm btn-alt-info" data-bs-toggle="tooltip"
                                                    title="View/Download Document">
                                                    <i class="fa fa-file-download me-1"></i> View
                                                </a>
                                                {{-- You could add an edit button here that opens a modal for editing the document --}}
                                                <a href="{{ route('generic-documents.edit', $doc->id) }}"
                                                    class="btn btn-sm btn-alt-secondary ms-1"><i
                                                        class="fa fa-pencil-alt"></i> Edit</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-3">No documents attached to
                                                this agreement.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{-- END NEW SECTION --}}
                    </div>

                    <!-- History Tab Pane -->
                    <div class="tab-pane fade" id="history-pane" role="tabpanel" aria-labelledby="history-tab"
                        tabindex="0">
                        <h4 class="fw-light mt-4 mb-3">Agreement History Log</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-vcenter fs-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 15%;">Date</th>
                                        <th style="width: 15%;">User</th>
                                        <th style="width: 20%;">Field</th>
                                        <th style="width: 25%;">Old Value</th>
                                        <th style="width: 25%;">New Value</th>
                                    </tr>
                                </thead>
                                <tbody id="history-items-body">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <div class="spinner-border text-primary spinner-border-sm me-2" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div> Loading history...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script>
        jQuery(function() {
            const agreementId = {{ $agreement->id }};
            const historyTabButton = jQuery('#history-tab');
            const historyItemsBody = jQuery('#history-items-body');

            function loadAgreementHistory() {
                historyItemsBody.html(`
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <div class="spinner-border text-primary spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div> Loading history...
                    </td>
                </tr>
            `);

                jQuery.ajax({
                    url: `/agreements/${agreementId}/history`, // IMPORTANT: Ensure this route exists and is properly implemented in your backend
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        let html = '';
                        if (data && data.length > 0) {
                            data.forEach(log => {
                                const userName = log.user ? (log.user.name || log.user) :
                                    'System';
                                const logDate = log.date ? new Date(log.date).toLocaleString() :
                                    'N/A';

                                if (log.changes && log.changes.length > 0) {
                                    log.changes.forEach(change => {
                                        const formatValue = (value) => {
                                            if (value === null)
                                                return '<em class="text-muted">(null)</em>';
                                            if (value === '')
                                                return '<em class="text-muted">(empty)</em>';
                                            // Specific handling for boolean status if needed, though spatie converts to 0/1
                                            if (change.field === 'Status') {
                                                return value === 1 || value ===
                                                    '1' ? 'Active' : 'Inactive';
                                            }
                                            return value;
                                        };

                                        html += `<tr>
                                        <td>${logDate}</td>
                                        <td>${userName}</td>
                                        <td><strong>${change.field || 'N/A'}</strong></td>
                                        <td class="text-danger">${formatValue(change.from)}</td>
                                        <td class="text-success">${formatValue(change.to)}</td>
                                    </tr>`;
                                    });
                                } else {
                                    html += `<tr>
                                    <td>${logDate}</td>
                                    <td>${userName}</td>
                                    <td colspan="3" class="text-center text-muted">No specific changes recorded for this entry.</td>
                                </tr>`;
                                }
                            });
                        } else {
                            html =
                                '<tr><td colspan="5" class="text-center text-muted py-4">No history found for this agreement.</td></tr>';
                        }
                        historyItemsBody.html(html);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Error loading agreement history:", textStatus, errorThrown,
                            jqXHR);
                        historyItemsBody.html(`
                        <tr>
                            <td colspan="5" class="text-center text-danger py-4">
                                <i class="fa fa-exclamation-triangle me-1"></i> Failed to load history. Please try again.
                                <small class="d-block mt-1">${jqXHR.responseJSON ? jqXHR.responseJSON.message : errorThrown}</small>
                            </td>
                        </tr>
                    `);
                    }
                });
            }

            historyTabButton.on('show.bs.tab', function() {
                if (!historyTabButton.data('history-loaded')) {
                    loadAgreementHistory();
                    historyTabButton.data('history-loaded', true);
                }
            });
        });
    </script>
@endsection
