@php
    $isEdit = $mode === 'edit';
    $statusValue = old('status', $agreement->status ?? '');
@endphp

@if ($errors->any())
    <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
        <i class="fa fa-exclamation-circle mt-1"></i>
        <div>
            <div class="fw-semibold">Please review the highlighted fields.</div>
            <div class="small">{{ $errors->first() }}</div>
        </div>
    </div>
@endif

<div class="agreement-form-section">
    <div class="agreement-section-heading">
        <span class="agreement-section-icon"><i class="fa fa-file-signature"></i></span>
        <div>
            <h4>Agreement Information</h4>
            <p>Core reference, dates, status, and notes for this agreement.</p>
        </div>
    </div>

    <div class="row g-3">
        <!-- Row 1: Reference No, Vendor, Status -->
        <div class="col-lg-4 col-md-6">
            <label class="form-label fw-semibold" for="agreement_ref_no">Reference No <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('agreement_ref_no') is-invalid @enderror"
                id="agreement_ref_no" name="agreement_ref_no" placeholder="Enter reference no"
                value="{{ old('agreement_ref_no', $agreement->agreement_ref_no ?? '') }}" required>
            @error('agreement_ref_no')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-4 col-md-6">
            <label class="form-label fw-semibold" for="vendor_id">Vendor</label>
            <select class="form-select js-select2 @error('vendor_id') is-invalid @enderror" id="vendor_id" name="vendor_id" data-placeholder="Select Vendor (Optional)" style="width: 100%;">
                <option value=""></option>
                @foreach ($vendors as $vendor)
                    <option value="{{ $vendor->id }}" {{ (string) old('vendor_id', $agreement->vendor_id ?? '') === (string) $vendor->id ? 'selected' : '' }}>
                        {{ $vendor->name }} ({{ $vendor->vendor_code }})
                    </option>
                @endforeach
            </select>
            @error('vendor_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-4 col-md-6">
            <label class="form-label fw-semibold" for="status">Status <span class="text-danger">*</span></label>
            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status"
                required>
                <option value="">Select Status</option>
                <option value="1" {{ (string) $statusValue === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ (string) $statusValue === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Row 2: Agreement Date, From Date, To Date -->
        <div class="col-lg-4 col-md-6">
            <label class="form-label fw-semibold" for="agreement_date">Agreement Date</label>
            <input type="date" class="form-control @error('agreement_date') is-invalid @enderror" id="agreement_date"
                name="agreement_date" value="{{ old('agreement_date', $agreement->agreement_date ?? '') }}">
            @error('agreement_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-4 col-md-6">
            <label class="form-label fw-semibold" for="from_date">From Date</label>
            <input type="date" class="form-control @error('from_date') is-invalid @enderror" id="from_date"
                name="from_date" value="{{ old('from_date', $agreement->from_date ?? '') }}">
            @error('from_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-4 col-md-6">
            <label class="form-label fw-semibold" for="to_date">To Date</label>
            <input type="date" class="form-control @error('to_date') is-invalid @enderror" id="to_date"
                name="to_date" value="{{ old('to_date', $agreement->to_date ?? '') }}">
            @error('to_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Row 3: Remarks -->
        <div class="col-12">
            <label class="form-label fw-semibold" for="remarks">Remarks</label>
            <textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="3"
                placeholder="Add internal notes or agreement context">{{ old('remarks', $agreement->remarks ?? '') }}</textarea>
            @error('remarks')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

@if ($isEdit)
    <div class="agreement-form-section">
        <div class="agreement-section-heading">
            <span class="agreement-section-icon"><i class="fa fa-paperclip"></i></span>
            <div>
                <h4>Documents</h4>
                <p>Attach an existing document or upload a new one for this agreement.</p>
            </div>
        </div>

        @include('components.select-generic-document', [
            'documents' => $documents,
            'documentableType' => 'agreement',
            'documentableId' => $agreement->id,
        ])
    </div>
@endif
