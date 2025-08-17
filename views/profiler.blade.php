<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laraddon | Profiler</title>

    <!-- Minified version -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style type="text/tailwindcss">
        
    </style>
</head>
<body class="mx-8 mt-8 mb-2">
    <header class="mt-3">
        <h1 class="text-2xl font-bold">Laraddon</h1>
        <p class="text-sm/6 text-gray-600">Profiler</p>
    </header>
    <main class="flex flex-col border rounded-md border-gray-200 shadow-md mt-8">
        <x-laraddon::list-routes />
    </main>

    <footer class="mt-8">
        Laraddon &copy; {{ date('Y') }}. All rights reserved.
    </footer>

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>

