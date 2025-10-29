@props(['selectedDocument' => null])

<div class="document-selector">
    <label class="form-label">Attach Document</label>

    <div class="d-flex align-items-center gap-2 mb-2">
        <span id="selected-document-label">
            @if($selectedDocument)
                <strong>{{ $selectedDocument->primary_text ?? 'Document #'.$selectedDocument->id }}</strong>
            @else
                <em>No document selected</em>
            @endif
        </span>
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#selectDocumentModal">
            Select Document
        </button>
        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#createDocumentModal">
            Add New
        </button>
    </div>

    <input type="hidden" name="document_id" id="selected-document-id" value="{{ $selectedDocument->id ?? '' }}">

    {{-- Select Modal --}}
    <div class="modal fade" id="selectDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Existing Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="documentSearch" class="form-control mb-3" placeholder="Search documents...">
                    <table class="table table-hover align-middle" id="documentListTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Primary Text</th>
                                <th>Category</th>
                                <th>Expiry Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="documentListBody">
                            <tr><td colspan="6" class="text-center text-muted">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="createDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe src="{{ route('generic-documents.create') }}" id="createDocumentIframe"
                            style="width:100%; height:80vh; border:0;"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('documentListBody');
    const searchInput = document.getElementById('documentSearch');
    const label = document.getElementById('selected-document-label');
    const hiddenInput = document.getElementById('selected-document-id');

    function loadDocuments(query = '') {
        fetch(`{{ route('generic-documents.index') }}?ajax=1&search=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                tableBody.innerHTML = '';
                if (!data.length) {
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No documents found</td></tr>';
                    return;
                }
                data.forEach(doc => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${doc.id}</td>
                        <td>${doc.documentable_type_label ?? ''}</td>
                        <td>${doc.primary_text ?? ''}</td>
                        <td>${doc.category?.category_name ?? ''}</td>
                        <td>${doc.expiry_date ?? ''}</td>
                        <td><button class="btn btn-sm btn-primary select-doc" data-id="${doc.id}" data-label="${doc.primary_text ?? ''}">Select</button></td>
                    `;
                    tableBody.appendChild(row);
                });
            });
    }

    // Initial load
    loadDocuments();

    // Search
    searchInput.addEventListener('input', e => {
        loadDocuments(e.target.value);
    });

    // Select document
    tableBody.addEventListener('click', e => {
        if (e.target.classList.contains('select-doc')) {
            const id = e.target.dataset.id;
            const text = e.target.dataset.label;
            hiddenInput.value = id;
            label.innerHTML = `<strong>${text || 'Document #' + id}</strong>`;
            bootstrap.Modal.getInstance(document.getElementById('selectDocumentModal')).hide();
        }
    });

    // Listen for postMessage from iframe after creation
    window.addEventListener('message', function(e) {
        if (e.data.type === 'documentCreated') {
            const newDoc = e.data.document;
            hiddenInput.value = newDoc.id;
            label.innerHTML = `<strong>${newDoc.primary_text}</strong>`;
            bootstrap.Modal.getInstance(document.getElementById('createDocumentModal')).hide();
        }
    });
});
</script>
@endpush
