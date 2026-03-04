@extends('Partials.app', ['activeMenu' => 'asset-categories'])

@section('title')
    {{ config('app.name') }}
@endsection

@section('page_title')
    Edit Project
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Edit Project</h3>
                <a href="{{ route('projects.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
            </div>
            <div class="block-content">
                <form action="{{ route('projects.update', $project->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="name">Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ $project->name ?? '' }}" required>
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="status">Status<span class="text-danger">*</span></label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="1" {{ $project->status == '1' ? 'selected' : '' }}>Active
                                </option>
                                <option value="0" {{ $project->status == '0' ? 'selected' : '' }}>Inactive
                                </option>
                            </select>
                        </div>
                    </div>


                    <div class="mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control" id="description" name="description">{{ $project->description ?? '' }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary mb-3">Update</button>
                </form>
            </div>
        </div>
    </div>
@endsection
