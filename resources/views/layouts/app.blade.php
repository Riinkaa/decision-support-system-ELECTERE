<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK ELECTRE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Anda bisa menambahkan styling kustom di sini jika diperlukan */
    </style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <nav class="bg-blue-600 p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <a href="{{ route('decision_cases.index') }}" class="text-white text-2xl font-bold">SPK ELECTRE</a>
            <div>
                </div>
        </div>
    </nav>

    <div class="container mx-auto mt-8 px-4">
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Sukses!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>