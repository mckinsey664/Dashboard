@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Orders</h5>
  <a href="{{ route('orders.create') }}" class="btn btn-primary btn-sm">Create Order</a>
</div>

@if ($orders->count() === 0)
  <div class="alert alert-light border">
    No orders yet. Click <strong>Create Order</strong> to add one.
  </div>
@else
  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Order Code</th>
            <th>Ref</th>
            <th>Client</th>
            <th>Date Received</th>
            <th>Priority</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($orders as $o)
            <tr>
              <td>{{ $o->id }}</td>
              <td>{{ $o->order_code }}</td>
              <td>{{ $o->ref }}</td>
              <td>{{ optional($o->client)->name ?? '-' }}</td>
              <td>{{ $o->date_received ? \Illuminate\Support\Carbon::parse($o->date_received)->format('Y-m-d') : '-' }}</td>
              <td>{{ $o->priority ?? '-' }}</td>
              <td>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('orders.show', $o) }}">View</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">
    {{ $orders->links() }}
  </div>
@endif
@endsection
