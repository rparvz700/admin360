@extends('Partials.app', ['activeMenu' => 'asset-attributes'])

@section('title')
    {{ env('APP_NAME') }}
@endsection

@section('page_title')
    Asset Attribute Details
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Asset Attribute Details</h3>
                <a href="{{ route('asset-attributes.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
            </div>
            <div class="block-content">
                <dl class="row mb-0">
                    <dt class="col-sm-3">ID</dt>
                    <dd class="col-sm-9">{{ $attribute->id }}</dd>

                    <dt class="col-sm-3">Category</dt>
                    <dd class="col-sm-9">{{ $attribute->category ? $attribute->category->name : '' }}</dd>

                    <dt class="col-sm-3">Name</dt>
                    <dd class="col-sm-9">{{ $attribute->name }}</dd>

                    <dt class="col-sm-3">Type</dt>
                    <dd class="col-sm-9">{{ $attribute->type }}</dd>

                    <dt class="col-sm-3">Options</dt>
                    <dd class="col-sm-9">{{ is_array($attribute->options) ? implode(',', $attribute->options) : $attribute->options }}</dd>
                </dl>
            </div>
        </div>
    </div>
@endsection
