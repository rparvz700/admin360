<div class="generic-document-selector mb-3">
    <label class="form-label">Attach Document</label>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm open-select-modal">
            Select Existing
        </button>
        <button type="button" class="btn btn-outline-success btn-sm open-create-modal">
            Add New
        </button>
        <span class="selected-doc-label text-muted ms-2">(No document selected)</span>
        <input type="hidden" name="generic_document_id" class="selected-doc-id" value="">
    </div>

    {{-- Hidden Modals (scoped per component instance) --}}
    <div class="modal fade document-list-modal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Select Existing Document</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <table class="table table-bordered table-hover document-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Type</th>
                  <th>Category</th>
                  <th>Issue Date</th>
                  <th>Expiry Date</th>
                  <th>Select</th>
                </tr>
              </thead>
              <tbody>
                @foreach($documents ?? [] as $doc)
                  <tr>
                    <td>{{ $doc->id }}</td>
                    <td>{{ class_basename($doc->documentable_type) }}</td>
                    <td>{{ $doc->category->category_name ?? '' }}</td>
                    <td>{{ $doc->issue_date }}</td>
                    <td>{{ $doc->expiry_date }}</td>
                    <td>
                      <button type="button" class="btn btn-sm btn-primary select-document-btn"
                              data-id="{{ $doc->id }}"
                              data-label="{{ class_basename($doc->documentable_type) }} ({{ $doc->category->category_name ?? '' }})">
                          Select
                      </button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade document-create-modal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Create New Document</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-0">
            <iframe src="{{ route('generic-documents.create') }}" class="create-doc-iframe" style="width:100%; height:80vh; border:none;"></iframe>
          </div>
        </div>
      </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.generic-document-selector').forEach(function(selector) {
        const selectModalEl = selector.querySelector('.document-list-modal');
        const createModalEl = selector.querySelector('.document-create-modal');
        const selectModal = new bootstrap.Modal(selectModalEl);
        const createModal = new bootstrap.Modal(createModalEl);

        // Open modals
        selector.querySelector('.open-select-modal').addEventListener('click', () => selectModal.show());
        selector.querySelector('.open-create-modal').addEventListener('click', () => createModal.show());

        // Select existing document
        selector.querySelectorAll('.select-document-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const label = btn.dataset.label;
                selector.querySelector('.selected-doc-id').value = id;
                selector.querySelector('.selected-doc-label').textContent = label;
                selectModal.hide();
            });
        });

        // Listen for postMessage (when new doc is created)
        window.addEventListener('message', (event) => {
            if (event.data?.type === 'documentCreated') {
                selector.querySelector('.selected-doc-id').value = event.data.id;
                selector.querySelector('.selected-doc-label').textContent = event.data.label;
                createModal.hide();
            }
        });
    });
});
</script>
@endpush
