<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <meta name="description" content=""/>
    <meta name="author" content=""/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Image Process</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <!-- Fonts -->
    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,400i,300,700" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="{{ asset('bower_components/jquery-ui/themes/base/jquery-ui.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('bower_components/bootstrap/dist/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendors/toast-master/css/jquery.toast.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendors/toastr/css/toastr.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendors/dropzone/dropzone.css') }}">

    <!-- Header Script -->
    @stack('header-script')
    <!-- End Header Script -->

</head>
<body class="sb-nav-fixed">
    @yield('contents')

    <script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('bower_components/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('vendors/@popperjs/core/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('vendors/froiden-helper/helper.js') }}"></script>
    <script src="{{ asset('vendors/toast-master/js/jquery.toast.js') }}"></script>
    <script src="{{ asset('vendors/toastr/js/toastr.js') }}"></script>
    <script src="{{ asset('vendors/dropzone/dropzone.js') }}"></script>
    <script type="text/javascript">

        $(document).ready(function() {

            @if($errors->any())
            toastr.error('<strong>Error:</strong> Please check the form below for errors');
            @endif

            @if($message = Session::get('success'))
            toastr.success('{{ $message }}');
            @endif

            @if($message = Session::get('error'))
            toastr.error('{{ $message }}');
            @endif

            @if($message = Session::get('warning'))
            toastr.warning('{{ $message }}');
            @endif

            @if($message = Session::get('info'))
            toastr.info('{{ $message }}');
            @endif

            @if($message = Session::get('msg'))
            toastr.error('{{ $message }}');
            @endif
        });

    </script>

    <!-- Footer Script -->
    @stack('footer-script')
    <!-- End Footer Script -->
</body>
</html>
