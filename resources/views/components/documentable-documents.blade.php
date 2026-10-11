@php
    $docsList = $documents ?? collect();
    $totalCount = $docsList->count();
    $today = \Carbon\Carbon::today();

    $expiredCount = 0;
    $expiringSoonCount = 0;
    $validCount = 0;
    $noExpiryCount = 0;

    foreach ($docsList as $doc) {
        if ($doc->expiry_date) {
            $expDate = \Carbon\Carbon::parse($doc->expiry_date)->startOfDay();
            $diffDays = $today->diffInDays($expDate, false);
            if ($diffDays < 0) {
                $expiredCount++;
            } elseif ($diffDays <= 30) {
                $expiringSoonCount++;
            } else {
                $validCount++;
            }
        } else {
            $noExpiryCount++;
        }
    }

    $currentUrl = url()->current();
    $entityLabel = $documentableType === 'vehicle' 
        ? ($model->registration_number ?? 'Vehicle #' . $documentableId) 
        : ($model->name ?? 'Driver #' . $documentableId);
@endphp

<div class="block block-rounded block-bordered shadow-sm mb-4" id="attached-documents-section">
    <!-- Block Header -->
    <div class="block-header block-header-default bg-body-light py-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h3 class="block-title fs-sm fw-bold text-uppercase mb-0">
                <i class="fa fa-folder-open me-1 text-primary"></i> {{ $title ?? 'Attached Documents & Compliance' }}
            </h3>
            <span class="badge bg-secondary font-monospace">{{ $totalCount }} {{ Str::plural('Document', $totalCount) }}</span>
            @if ($validCount > 0)
                <span class="badge bg-success" title="Valid Documents">
                    <i class="fa fa-check-circle me-1"></i> {{ $validCount }} Valid
                </span>
            @endif
            @if ($expiringSoonCount > 0)
                <span class="badge bg-warning text-dark" title="Expiring within 30 days">
                    <i class="fa fa-clock me-1"></i> {{ $expiringSoonCount }} Expiring Soon
                </span>
            @endif
            @if ($expiredCount > 0)
                <span class="badge bg-danger" title="Expired Documents">
                    <i class="fa fa-exclamation-triangle me-1"></i> {{ $expiredCount }} Expired
                </span>
            @endif
            @if ($noExpiryCount > 0 && $totalCount > $noExpiryCount)
                <span class="badge bg-info-light text-info" title="Lifetime / No Expiry Date">
                    {{ $noExpiryCount }} No Expiry
                </span>
            @endif
        </div>
        <div class="block-options">
            @can('create-generic-document')
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                    <i class="fa fa-plus me-1"></i> Add Document
                </button>
            @else
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                    <i class="fa fa-plus me-1"></i> Add Document
                </button>
            @endcan
        </div>
    </div>

    <!-- Block Content -->
    <div class="block-content p-0">
        @if ($totalCount === 0)
            <!-- Empty State -->
            <div class="text-center py-5 px-3">
                <div class="item item-2x item-circle bg-body-light text-muted mx-auto mb-3">
                    <i class="fa fa-file-invoice fs-2"></i>
                </div>
                <h5 class="fw-bold mb-1">No Documents Tagged Yet</h5>
                <p class="text-muted fs-sm mb-3 mx-auto" style="max-width: 540px;">
                    No regulatory, compliance, or verification documents have been attached to this {{ ucfirst($documentableType) }}.
                    You can tag documents such as licenses, permits, fitness certificates, tokens, or registration papers with their issue and expiry dates.
                </p>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                    <i class="fa fa-plus me-1"></i> Attach First Document
                </button>
            </div>
        @else
            <!-- Documents Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover table-vcenter fs-sm mb-0 align-middle">
                    <thead class="table-light text-uppercase fs-xs">
                        <tr>
                            <th style="width: 60px;" class="text-center">#</th>
                            <th style="width: 18%;">Category / Type</th>
                            <th style="width: 14%;">Issue Date</th>
                            <th style="width: 18%;">Expiry Date & Status</th>
                            <th style="width: 26%;">Other Attributes (Custom Fields)</th>
                            <th style="width: 12%;">Attachment</th>
                            <th style="width: 12%;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($docsList as $doc)
                            @php
                                $ext = $doc->file_path ? strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION)) : null;
                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                $isPdf = $ext === 'pdf';
                                $fileUrl = $doc->file_path ? asset('storage/' . $doc->file_path) : null;

                                $hasExpiry = !empty($doc->expiry_date);
                                $isExpired = false;
                                $isExpiringSoon = false;
                                $diffDays = 0;

                                if ($hasExpiry) {
                                    $expDate = \Carbon\Carbon::parse($doc->expiry_date)->startOfDay();
                                    $diffDays = $today->diffInDays($expDate, false);
                                    if ($diffDays < 0) {
                                        $isExpired = true;
                                    } elseif ($diffDays <= 30) {
                                        $isExpiringSoon = true;
                                    }
                                }
                            @endphp
                            <tr>
                                <!-- ID -->
                                <td class="text-center font-monospace fw-semibold text-muted">
                                    #{{ $doc->id }}
                                </td>

                                <!-- Category -->
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="item item-circle bg-primary-light text-primary flex-shrink-0" style="width: 32px; height: 32px;">
                                            <i class="fa fa-file-alt fs-sm"></i>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0)" class="fw-bold text-dark doc-details-trigger" 
                                               data-bs-toggle="modal" data-bs-target="#docDetailModal-{{ $doc->id }}">
                                                {{ $doc->category->category_name ?? 'General Document' }}
                                            </a>
                                            @if(!empty($doc->category->description))
                                                <div class="text-muted fs-xs text-truncate" style="max-width: 180px;" title="{{ $doc->category->description }}">
                                                    {{ $doc->category->description }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Issue Date -->
                                <td>
                                    @if ($doc->issue_date)
                                        <div class="fw-semibold text-dark">
                                            <i class="fa fa-calendar-alt text-muted me-1"></i>
                                            {{ \Carbon\Carbon::parse($doc->issue_date)->format('d M, Y') }}
                                        </div>
                                        <div class="text-muted fs-xs">
                                            {{ \Carbon\Carbon::parse($doc->issue_date)->diffForHumans() }}
                                        </div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>

                                <!-- Expiry Date & Status -->
                                <td>
                                    @if ($hasExpiry)
                                        <div class="fw-semibold text-dark mb-1">
                                            <i class="fa fa-calendar-check text-muted me-1"></i>
                                            {{ \Carbon\Carbon::parse($doc->expiry_date)->format('d M, Y') }}
                                        </div>
                                        @if ($isExpired)
                                            <span class="badge bg-danger" title="Expired on {{ $doc->expiry_date }}">
                                                <i class="fa fa-times-circle me-1"></i> Expired ({{ abs($diffDays) }}d ago)
                                            </span>
                                        @elseif ($diffDays === 0)
                                            <span class="badge bg-warning text-dark">
                                                <i class="fa fa-exclamation-circle me-1"></i> Expires Today
                                            </span>
                                        @elseif ($isExpiringSoon)
                                            <span class="badge bg-warning text-dark" title="Expiring within 30 days">
                                                <i class="fa fa-clock me-1"></i> Expiring (in {{ $diffDays }}d)
                                            </span>
                                        @else
                                            <span class="badge bg-success" title="Valid until {{ $doc->expiry_date }}">
                                                <i class="fa fa-check-circle me-1"></i> Valid (in {{ $diffDays }}d)
                                            </span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fa fa-infinity me-1"></i> Lifetime / No Expiry
                                        </span>
                                    @endif
                                </td>

                                <!-- Custom Attributes ("other attributes") -->
                                <td>
                                    @if ($doc->attributeValues && $doc->attributeValues->count() > 0)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($doc->attributeValues as $attrVal)
                                                @php
                                                    $raw = $attrVal->value;
                                                    $valDisplay = $raw;
                                                    if (($attrVal->attribute->attribute_type ?? '') === 'boolean') {
                                                        $valDisplay = ($raw === '1' || $raw === 1 || $raw === true) ? 'Yes' : 'No';
                                                    } elseif (is_string($raw) && str_starts_with(trim($raw), '[') && str_ends_with(trim($raw), ']')) {
                                                        $decoded = json_decode($raw, true);
                                                        if (is_array($decoded)) {
                                                            $valDisplay = implode(', ', $decoded);
                                                        }
                                                    }
                                                @endphp
                                                <span class="badge bg-light text-dark border fs-xs py-1 px-2 mb-1" 
                                                      title="Type: {{ $attrVal->attribute->attribute_type ?? 'Text' }}">
                                                    <span class="text-muted fw-normal">{{ $attrVal->attribute->attribute_name ?? 'Field' }}:</span>
                                                    <strong class="text-primary">{{ $valDisplay ?? '-' }}</strong>
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted fs-xs fst-italic">No custom attributes recorded</span>
                                    @endif
                                </td>

                                <!-- Attachment / File -->
                                <td>
                                    @if ($doc->file_path)
                                        <div class="d-flex align-items-center gap-1">
                                            @if ($isImage)
                                                <button type="button" class="btn btn-sm btn-alt-primary doc-preview-trigger px-2"
                                                        data-url="{{ $fileUrl }}"
                                                        data-type="image"
                                                        data-title="{{ $doc->category->category_name ?? 'Document File' }}"
                                                        title="Click to preview image">
                                                    <i class="fa fa-image me-1"></i> Image
                                                </button>
                                            @elseif ($isPdf)
                                                <button type="button" class="btn btn-sm btn-alt-info doc-preview-trigger px-2"
                                                        data-url="{{ $fileUrl }}"
                                                        data-type="pdf"
                                                        data-title="{{ $doc->category->category_name ?? 'Document File' }}"
                                                        title="Click to view PDF in modal">
                                                    <i class="fa fa-file-pdf me-1"></i> PDF
                                                </button>
                                            @else
                                                <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-alt-secondary px-2" download title="Download File">
                                                    <i class="fa fa-file-download me-1"></i> File
                                                </a>
                                            @endif
                                            <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-alt-secondary px-2" download title="Direct Download">
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-muted border">No file</span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- View Details in Modal -->
                                        <button type="button" class="btn btn-alt-primary" data-bs-toggle="modal" 
                                                data-bs-target="#docDetailModal-{{ $doc->id }}" title="View Full Details">
                                            <i class="fa fa-eye"></i>
                                        </button>

                                        <!-- Edit Document -->
                                        @can('edit-generic-document')
                                            <a href="{{ route('generic-documents.edit', ['generic_document' => $doc->id, 'return_url' => $currentUrl]) }}" 
                                               class="btn btn-alt-warning" title="Edit Document & Attributes">
                                                <i class="fa fa-pencil-alt"></i>
                                            </a>
                                        @endcan

                                        <!-- Delete Document -->
                                        @can('delete-generic-document')
                                            <form action="{{ route('generic-documents.destroy', $doc->id) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this document (#{{ $doc->id }})? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="return_url" value="{{ $currentUrl }}">
                                                <button type="submit" class="btn btn-alt-danger" title="Delete Document">
                                                    <i class="fa fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- ========================================== -->
<!-- 1. FULL DETAILS MODALS (FOR EACH DOCUMENT) -->
<!-- ========================================== -->
@foreach ($docsList as $doc)
    @php
        $ext = $doc->file_path ? strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION)) : null;
        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        $isPdf = $ext === 'pdf';
        $fileUrl = $doc->file_path ? asset('storage/' . $doc->file_path) : null;

        $hasExpiry = !empty($doc->expiry_date);
        $diffDays = $hasExpiry ? $today->diffInDays(\Carbon\Carbon::parse($doc->expiry_date)->startOfDay(), false) : null;
    @endphp
    <div class="modal fade" id="docDetailModal-{{ $doc->id }}" tabindex="-1" aria-labelledby="docDetailModalLabel-{{ $doc->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-body-light">
                    <h5 class="modal-title fs-base fw-bold" id="docDetailModalLabel-{{ $doc->id }}">
                        <i class="fa fa-file-invoice text-primary me-2"></i>
                        Document Breakdown: {{ $doc->category->category_name ?? 'Document' }} 
                        <span class="badge bg-secondary font-monospace ms-1">#{{ $doc->id }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Basic Document Metadata Table -->
                    <h6 class="fw-bold text-uppercase fs-xs text-muted mb-2">
                        <i class="fa fa-info-circle me-1 text-primary"></i> 1. Core Document Attributes
                    </h6>
                    <table class="table table-bordered table-striped fs-sm mb-4">
                        <tbody>
                            <tr>
                                <th style="width: 35%;" class="bg-light">Associated Entity</th>
                                <td>
                                    <span class="badge bg-primary-light text-primary fw-bold text-uppercase">{{ $documentableType }}</span>
                                    <span class="fw-semibold ms-1">{{ $entityLabel }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Document Category</th>
                                <td>
                                    <strong>{{ $doc->category->category_name ?? 'General' }}</strong>
                                    @if(!empty($doc->category->description))
                                        <div class="text-muted fs-xs mt-1">{{ $doc->category->description }}</div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Issue Date</th>
                                <td>
                                    @if ($doc->issue_date)
                                        <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($doc->issue_date)->format('d M, Y') }}</span>
                                        <span class="text-muted fs-xs ms-1">({{ \Carbon\Carbon::parse($doc->issue_date)->diffForHumans() }})</span>
                                    @else
                                        <span class="text-muted">Not Recorded</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Expiry Date</th>
                                <td>
                                    @if ($hasExpiry)
                                        <span class="fw-semibold text-dark me-2">{{ \Carbon\Carbon::parse($doc->expiry_date)->format('d M, Y') }}</span>
                                        @if ($diffDays < 0)
                                            <span class="badge bg-danger"><i class="fa fa-times-circle me-1"></i> Expired ({{ abs($diffDays) }} days ago)</span>
                                        @elseif ($diffDays === 0)
                                            <span class="badge bg-warning text-dark"><i class="fa fa-exclamation-circle me-1"></i> Expires Today</span>
                                        @elseif ($diffDays <= 30)
                                            <span class="badge bg-warning text-dark"><i class="fa fa-clock me-1"></i> Expiring Soon (in {{ $diffDays }} days)</span>
                                        @else
                                            <span class="badge bg-success"><i class="fa fa-check-circle me-1"></i> Valid (in {{ $diffDays }} days)</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary"><i class="fa fa-infinity me-1"></i> Lifetime / No Expiry Date</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Attachment File</th>
                                <td>
                                    @if ($doc->file_path)
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-dark font-monospace text-uppercase">{{ $ext }}</span>
                                            <span class="text-truncate" style="max-width: 250px;">{{ basename($doc->file_path) }}</span>
                                            <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-alt-secondary py-0 px-2" download>
                                                <i class="fa fa-download me-1"></i> Download
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted fs-xs">No attachment uploaded</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Record Timestamps</th>
                                <td class="fs-xs text-muted">
                                    Created: {{ $doc->created_at ? $doc->created_at->format('d M, Y h:i A') : 'N/A' }} &bull;
                                    Updated: {{ $doc->updated_at ? $doc->updated_at->format('d M, Y h:i A') : 'N/A' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Custom Attributes Section -->
                    <h6 class="fw-bold text-uppercase fs-xs text-muted mb-2">
                        <i class="fa fa-sliders-h me-1 text-primary"></i> 2. Category Custom Attributes & Values
                    </h6>
                    @if ($doc->attributeValues && $doc->attributeValues->count() > 0)
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-striped fs-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%;">Attribute Name</th>
                                        <th style="width: 20%;">Field Type</th>
                                        <th style="width: 45%;">Recorded Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($doc->attributeValues as $attrVal)
                                        @php
                                            $raw = $attrVal->value;
                                            $type = $attrVal->attribute->attribute_type ?? 'text';
                                            $valDisplay = $raw;
                                            if ($type === 'boolean') {
                                                $valDisplay = ($raw === '1' || $raw === 1 || $raw === true) ? 'Yes' : 'No';
                                            } elseif (is_string($raw) && str_starts_with(trim($raw), '[') && str_ends_with(trim($raw), ']')) {
                                                $decoded = json_decode($raw, true);
                                                if (is_array($decoded)) {
                                                    $valDisplay = implode(', ', $decoded);
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold text-dark">{{ $attrVal->attribute->attribute_name ?? 'Custom Attribute' }}</td>
                                            <td>
                                                <span class="badge bg-light text-dark border font-monospace">{{ ucfirst($type) }}</span>
                                            </td>
                                            <td>
                                                @if ($type === 'boolean')
                                                    <span class="badge {{ $valDisplay === 'Yes' ? 'bg-success' : 'bg-danger' }}">{{ $valDisplay }}</span>
                                                @else
                                                    <span class="fw-semibold text-dark">{{ $valDisplay ?? '-' }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-light border py-2 px-3 text-muted fs-sm mb-4">
                            <i class="fa fa-info-circle me-1"></i> No custom attribute fields are assigned or populated for this document category.
                        </div>
                    @endif

                    <!-- Embedded Preview in Details Modal (if file present) -->
                    @if ($doc->file_path)
                        <h6 class="fw-bold text-uppercase fs-xs text-muted mb-2">
                            <i class="fa fa-eye me-1 text-primary"></i> 3. Document File Preview
                        </h6>
                        <div class="p-3 bg-light rounded text-center border">
                            @if ($isImage)
                                <img src="{{ $fileUrl }}" alt="Document Preview" class="img-fluid rounded shadow-sm doc-preview-trigger"
                                     style="max-height: 260px; cursor: pointer;"
                                     data-url="{{ $fileUrl }}" data-type="image" data-title="{{ $doc->category->category_name ?? 'Document' }}"
                                     title="Click to view zoomed image">
                                <div class="text-muted fs-xs mt-2"><i class="fa fa-search-plus me-1"></i> Click image to open zoom viewer</div>
                            @elseif ($isPdf)
                                <div class="py-2">
                                    <button type="button" class="btn btn-primary doc-preview-trigger"
                                            data-url="{{ $fileUrl }}" data-type="pdf" data-title="{{ $doc->category->category_name ?? 'Document' }}">
                                        <i class="fa fa-file-pdf me-1"></i> Open PDF Viewer
                                    </button>
                                    <a href="{{ $fileUrl }}" target="_blank" class="btn btn-alt-secondary ms-2" download>
                                        <i class="fa fa-download me-1"></i> Download PDF
                                    </a>
                                </div>
                            @else
                                <div class="py-2">
                                    <a href="{{ $fileUrl }}" target="_blank" class="btn btn-secondary" download>
                                        <i class="fa fa-download me-1"></i> Download File ({{ strtoupper($ext) }})
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-body-light d-flex justify-content-between">
                    <div>
                        <a href="{{ route('generic-documents.show', $doc->id) }}" target="_blank" class="btn btn-sm btn-alt-secondary">
                            <i class="fa fa-external-link-alt me-1"></i> Open Dedicated Page
                        </a>
                    </div>
                    <div class="d-flex gap-2">
                        @can('edit-generic-document')
                            <a href="{{ route('generic-documents.edit', ['generic_document' => $doc->id, 'return_url' => $currentUrl]) }}" class="btn btn-sm btn-warning">
                                <i class="fa fa-pencil-alt me-1"></i> Edit Document
                            </a>
                        @endcan
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- ========================================== -->
<!-- 2. UNIVERSAL FILE PREVIEW MODAL (ZOOM/PDF) -->
<!-- ========================================== -->
<div class="modal fade" id="docUniversalPreviewModal" tabindex="-1" aria-labelledby="docUniversalPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-body-light py-2">
                <h5 class="modal-title fs-sm fw-bold" id="docUniversalPreviewModalLabel">
                    <i class="fa fa-file-search me-1 text-primary"></i> Document File Preview
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="docUniversalDownloadLink" class="btn btn-sm btn-alt-secondary" download>
                        <i class="fa fa-download me-1"></i> Download
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body text-center p-0" style="background: #2b2b2b; min-height: 480px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                <!-- Image Zoom Container -->
                <div id="docUniversalImageContainer" class="w-100" style="overflow: hidden; cursor: grab; display: none;">
                    <img id="docUniversalZoomImage" src="" alt="Document Preview" class="img-fluid" style="max-height: 80vh; transition: transform 0.15s ease-out; user-select: none;">
                </div>
                <!-- PDF iFrame Container -->
                <div id="docUniversalPdfContainer" class="w-100 h-100" style="display: none;">
                    <iframe id="docUniversalPdfIframe" src="" width="100%" height="650px" style="border: none;"></iframe>
                </div>
                <!-- Unsupported Container -->
                <div id="docUniversalOtherContainer" class="text-white py-5" style="display: none;">
                    <i class="fa fa-file fa-3x text-muted mb-3 d-block"></i>
                    <p class="mb-2">Preview is not available for this file type.</p>
                    <a href="#" id="docUniversalOtherDownloadLink" class="btn btn-sm btn-light" download>Download File</a>
                </div>
            </div>
            <div class="modal-footer bg-body-light py-1 fs-xs text-muted d-flex justify-content-between">
                <span id="docUniversalZoomHelper"><i class="fa fa-mouse me-1"></i> Scroll to zoom &bull; Drag to pan &bull; Double-click to reset</span>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 3. ADD DOCUMENT MODAL (EMBEDDED CREATION)  -->
<!-- ========================================== -->
<div class="modal fade" id="addDocumentModal" tabindex="-1" aria-labelledby="addDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-body-light py-2">
                <h5 class="modal-title fs-sm fw-bold" id="addDocumentModalLabel">
                    <i class="fa fa-plus me-1 text-primary"></i> Add Document for {{ ucfirst($documentableType) }}: {{ $entityLabel }}
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('generic-documents.create', ['documentable_type' => $documentableType, 'documentable_id' => $documentableId]) }}" 
                       target="_blank" class="btn btn-sm btn-alt-secondary" title="Open full creation form in new tab">
                        <i class="fa fa-external-link-alt me-1"></i> Full Page Form
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0" style="min-height: 520px;">
                <iframe src="{{ route('generic-documents.create', ['embedded' => 1, 'documentable_type' => $documentableType, 'documentable_id' => $documentableId]) }}" 
                        style="width: 100%; height: 75vh; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 4. COMPONENT JAVASCRIPT & INTERACTIONS     -->
<!-- ========================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Universal File Preview Modal Logic
    const previewModalEl = document.getElementById('docUniversalPreviewModal');
    if (previewModalEl) {
        const previewModal = new bootstrap.Modal(previewModalEl);
        const titleEl = document.getElementById('docUniversalPreviewModalLabel');
        const dlLink = document.getElementById('docUniversalDownloadLink');
        const imgContainer = document.getElementById('docUniversalImageContainer');
        const zoomImg = document.getElementById('docUniversalZoomImage');
        const pdfContainer = document.getElementById('docUniversalPdfContainer');
        const pdfIframe = document.getElementById('docUniversalPdfIframe');
        const otherContainer = document.getElementById('docUniversalOtherContainer');
        const otherDlLink = document.getElementById('docUniversalOtherDownloadLink');
        const zoomHelper = document.getElementById('docUniversalZoomHelper');

        // Zoom & Pan state
        let scale = 1;
        let isPanning = false;
        let startX = 0, startY = 0;
        let translateX = 0, translateY = 0;

        function updateTransform() {
            if (zoomImg) {
                zoomImg.style.transform = `scale(${scale}) translate(${translateX}px, ${translateY}px)`;
            }
        }

        function resetZoom() {
            scale = 1;
            translateX = 0;
            translateY = 0;
            updateTransform();
        }

        // Attach trigger clicks
        document.querySelectorAll('.doc-preview-trigger').forEach(trigger => {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.getAttribute('data-url');
                const type = this.getAttribute('data-type');
                const title = this.getAttribute('data-title') || 'Document File';

                if (titleEl) titleEl.innerHTML = `<i class="fa fa-file-search me-1 text-primary"></i> ${title}`;
                if (dlLink) dlLink.href = url;

                // Reset views
                imgContainer.style.display = 'none';
                pdfContainer.style.display = 'none';
                otherContainer.style.display = 'none';
                zoomHelper.style.display = 'none';

                if (type === 'image') {
                    zoomImg.src = url;
                    imgContainer.style.display = 'block';
                    zoomHelper.style.display = 'inline-block';
                    resetZoom();
                } else if (type === 'pdf') {
                    pdfIframe.src = url;
                    pdfContainer.style.display = 'block';
                } else {
                    otherDlLink.href = url;
                    otherContainer.style.display = 'block';
                }

                previewModal.show();
            });
        });

        // Mouse Wheel Zoom
        if (imgContainer) {
            imgContainer.addEventListener('wheel', e => {
                e.preventDefault();
                const delta = e.deltaY * -0.0012;
                scale = Math.min(Math.max(1, scale + delta), 4.5);
                updateTransform();
            });

            // Drag Pan
            imgContainer.addEventListener('mousedown', e => {
                e.preventDefault();
                isPanning = true;
                imgContainer.style.cursor = 'grabbing';
                startX = e.clientX;
                startY = e.clientY;
            });

            const stopPan = () => {
                isPanning = false;
                if (imgContainer) imgContainer.style.cursor = 'grab';
            };
            imgContainer.addEventListener('mouseup', stopPan);
            imgContainer.addEventListener('mouseleave', stopPan);

            imgContainer.addEventListener('mousemove', e => {
                if (!isPanning) return;
                e.preventDefault();
                const dx = (e.clientX - startX) / scale;
                const dy = (e.clientY - startY) / scale;
                translateX += dx;
                translateY += dy;
                startX = e.clientX;
                startY = e.clientY;
                updateTransform();
            });

            // Double Click Reset / Zoom
            zoomImg.addEventListener('dblclick', () => {
                if (scale !== 2) {
                    scale = 2;
                    translateX = 0;
                    translateY = 0;
                } else {
                    resetZoom();
                }
                updateTransform();
            });
        }

        // Reset on Modal Close
        previewModalEl.addEventListener('hidden.bs.modal', () => {
            resetZoom();
            if (pdfIframe) pdfIframe.src = '';
            if (zoomImg) zoomImg.src = '';
        });
    }

    // Embedded Document Creation Message Listener
    window.addEventListener('message', function (event) {
        if (event.data && event.data.type === 'documentCreated') {
            const addModalEl = document.getElementById('addDocumentModal');
            if (addModalEl) {
                const addModal = bootstrap.Modal.getInstance(addModalEl);
                if (addModal) addModal.hide();
            }
            // Auto reload current page to reflect new document
            window.location.reload();
        }
    });
});
</script>
