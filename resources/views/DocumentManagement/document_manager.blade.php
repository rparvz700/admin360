@extends('Partials.app', ['activeMenu' => 'document-managerdocument-manager.prototype'])

@section('title')
    {{ env('APP_NAME') }}
@endsection

@section('scripts')
    <link href="{{ asset('js/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
@endsection

@section('page_title')
    Edit Vehicle Document
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Document Manager</h4>
                </div>
                <form id="document-form" enctype="multipart/form-data" class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label" for="category_id">Document Type <span class="text-danger">*</span></label>
                                <select class="form-select select2" id="category_id" name="category_id" required>
                                    <option value="">Select Type</option>
                                    {{-- @foreach($categories as $cat) --}}
                                    {{-- <option value="{{ $cat->id }}">{{ $cat->category_name }}</option> --}}
                                    {{-- @endforeach --}}
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label" for="related_entity">Related Entity <span class="text-danger">*</span></label>
                                <select class="form-select" id="related_entity" name="related_entity" required>
                                    <option value="">Select Entity</option>
                                    <option value="vehicle">Vehicle</option>
                                    <option value="employee">Employee</option>
                                    <option value="asset">Asset</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label" for="issue_date">Issue Date</label>
                                <input type="date" class="form-control" id="issue_date" name="issue_date">
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label" for="expiry_date">Expiry Date</label>
                                <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label" for="status">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active">Active</option>
                                    <option value="expired">Expired</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Upload Document(s)</label>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                    <i class="fa fa-upload"></i> Attach File(s)
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6" id="document-preview-section" style="display:none;">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Document Preview</h6>
                                </div>
                                <div id="document-preview" class="card-body border p-2 mb-2" style="min-height:200px; background:#f8f9fa;"></div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <h5 class="mb-2">Custom Fields</h5>
                        <div id="dynamic-attributes"></div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-success">Save Document</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
let uploadedFile = null;
let mockExtractedData = {
    string_field: 'Sample Text',
    number_field: 12345,
    date_field: '2025-08-28',
    boolean_field: true,
    select_field: 'Option 2'
};


// Ensure jQuery and DOM are ready
$(document).ready(function() {
    // Use event delegation for upload button
    $(document).on('click', '#upload-btn', function() {
        const files = document.getElementById('document_files').files;
        if (!files.length) {
            alert('Please select a file to upload.');
            return;
        }
        uploadedFile = files[0];
        // Close the upload modal first, then show side-by-side modal after modal is fully hidden
        $('#uploadModal').modal('hide');
        $('#uploadModal').on('hidden.bs.modal', function() {
            showSideBySideModal();
            // Remove this handler so it doesn't fire multiple times
            $(this).off('hidden.bs.modal');
        });
    });
});

function showSideBySideModal() {
    // Create modal if not exists
    if (!document.getElementById('sideBySideModal')) {
        $('body').append(`
        <div class="modal fade" id="sideBySideModal" tabindex="-1" aria-labelledby="sideBySideModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sideBySideModalLabel">Validate Extracted Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Document Preview</h6>
                                <div id="side-preview" class="border p-2 mb-2" style="min-height:300px; background:#f8f9fa;"></div>
                            </div>
                            <div class="col-md-6">
                                <h6>Extracted Fields</h6>
                                <form id="side-fields">
                                    <div class="mb-3">
                                        <label>String Field</label>
                                        <input type="text" class="form-control" id="mock_string_field" value="${mockExtractedData.string_field}">
                                    </div>
                                    <div class="mb-3">
                                        <label>Number Field</label>
                                        <input type="number" class="form-control" id="mock_number_field" value="${mockExtractedData.number_field}">
                                    </div>
                                    <div class="mb-3">
                                        <label>Date Field</label>
                                        <input type="date" class="form-control" id="mock_date_field" value="${mockExtractedData.date_field}">
                                    </div>
                                    <div class="mb-3">
                                        <label>Boolean Field</label><br>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="mock_boolean_field" id="mock_boolean_yes" value="1" ${mockExtractedData.boolean_field ? 'checked' : ''}>
                                            <label class="form-check-label" for="mock_boolean_yes">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="mock_boolean_field" id="mock_boolean_no" value="0" ${!mockExtractedData.boolean_field ? 'checked' : ''}>
                                            <label class="form-check-label" for="mock_boolean_no">No</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label>Select Field</label>
                                        <select class="form-select" id="mock_select_field">
                                            <option value="Option 1" ${mockExtractedData.select_field === 'Option 1' ? 'selected' : ''}>Option 1</option>
                                            <option value="Option 2" ${mockExtractedData.select_field === 'Option 2' ? 'selected' : ''}>Option 2</option>
                                            <option value="Option 3" ${mockExtractedData.select_field === 'Option 3' ? 'selected' : ''}>Option 3</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="side-apply-btn">Apply Data</button>
                    </div>
                </div>
            </div>
        </div>
        `);
    }
    // Show preview after modal is shown to ensure DOM is ready
    $('#sideBySideModal').on('shown.bs.modal', function() {
        const preview = document.getElementById('side-preview');
        if (uploadedFile && preview) {
            if (uploadedFile.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="img-fluid" style="max-height:250px;">`;
                };
                reader.readAsDataURL(uploadedFile);
            } else if (uploadedFile.type === 'application/pdf') {
                preview.innerHTML = `<embed src="${URL.createObjectURL(uploadedFile)}" type="application/pdf" width="100%" height="250px">`;
            } else {
                preview.innerHTML = `<span class='text-muted'>No preview available for this file type.</span>`;
            }
        }
        // Remove this handler after running
        $(this).off('shown.bs.modal');
    });
    $('#sideBySideModal').modal('show');
}

// Apply extracted data to main form fields
$(document).on('click', '#side-apply-btn', function() {
    $('#dynamic-attributes').html(`
        <div class='row'>
            <div class='col-md-6 mb-3'>
                <label>String Field</label>
                <input type='text' class='form-control' name='string_field' value='${$('#mock_string_field').val()}'>
            </div>
            <div class='col-md-6 mb-3'>
                <label>Number Field</label>
                <input type='number' class='form-control' name='number_field' value='${$('#mock_number_field').val()}'>
            </div>
            <div class='col-md-6 mb-3'>
                <label>Date Field</label>
                <input type='date' class='form-control' name='date_field' value='${$('#mock_date_field').val()}'>
            </div>
            <div class='col-md-6 mb-3'>
                <label>Boolean Field</label><br>
                <div class='form-check form-check-inline'>
                    <input class='form-check-input' type='radio' name='boolean_field' value='1' ${$('input[name="mock_boolean_field"]:checked').val() == '1' ? 'checked' : ''}>
                    <label class='form-check-label'>Yes</label>
                </div>
                <div class='form-check form-check-inline'>
                    <input class='form-check-input' type='radio' name='boolean_field' value='0' ${$('input[name="mock_boolean_field"]:checked').val() == '0' ? 'checked' : ''}>
                    <label class='form-check-label'>No</label>
                </div>
            </div>
            <div class='col-md-6 mb-3'>
                <label>Select Field</label>
                <select class='form-select' name='select_field'>
                    <option value='Option 1' ${$('#mock_select_field').val() === 'Option 1' ? 'selected' : ''}>Option 1</option>
                    <option value='Option 2' ${$('#mock_select_field').val() === 'Option 2' ? 'selected' : ''}>Option 2</option>
                    <option value='Option 3' ${$('#mock_select_field').val() === 'Option 3' ? 'selected' : ''}>Option 3</option>
                </select>
            </div>
        </div>
    `);
    $('#sideBySideModal').modal('hide');
});
</script>
@endpush
