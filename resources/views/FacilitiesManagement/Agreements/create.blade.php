@extends('Partials.app', ['activeMenu' => 'agreements'])

@section('title')
    {{ config('app.name') }} 
@endsection

@section('page_title')
    Add Agreement
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Add Agreement</h3>
            </div>
            <div class="block-content fs-sm data-content">
                <form class="mb-4" action="{{ route('agreements.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="agreement_ref_no">Reference No<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="agreement_ref_no" name="agreement_ref_no" value="{{ old('agreement_ref_no', $agreement->agreement_ref_no ?? '') }}" required>
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="agreement_date">Agreement Date</label>
                            <input type="date" class="form-control" id="agreement_date" name="agreement_date" value="{{ old('agreement_date', $agreement->agreement_date ?? '') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="from_date">From Date</label>
                            <input type="date" class="form-control" id="from_date" name="from_date" value="{{ old('from_date', $agreement->from_date ?? '') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="to_date">To Date</label>
                            <input type="date" class="form-control" id="to_date" name="to_date" value="{{ old('to_date', $agreement->to_date ?? '') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="status">Status</label>
                            <input type="text" class="form-control" id="status" name="status" value="{{ old('status', $agreement->status ?? '') }}">
                        </div>
                        <div class="col-md-12 mb-4">
                            <label class="form-label" for="remarks">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks">{{ old('remarks', $agreement->remarks ?? '') }}</textarea>
                        </div>
                        @include('components.select-generic-document', ['documents' => $documents])
                        
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('agreements.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
@endsection
