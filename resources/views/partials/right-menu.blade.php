@php
  // Helpers for "active" state
  $isOrders      = request()->routeIs('orders.*');
  $isOrdersList  = request()->routeIs('orders.index');
  $isOrdersNew   = request()->routeIs('orders.create');
  $isRfqPo       = request()->routeIs('rfq-po');
  $isStats       = request()->routeIs('stats.page');
  $isReportPO    = request()->is('report');
  $isReportRFQ   = request()->is('report2');
  $isSheetStats  = request()->is('sheet-stats');
@endphp

<div class="card shadow-sm">
  <div class="card-header bg-white">
    <div class="fw-semibold"><i class="bi bi-speedometer2 me-1"></i> Quick Menu</div>
  </div>

  <div class="list-group list-group-flush">

    <!-- <div class="list-group-item text-uppercase small text-muted pb-1">Orders</div>
    <a href="{{ route('orders.index') }}"
       class="list-group-item list-group-item-action {{ $isOrdersList ? 'active' : '' }}">
      <i class="bi bi-bag me-2"></i> Orders List
    </a>
    <a href="{{ route('orders.create') }}"
       class="list-group-item list-group-item-action {{ $isOrdersNew ? 'active' : '' }}">
      <i class="bi bi-plus-circle me-2"></i> New Order
    </a> -->

    <div class="list-group-item text-uppercase small text-muted pb-1 mt-2">Dashboard</div>
    <a href="{{ url('/sheet-stats') }}"
       class="list-group-item list-group-item-action {{ $isSheetStats ? 'active' : '' }}">
      <i class="bi bi-bar-chart-line me-2"></i> RFQ Dashboard
    </a>
    <a href="{{ route('rfq-po') }}"
       class="list-group-item list-group-item-action {{ $isRfqPo ? 'active' : '' }}">
      <i class="bi bi-diagram-3 me-2"></i> RFQ → PO Conversion
    </a>
    <a href="{{ route('rfq-po') }}"
       class="list-group-item list-group-item-action {{ $isReportPO ? 'active' : '' }}">
      <i class="bi bi-bar-chart-line me-2"></i> PO Dashboard
    </a>

    <div class="list-group-item text-uppercase small text-muted pb-1 mt-2">Account</div>
    @auth
      <a href="{{ route('welcome') }}" class="list-group-item list-group-item-action">
        <i class="bi bi-person-circle me-2"></i> Home
      </a>
      <a href="{{ route('logout') }}" class="list-group-item list-group-item-action">
        <i class="bi bi-box-arrow-right me-2"></i> Logout
      </a>
    @else
      <a href="{{ route('login') }}" class="list-group-item list-group-item-action">
        <i class="bi bi-box-arrow-in-right me-2"></i> Login
      </a>
      <a href="{{ route('signup.form') }}" class="list-group-item list-group-item-action">
        <i class="bi bi-person-plus me-2"></i> Sign Up
      </a>
    @endauth
  </div>
</div>
