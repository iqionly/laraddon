<h1>Profiler</h1>
<h2>Registered Routes</h2>
<ul>
@foreach ($routes as $route)
    @if ($route)
        <li>{{ $route->uri() }} - {{ implode(', ', $route->methods()) }}</li>
    @endif
@endforeach
</ul>