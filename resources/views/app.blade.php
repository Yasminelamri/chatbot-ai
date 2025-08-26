<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>Chatbot</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @inertiaHead
</head>
<body>
    @inertia
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
