<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#10111d">
    <title inertia>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css','resources/js/app.ts'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
