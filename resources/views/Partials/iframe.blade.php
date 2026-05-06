<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    @yield('styles')
    <link rel="stylesheet" id="css-main" href="{{ asset('css/oneui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    <main id="main-container">
        @yield('content')
    </main>

    <script src="{{ asset('js/lib/jquery.min.js') }}"></script>
    <script src="{{ asset('js/oneui.app.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2/js/select2.full.js') }}"></script>
    <script>One.helpersOnLoad(['jq-select2', 'jq-notify']);</script>
    @yield('scripts')
</body>

</html>
