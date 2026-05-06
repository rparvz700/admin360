@extends(($embedded ?? false) ? 'Partials.iframe' : 'Partials.app', ['activeMenu' => 'generic-documents'])

@section('title')
    {{ config('app.name') }} 
@endsection

@section('scripts')
    <link href="{{ asset('js/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        window.allDocumentAttributes = @json($attributes);
        window.oldAttributeValues = @json($oldAttributeValues);

        @if (!($lockDocumentable ?? false))
            $('#documentable_type').on('change', function () {
                var type = $(this).val();
                $('#documentable_id').empty().append('<option value="">Loading...</option>');
                $('#documentable-id-wrapper').show();

                if (type) {
                    $.ajax({
                        url: '{{ route("documentable.fetch") }}',
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
        @endif
    </script>
    <script src="{{ asset('js/generic-document-attribute-fields.js') }}"></script>
@endsection

@section('page_title')
    Add Generic Document
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            @unless ($embedded ?? false)
                <div class="block-header block-header-default">
                    <h3 class="block-title">Add Generic Document</h3>
                    <a href="{{ route('generic-documents.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
                </div>
            @endunless
            <div class="block-content">
                <form action="{{ route('generic-documents.store') }}" method="POST" autocomplete="off" enctype="multipart/form-data">
                    @csrf
                    @if ($embedded ?? false)
                        <input type="hidden" name="embedded" value="1">
                    @endif
                    <div class="row">
                        <div class="{{ ($embedded ?? false) ? 'col-12' : 'col-md-8' }}">
                            @include('GenericDocumentManagement.GenericDocument.form', [
                                'doc' => null,
                                'documentableTypes' => $documentableTypes,
                                'documentables' => $documentables,
                                'categories' => $categories,
                                'selectedDocumentableType' => $selectedDocumentableType,
                                'selectedDocumentableId' => $selectedDocumentableId,
                                'lockDocumentable' => $lockDocumentable,
                            ])
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
@endsection
