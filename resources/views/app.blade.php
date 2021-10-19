<!DOCTYPE HTML>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'Stocktake') }}</title>
    <link rel="preconnect" href="{{ config('app.url') }}">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
</head>
<body class="font-sans antialiased">
@inertia
</body>
@routes
<script src="{{ mix('js/app.js') }}" defer></script>
</html>
