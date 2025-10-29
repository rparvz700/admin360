@extends('Partials.app', ['activeMenu' => 'asset-categories'])

@section('title')
    {{ config('app.name') }} 
@endsection

@section('page_title')
    Add Asset Category
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Add Asset Category</h3>
                <a href="{{ route('asset-categories.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
            </div>
            <div class="block-content">
                <form action="{{ route('asset-categories.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="category_name">Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="category_name" name="category_name" value="{{ old('category_name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control" id="description" name="description">{{ old('description') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
@endsection
