@extends('Partials.app', ['activeMenu' => 'asset-categories'])

@section('title')
    {{ env('APP_NAME') }}
@endsection

@section('page_title')
    Asset Category Details
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Asset Category Details</h3>
                <a href="{{ route('asset-categories.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
            </div>
            <div class="block-content">
                <dl class="row">
                    <dt class="col-sm-3">ID</dt>
                    <dd class="col-sm-9">{{ $category->id }}</dd>
                    <dt class="col-sm-3">Name</dt>
                    <dd class="col-sm-9">{{ $category->name }}</dd>
                    <dt class="col-sm-3">Description</dt>
                    <dd class="col-sm-9">{{ $category->description }}</dd>
                </dl>
            </div>
        </div>
    </div>
@endsection
