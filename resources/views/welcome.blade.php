<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel Simple</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 antialiased min-h-screen flex flex-col justify-center items-center">

    @if (Route::has('login'))
        <div class="absolute top-6 right-6 space-x-4">
            @auth
                <a href="{{ url('/home') }}" class="text-gray-700 dark:text-gray-200 font-semibold hover:underline">Home</a>
            @else
                <a href="{{ route('login') }}" class="text-gray-700 dark:text-gray-200 font-semibold hover:underline">Login</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="text-gray-700 dark:text-gray-200 font-semibold hover:underline">Register</a>
                @endif
            @endauth
        </div>
    @endif

    <div class="text-center space-y-6">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Welcome to Laravel</h1>
        <p class="text-gray-600 dark:text-gray-300">A simple, clean, and responsive starter page using TailwindCSS.</p>

        <div class="flex justify-center gap-4">
            <a href="https://laravel.com/docs" class="px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">Docs</a>
            <a href="https://laracasts.com" class="px-6 py-3 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition">Laracasts</a>
        </div>
    </div>

</body>
</html>
