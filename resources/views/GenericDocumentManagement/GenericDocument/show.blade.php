@extends('Partials.app', ['activeMenu' => 'generic-documents'])

@section('title')
    {{ config('app.name') }} 
@endsection

@section('page_title')
    Generic Document Details
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Generic Document Details</h3>
                <a href="{{ route('generic-documents.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
            </div>
            <div class="block-content">
                <table class="table table-bordered">
                    <tbody>
                        <tr><th>Documentable Type</th><td>{{ $typeLabel ?? '' }}</td></tr>
                        <tr><th>Documentable</th><td>{{ $primaryText ?? '' }}</td></tr>
                        <tr><th>Category</th><td>{{ $doc->category->category_name ?? '' }}</td></tr>
                        <tr><th>Issue Date</th><td>{{ $doc->issue_date }}</td></tr>
                        <tr><th>Expiry Date</th><td>{{ $doc->expiry_date }}</td></tr>
                        <tr><th>File</th><td>
                            @if($doc->file_path)
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


                                <div class="modal fade" id="filePreviewModal" tabindex="-1" aria-labelledby="filePreviewModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-xl modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">File Preview</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                @if(!empty($doc->file_path))
                                                    @php
                                                        $fileExt = pathinfo($doc->file_path, PATHINFO_EXTENSION);
                                                    @endphp
                                                    @if(in_array(strtolower($fileExt), ['jpg','jpeg','png','gif','webp']))
                                                        <div id="zoomContainer" style="overflow:hidden; cursor: grab;">
                                                            <img id="zoomImage" src="{{ asset('storage/'.$doc->file_path) }}" 
                                                                alt="Preview" class="img-fluid" style="max-height: 80vh;">
                                                        </div>
                                                    @elseif(strtolower($fileExt) === 'pdf')
                                                        <iframe src="{{ asset('storage/'.$doc->file_path) }}" width="100%" height="600px"></iframe>
                                                    @else
                                                        <p>No preview available</p>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif


                        </td></tr>
                    </tbody>
                </table>
                <h5 class="mt-4">Custom Fields</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Field Name</th>
                            <th>Type</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($doc->attributeValues as $attrValue)
                            <tr>
                                <td>{{ $attrValue->attribute->attribute_name ?? '' }}</td>
                                <td>{{ $attrValue->attribute->attribute_type ?? '' }}</td>
                                <td>
                                    @if(($attrValue->attribute->attribute_type ?? '') === 'boolean')
                                        {{ $attrValue->value == '1' ? 'Yes' : 'No' }}
                                    @else
                                        {{ $attrValue->value }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const img = document.getElementById('zoomImage');
    const container = document.getElementById('zoomContainer');

    if (!img || !container) return;

    let scale = 1;
    let isPanning = false;
    let startX = 0, startY = 0;
    let translateX = 0, translateY = 0;

    img.ondragstart = () => false;

    // Mouse wheel zoom
    container.addEventListener('wheel', e => {
        e.preventDefault();
        const delta = e.deltaY * -0.001;
        scale = Math.min(Math.max(1, scale + delta), 4);
        updateTransform();
    });

    // Pan start
    container.addEventListener('mousedown', e => {
        e.preventDefault();
        isPanning = true;
        container.style.cursor = 'grabbing';
        startX = e.clientX;
        startY = e.clientY;
    });

    // Pan end
    const endPan = () => {
        isPanning = false;
        container.style.cursor = 'grab';
    };
    container.addEventListener('mouseup', endPan);
    container.addEventListener('mouseleave', endPan);

    // Pan move
    container.addEventListener('mousemove', e => {
        if (!isPanning) return;
        e.preventDefault();
        const dx = (e.clientX - startX) / scale;
        const dy = (e.clientY - startY) / scale;
        translateX += dx;
        translateY += dy;
        startX = e.clientX;
        startY = e.clientY;
        updateTransform();
    });

    // Double-click toggle 1x ↔ 2x
    img.addEventListener('dblclick', () => {
        if (scale !== 2) {
            scale = 2;
            translateX = 0;
            translateY = 0;
        } else {
            scale = 1;
            translateX = 0;
            translateY = 0;
        }
        updateTransform();
    });

    // Reset on modal close
    const modal = container.closest('.modal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', () => {
            scale = 1;
            translateX = 0;
            translateY = 0;
            updateTransform();
        });
    }

    function updateTransform() {
        img.style.transform = `scale(${scale}) translate(${translateX}px, ${translateY}px)`;
    }
});

</script>
@endsection
@section('styles')
<style>
    #zoomImage {
        transition: transform 0.2s ease-out; /* smooth transition for zoom and pan */
        transform: scale(1) translate(0px, 0px); /* initial state */
        cursor: grab;
        user-select: none; /* prevent text selection */
    }
    #zoomContainer {
        /* overflow: hidden;
        display: inline-block; */
    }
</style>
@endsection


