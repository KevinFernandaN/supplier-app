<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Supplier App')</title>

    <!-- Bootstrap CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">Supplier App</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto">

                {{-- Boss --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Boss</a>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li><a class="dropdown-item" href="{{ route('boss.dashboard') }}">Dashboard</a></li>
                        <li><a class="dropdown-item" href="{{ route('boss.best-suppliers.index') }}">Best Suppliers</a></li>
                        <li><a class="dropdown-item" href="{{ route('boss.complaints.index') }}">Complaint Analytics</a></li>
                        <li><a class="dropdown-item" href="{{ route('boss.returns.index') }}">Return Analytics</a></li>
                    </ul>
                </li>

                {{-- Operations --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Operations</a>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li><a class="dropdown-item" href="{{ route('purchase-requests.index') }}">Purchase Requests</a></li>
                        <li><a class="dropdown-item" href="{{ route('purchase-orders.index') }}">Purchase Orders</a></li>
                        <li><a class="dropdown-item" href="{{ route('receivings.index') }}">Receiving</a></li>
                        <li><a class="dropdown-item" href="{{ route('menus.index') }}">Menus</a></li>
                        <li><a class="dropdown-item" href="{{ route('sales-orders.index') }}">Sales Orders</a></li>
                        <li><a class="dropdown-item" href="{{ route('rabs.index') }}">RAB</a></li>
                    </ul>
                </li>

                {{-- Master Data --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Master Data</a>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li><a class="dropdown-item" href="{{ route('products.index') }}">Products</a></li>
                        <li><a class="dropdown-item" href="{{ route('suppliers.index') }}">Suppliers</a></li>
                        <li><a class="dropdown-item" href="{{ route('returns.index') }}">Return Orders</a></li>
                        <li><a class="dropdown-item" href="{{ route('unit-conversions.index') }}">Unit Conversions</a></li>
                        <li><a class="dropdown-item" href="{{ route('certifications.index') }}">Certifications</a></li>
                        <li><a class="dropdown-item" href="{{ route('kitchens.index') }}">Kitchens</a></li>
                    </ul>
                </li>

                {{-- Reports --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Reports</a>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li><a class="dropdown-item" href="{{ route('reports.margin.daily') }}">Daily Margin</a></li>
                        <li><a class="dropdown-item" href="{{ route('reports.margin.monthly') }}">Monthly Margin</a></li>
                    </ul>
                </li>

            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold">Please fix the errors below:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<!-- Bootstrap JS (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
