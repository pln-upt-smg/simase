<!DOCTYPE HTML>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4338CA"/>
    <meta name="keywords" content="Stocktake">
    <meta name="description" content="Item Stock Management System for Unilever Indonesia.">
    <title inertia>{{ config('app.name', 'Stocktake') }}</title>
    <link rel="preconnect" href="{{ config('app.url') }}">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="stylesheet" href="{{ mix('css/app.min.css') }}">
</head>
<body class="font-sans antialiased">
@inertia
</body>
@routes
<script src="{{ mix('js/app.min.js') }}" defer></script>
</html>
