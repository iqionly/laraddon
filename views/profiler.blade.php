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

        <div class="w-auto flex-1 px-8 pt-4 pb-3 border-b-1 border-gray-200">
            <h3 class="text-lg/6">Registered Routes</h3>
        </div>
        
        <ul role="list" class="flex-1 pb-4 divide-y divide-gray-100">
        @foreach ($routes as $route)
            @if ($route)
            <li class="px-8 pt-3 pb-3 justify-between gap-x-6 py-5">
                <div class="flex min-w-0 gap-x-4">
                    <div class="min-w-0 flex-auto">
                        <p class="text-sm/6"><a href="{{ $route->uri() }}" class="text-blue-600 visited:text-purple-600 hover:text-blue-800 target:shadow-lg">{{ url($route->uri()) }}</a></p>
                    </div>
                    <div class="w-30 flex-wrap text-xs/5 text-gray-400">
                    @foreach($route->methods() as $method)
                        @if($method == 'GET')
                        <span class="items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 inset-ring inset-ring-green-600/20">{{ $method }}</span>
                        @elseif($method == 'POST')
                        <span class="items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 inset-ring inset-ring-blue-600/20">{{ $method }}</span>
                        @elseif($method == 'PUT')
                        <span class="items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 inset-ring inset-ring-yellow-600/20">{{ $method }}</span>
                        @elseif($method == 'PATCH')
                        <span class="items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 inset-ring inset-ring-yellow-600/20">{{ $method }}</span>
                        @elseif($method == 'DELETE')
                        <span class="items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 inset-ring inset-ring-red-600/20">{{ $method }}</span>
                        @elseif($method == 'HEAD')
                        <span class="items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 inset-ring inset-ring-gray-600/20">{{ $method }}</span>
                        @elseif($method == 'OPTIONS')
                        <span class="items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 inset-ring inset-ring-gray-600/20">{{ $method }}</span>
                        @endif
                    @endforeach
                    </div>    
                </div>
            </li>
            @endif
        @endforeach
        </ul>

    </main>

    <footer class="mt-8">
        Laraddon &copy; {{ date('Y') }}. All rights reserved.
    </footer>

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>

