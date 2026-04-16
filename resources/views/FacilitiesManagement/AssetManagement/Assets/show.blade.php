@extends('Partials.app', ['activeMenu' => 'assets'])

@section('title')
    {{ config('app.name') }} - Asset Details: {{ $asset->asset_tag }}
@endsection

@section('page_title')
    Asset Details <small class="text-muted">({{ $asset->asset_tag }})</small>
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Asset: <span class="fw-bold">{{ $asset->asset_name }}</span></h3>
                <div class="block-options">
                    {{-- Assuming an 'edit' route for assets --}}
                    <a href="{{ route('assets.edit', $asset) }}" class="btn btn-sm btn-primary me-2">
                        <i class="fa fa-pencil-alt me-1"></i> Edit Asset
                    </a>
                    <a href="{{ route('assets.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="block-content">
                <!-- Tabs Navigation -->
                {{-- Using Bootstrap's nav-tabs-alt if your theme supports it for a slightly different style --}}
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
                            data-asset-id="{{ $asset->id }}">
                            <i class="fa fa-history me-1"></i> History
                        </button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content pb-4"> {{-- Added padding-bottom for content below tabs --}}
                    <!-- Details Tab Pane -->
                    <div class="tab-pane fade show active" id="details-pane" role="tabpanel" aria-labelledby="details-tab"
                        tabindex="0">
                        <h4 class="fw-light mt-4 mb-3">Asset Information</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-striped table-bordered fs-sm"> {{-- table-striped for better readability, fs-sm for smaller font --}}
                                    <tbody>
                                        <tr>
                                            <th>Asset Tag</th>
                                            <td>{{ $asset->asset_tag ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Asset Name</th>
                                            <td>{{ $asset->asset_name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Category</th>
                                            <td>{{ $asset->category ? $asset->category->category_name : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Brand</th>
                                            <td>{{ $asset->brand ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Model</th>
                                            <td>{{ $asset->model ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Serial Number</th>
                                            <td>{{ $asset->serial_number ?? 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-striped table-bordered fs-sm">
                                    <tbody>
                                        <tr>
                                            <th>Purchase Date</th>
                                            <td>{{ $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->format('Y-m-d') : 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Warranty Expiry</th>
                                            <td>{{ $asset->warranty_expiry ? \Carbon\Carbon::parse($asset->warranty_expiry)->format('Y-m-d') : 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Floor</th>
                                            <td>
                                                {{-- Fixed floor display logic: handle null building and floor --}}
                                                @if ($asset->floor)
                                                    {{ $asset->floor->building ? $asset->floor->building->site_name . ', ' : '' }}
                                                    {{ $asset->floor->floor_label }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Location within Floor</th>
                                            <td>{{ $asset->location_within_floor ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Parent Asset</th>
                                            <td>
                                                {{-- Fixed parent display logic --}}
                                                @if ($asset->parent)
                                                    <a href="{{ route('assets.show', $asset->parent) }}">{{ $asset->parent->asset_tag }}
                                                        - {{ $asset->parent->asset_name }}</a>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                        {{-- Assuming 'status' and 'status_color' exist on the Asset model --}}
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                <span class="badge {{ $asset->status_color ?? 'bg-secondary' }}">
                                                    {{ $asset->status ?? 'N/A' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Custom Fields Section --}}
                        @if ($asset->attributeValues->isNotEmpty())
                            <h4 class="fw-light mt-4 mb-3">Custom Fields</h4>
                            <div class="table-responsive"> {{-- Make custom fields table responsive --}}
                                <table class="table table-striped table-bordered table-vcenter fs-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30%;">Field Name</th>
                                            <th style="width: 20%;">Type</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($asset->attributeValues as $attrValue)
                                            <tr>
                                                <td>{{ $attrValue->attribute->attribute_name ?? 'N/A' }}</td>
                                                <td>{{ $attrValue->attribute->attribute_type ?? 'N/A' }}</td>
                                                <td>
                                                    @if (($attrValue->attribute->attribute_type ?? '') === 'boolean')
                                                        {{ $attrValue->value == '1' ? 'Yes' : 'No' }}
                                                    @else
                                                        {{ $attrValue->value ?? 'N/A' }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info mt-4">
                                <i class="fa fa-info-circle me-1"></i> No custom fields defined for this asset.
                            </div>
                        @endif

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
                                    @forelse ($asset->documents as $doc)
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
                        <h4 class="fw-light mt-4 mb-3">Asset History Log</h4>
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
        // Ensure jQuery is loaded and available
        jQuery(function() {
            const assetId = {{ $asset->id }};
            const historyTabButton = jQuery('#history-tab');
            const historyItemsBody = jQuery('#history-items-body');

            // Function to load history data via AJAX
            function loadAssetHistory() {
                // Display loading state
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
                    url: `/assets/${assetId}/history`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        let html = '';
                        if (data && data.length > 0) {
                            data.forEach(log => {
                                // Assuming each log entry has a 'changes' array
                                // and that log.date and log.user are available.
                                // log.user could be an object {id: 1, name: 'John Doe'} or just a string 'John Doe'
                                const userName = log.user ? (log.user.name || log.user) :
                                    'System';
                                const logDate = log.date ? new Date(log.date).toLocaleString() :
                                    'N/A';

                                if (log.changes && log.changes.length > 0) {
                                    log.changes.forEach(change => {
                                        // Helper function to format null/empty values
                                        const formatValue = (value) => {
                                            if (value === null)
                                                return '<em class="text-muted">(null)</em>';
                                            if (value === '')
                                                return '<em class="text-muted">(empty)</em>';
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
                                    // If a log entry exists but has no specific changes recorded
                                    html += `<tr>
                                    <td>${logDate}</td>
                                    <td>${userName}</td>
                                    <td colspan="3" class="text-center text-muted">No specific changes recorded for this entry.</td>
                                </tr>`;
                                }
                            });
                        } else {
                            html =
                                '<tr><td colspan="5" class="text-center text-muted py-4">No history found for this asset.</td></tr>';
                        }
                        historyItemsBody.html(html);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Error loading asset history:", textStatus, errorThrown, jqXHR);
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

            // Event listener for when the history tab is shown
            historyTabButton.on('show.bs.tab', function() {
                // Prevent multiple AJAX calls if the history has already been loaded
                if (!historyTabButton.data('history-loaded')) {
                    loadAssetHistory();
                    historyTabButton.data('history-loaded', true); // Mark as loaded
                }
            });

            // Optional: If you want to force reload history every time the tab is clicked,
            // remove the 'history-loaded' check and simply call loadAssetHistory()
            // inside the 'show.bs.tab' event handler.
        });
    </script>
@endsection
