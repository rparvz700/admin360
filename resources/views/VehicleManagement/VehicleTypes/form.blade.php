@if ($errors->any())
    <div class="alert alert-danger d-flex align-items-start gap-2 mb-4" role="alert">
        <i class="fa fa-exclamation-circle mt-1"></i>
        <div>
            <div class="fw-semibold">Please review the following:</div>
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="block block-rounded block-bordered shadow-sm mb-0">
    <div class="block-header block-header-default bg-body-light py-2">
        <h3 class="block-title fs-sm fw-bold text-uppercase">
            <i class="fa fa-tag me-1 text-primary"></i> Vehicle Type Details
        </h3>
    </div>
    <div class="block-content pt-4 pb-3">
        <div class="row">
            <div class="col-md-8 col-lg-6 mb-3">
                <label class="form-label" for="type_name">Type / Category Name <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-tag"></i></span>
                    <input type="text" class="form-control" id="type_name" name="type_name" value="{{ old('type_name', $type->type_name ?? '') }}" placeholder="e.g. Sedan, Microbus, SUV, Pickup, Motorcycle" required autofocus>
                </div>
                <div class="form-text fs-xs text-muted">A clear, standard vehicle classification name used across fleet reports and assignments.</div>
            </div>
        </div>
    </div>
</div>
