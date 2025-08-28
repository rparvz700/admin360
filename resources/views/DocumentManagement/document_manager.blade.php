@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <h2>Document Manager</h2>
    <form id="document-form" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label" for="category_id">Document Type <span class="text-danger">*</span></label>
                    <select class="form-select select2" id="category_id" name="category_id" required>
                        <option value="">Select Type</option>
                        {{-- @foreach($categories as $cat) --}}
                        {{-- <option value="{{ $cat->id }}">{{ $cat->category_name }}</option> --}}
                        {{-- @endforeach --}}
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="related_entity">Related Entity <span class="text-danger">*</span></label>
                    <select class="form-select" id="related_entity" name="related_entity" required>
                        <option value="">Select Entity</option>
                        <option value="vehicle">Vehicle</option>
                        <option value="employee">Employee</option>
                        <option value="asset">Asset</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="issue_date">Issue Date</label>
                    <input type="date" class="form-control" id="issue_date" name="issue_date">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="expiry_date">Expiry Date</label>
                    <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active">Active</option>
                        <option value="expired">Expired</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Upload Document(s)</label>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="fa fa-upload"></i> Attach File(s)
                    </button>
                </div>
            </div>
            <div class="col-md-6" id="document-preview-section" style="display:none;">
                <h5>Document Preview</h5>
                <div id="document-preview" class="border p-2 mb-2" style="min-height:200px; background:#f8f9fa;"></div>
            </div>
        </div>
        <hr>
        <h5>Custom Fields</h5>
        <div id="dynamic-attributes"></div>
        <button type="submit" class="btn btn-success mt-3">Save Document</button>
    </form>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="document_files" class="form-label">Select File(s)</label>
                        <input class="form-control" type="file" id="document_files" name="document_files[]" multiple accept=".pdf,image/*">
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" value="1" id="use_ai_parsing" name="use_ai_parsing">
                        <label class="form-check-label" for="use_ai_parsing">Use AI Parsing</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="upload-btn">Upload</button>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Extraction Confirmation Modal -->
    <div class="modal fade" id="aiExtractModal" tabindex="-1" aria-labelledby="aiExtractModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="aiExtractModalLabel">AI Data Extraction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>AI has extracted data from your document. Would you like to populate the form fields with this data?</p>
                    <div id="ai-extracted-data" class="mb-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep My Data</button>
                    <button type="button" class="btn btn-success" id="apply-ai-data">Yes, Use Extracted Data</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Placeholder: Load attributes dynamically based on selected category
$('#category_id').on('change', function() {
    // AJAX to fetch attributes and render fields in #dynamic-attributes
});
// Placeholder: Show preview section if file is image/pdf
$('#upload-btn').on('click', function() {
    // Handle upload, preview, and AI extraction modal
});
$('#apply-ai-data').on('click', function() {
    // Populate fields with extracted data
    $('#aiExtractModal').modal('hide');
});
</script>
@endpush
