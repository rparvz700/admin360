@extends('Partials.app', ['activeMenu' => 'generic-documents'])

@section('title')
    {{ config('app.name') }} 
@endsection

@section('scripts')
    <link href="{{ asset('js/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        window.allDocumentAttributes = @json($attributes);
        window.oldAttributeValues = @json($oldAttributeValues);
    </script>
    <script>
        window.allDocumentAttributes = @json($attributes);
        window.oldAttributeValues = @json($oldAttributeValues);

        $('#documentable_type').on('change', function () {
            var type = $(this).val();
            $('#documentable_id').empty().append('<option value="">Loading...</option>');
            $('#documentable-id-wrapper').show();

            if (type) {
                $.ajax({
                    url: '{{ route("documentable.fetch") }}', // We'll create this route
                    data: { type: type },
                    success: function (data) {
                        $('#documentable_id').empty().append('<option value="">Select record</option>');
                        $.each(data, function (id, name) {
                            $('#documentable_id').append('<option value="' + id + '">' + name + '</option>');
                        });
                    }
                });
            } else {
                $('#documentable-id-wrapper').hide();
            }
        });
    </script>
    <script src="{{ asset('js/generic-document-attribute-fields.js') }}"></script>
@endsection

@section('page_title')
    Edit Generic Document
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Edit Generic Document</h3>
                <a href="{{ route('generic-documents.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
            </div>
            <div class="block-content">
                <form action="{{ route('generic-documents.update', $doc->id) }}" method="POST" enctype="multipart/form-data" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-8">
                            @include('GenericDocumentManagement.GenericDocument.form', ['doc' => $doc, 'documentables' => $documentables, 'categories' => $categories])
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('generic-documents.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
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


