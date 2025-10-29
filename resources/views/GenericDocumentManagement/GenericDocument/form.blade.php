<!-- Step 1: Choose Entity Type -->
<div class="mb-3">
    <label class="form-label" for="documentable_type">Documentable<span class="text-danger">*</span></label>
    <select name="documentable_type" id="documentable_type" class="form-control" required>
        <option value="">-- Select Document Owner Type --</option>
        @foreach($documentableTypes as $label => $config)
            <option value="{{ $label }}"
                @if(old('documentable_type', $selectedDocumentableType ?? '') == $label) selected @endif>
                {{ ucfirst($label) }}
            </option>
        @endforeach
    </select>
</div>

<!-- Step 2: Choose Specific Record -->
<div class="mb-3" id="documentable-id-wrapper">
    <label class="form-label" for="documentable_id">Select Record <span class="text-danger">*</span></label>
    <select class="form-select select2" id="documentable_id" name="documentable_id" style="width:100%;" required>
        <option value="">Select record</option>
        @if(isset($documentables) && $documentables->count())
            @foreach($documentables as $item)
                <option value="{{ $item->id }}"
                    @if(old('documentable_id', $doc->documentable_id ?? '') == $item->id) selected @endif>
                    {{ $item->label }}
                </option>
            @endforeach
        @endif
    </select>
</div>
<div class="mb-3">
    <label class="form-label" for="category_id">Category<span class="text-danger">*</span></label>
    <select class="form-select select2" id="category_id" name="category_id" required>
        <option value="">Select Category</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ old('category_id', $doc->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->category_name }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label" for="issue_date">Issue Date <span class="text-danger">*</span></label>
    <input type="date" class="form-control" id="issue_date" name="issue_date" value="{{ old('issue_date', $doc->issue_date ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label" for="expiry_date">Expiry Date</label>
    <input type="date" class="form-control" id="expiry_date" name="expiry_date" value="{{ old('expiry_date', $doc->expiry_date ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label" for="file">Upload File</label>
    <input type="file" class="form-control" id="file" name="file" accept="image/*,application/pdf">

    @if(!empty($doc->file_path))
        @php
            $ext = pathinfo($doc->file_path, PATHINFO_EXTENSION);
        @endphp
        <div class="mt-3">
            @if(in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp']))
                <img src="{{ asset('storage/'.$doc->file_path) }}"
                     class="img-thumbnail"
                     alt="Document"
                     style="max-width: 150px; cursor: pointer;"
                     data-bs-toggle="modal"
                     data-bs-target="#filePreviewModal">
            @elseif(strtolower($ext) === 'pdf')
                <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="btn btn-sm btn-info">
                    View PDF
                </a>
            @else
                <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="btn btn-sm btn-secondary">
                    Download File
                </a>
            @endif
        </div>
    @endif
</div>



<hr>
<h5>Custom Fields</h5>
<div id="attribute-fields"></div>
