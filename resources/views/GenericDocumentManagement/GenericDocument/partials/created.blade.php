<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
</head>

<body>
    <script>
        window.parent.postMessage({
            type: 'documentCreated',
            id: @json($doc->id),
            label: @json($label),
            directlyAttached: @json($directlyAttached ?? false),
        }, window.location.origin);
    </script>
</body>

</html>
