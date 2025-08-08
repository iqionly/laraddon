<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laraddon | Profiler</title>

    <!-- Minified version -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <style>
        .bg-get {
            background-color: var(--bs-success) !important;
        }

        .bg-post {
            background-color: var(--bs-info) !important;
        }

        .bg-delete {
            background-color: var(--bs-danger) !important;
        }

        .bg-put {
            background-color: var(--bs-secondary) !important;
        }

        .bg-patch {
            background-color: var(--bs-secondary) !important;
        }

        .bg-options {
            background-color: var(--bs-light) !important;
        }

        .bg-head {
            background-color: var(--bs-dark) !important;
            color: white !important;
        }
    </style>
</head>
<body>

    <div class="container">
        <header>
            <h1>Laraddon</h1>
            <p>Profiler</p>
        </header>   
        <main>
            <h3>Registered Routes</h3>
            <ul class="list-group">
            @foreach ($routes as $route)
                @if ($route)
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto col-7">
                            <a href="{{ url($route->uri()) }}" class="fw-bold">{{ $route->uri() }}</a>
                        </div>
                        <div class="col-3">
                            @foreach($route->methods() as $method)
                                <span class="badge bg-primary rounded-pill bg-{{ strtolower($method) }}">{{ $method }}</span>
                            @endforeach
                        </div>
                    </li>
                @endif
            @endforeach
            </ul>
        </main>

        <footer>
            Laraddon &copy; {{ date('Y') }}. All rights reserved.
        </footer>
    </div>

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>

