<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @hasSection('title') @yield('title') | @elseif(isset($title)) {{ $title }} | @endif {{ config('app.name', 'Laravel') }}
    </title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="{!! asset('assets/plugin/bootstrap/bootstrap.min.css') !!}" rel="stylesheet" type="text/css" />
    <link href="{!! asset('assets/css/app.css') !!}" rel="stylesheet" type="text/css" />
    <link href="{!! asset('assets/plugin/toastify/toastify.min.css') !!}" rel="stylesheet" type="text/css" />
    <link href="{!! asset('assets/plugin/datatables/dataTables.bootstrap5.min.css') !!}" rel="stylesheet" type="text/css" />
    <link href="{!! asset('assets/plugin/flatpickr/flatpickr.min.css') !!}" rel="stylesheet" type="text/css" />
    <link href="{!! asset('assets/plugin/tom-select/tom-select.bootstrap5.min.css') !!}" rel="stylesheet" type="text/css" />
    <link href="{!! asset('assets/css/custom.css') !!}" rel="stylesheet" type="text/css" />

    @stack('styles')
</head>

<body>
    <div id="app">
        <div class="wrapper">
            @include('layouts.sidebar')

            <div class="main">
                @include('layouts.navbar')

                <main class="content">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    <!-- <script src="{!! asset('assets/plugin/bootstrap/bootstrap.min.js') !!}" type="text/javascript"></script> -->
    <script src="{!! asset('assets/js/app.js') !!}" type="text/javascript"></script>
    <script src="{!! asset('assets/js/jquery-3.7.1.min.js') !!}" type="text/javascript"></script>

    <script src="{!! asset('assets/plugin/toastify/toastify.min.js') !!}" type="text/javascript"></script>
    <script src="{!! asset('assets/plugin/sweetalert/sweetalert.min.js') !!}" type="text/javascript"></script>
    <script src="{!! asset('assets/plugin/datatables/dataTables.min.js') !!}" type="text/javascript"></script>
    <script src="{!! asset('assets/plugin/datatables/dataTables.bootstrap5.min.js') !!}" type="text/javascript"></script>
    <script src="{!! asset('assets/plugin/flatpickr/flatpickr.min.js') !!}" type="text/javascript"></script>
    <script src="{!! asset('assets/plugin/tom-select/tom-select.complete.min.js') !!}" type="text/javascript"></script>
    <script src="{!! asset('assets/js/custom.js') !!}" type="text/javascript"></script>
    @stack('script-assets')

    @if (session('success'))
    <script>
        $(document).ready(function() {
            showToast(@json(session('success')), 'success');
        });
    </script>
    @endif

    @if (session('error'))
    <script>
        $(document).ready(function() {
            showToast(@json(session('error')), 'error');
        });
    </script>
    @endif

    @stack('scripts')
</body>

</html>