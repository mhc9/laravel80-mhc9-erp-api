<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- csrf token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>ศูนย์สุขภาพจิตที่ 9 :: Mental Health Center 9</title>

        <!-- Icon -->
        <!-- <link href="{{ asset('img/favicon.ico') }}" rel="icon"> -->

        <!-- Fonts -->
        <!-- <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet"> -->

        <!-- styles  -->
        <!-- <link href="{{ asset('css/style.css') }}" rel="stylesheet">
        <link href="{{ asset('css/bootstrap-icons.css') }}" rel="stylesheet"> -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    </head>

    <body>
        <div id="root"></div>
        <!-- <script src="{{ asset('js/tinymce.min.js') }}"></script>
        <script src="{{ asset('js/main.js') }}"></script> -->
        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>
