<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'STOKGX') }}</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light text-dark antialiased">
        <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center pt-4">
            <div class="w-100 mt-4 px-4 py-4 bg-white shadow-sm rounded-3" style="max-width: 480px;">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>

