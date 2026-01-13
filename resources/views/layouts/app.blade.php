<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>McKinsey Electronics</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Bootstrap & Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    /* Make the right panel sticky on large screens */
    .right-panel-sticky {
      position: sticky;
      top: 1rem;
    }
    body { background-color: #f7f7f7; }
  </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand fw-semibold" href="{{ route('orders.index') }}">McKinsey Electronics</a>

    <div class="d-flex align-items-center gap-2">

      {{-- Toggle for mobile right panel --}}
      <button class="btn btn-outline-light btn-sm d-lg-none" type="button"
              data-bs-toggle="offcanvas" data-bs-target="#rightMenuOffcanvas" aria-controls="rightMenuOffcanvas">
        <i class="bi bi-list"></i> Menu
      </button>
    </div>
  </div>
</nav>

<div class="container-fluid py-3">
  <div class="row g-3">
    {{-- MAIN CONTENT --}}
    <main class="col-12 col-lg-9">
      @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
      @endif
      @yield('content')
    </main>

    {{-- RIGHT SIDEBAR (desktop and up) --}}
    <aside class="col-12 col-lg-3 d-none d-lg-block">
      <div class="right-panel-sticky">
        @include('partials.right-menu')
      </div>
    </aside>
  </div>
</div>

{{-- RIGHT OFFCANVAS (mobile/tablet) --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="rightMenuOffcanvas" aria-labelledby="rightMenuLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="rightMenuLabel">Quick Menu</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    @include('partials.right-menu')
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
