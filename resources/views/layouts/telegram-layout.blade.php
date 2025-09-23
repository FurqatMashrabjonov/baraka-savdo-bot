<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>

    {{-- Include Sheaf UI CSS --}}
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-screen">
<main class="w-full max-w-md mx-auto p-4 sm:p-6 bg-white min-h-screen pb-20">
    @yield('content')
</main>

{{-- Include Sheaf UI JavaScript --}}
@vite(['resources/js/app.js'])
</body>
</html>
