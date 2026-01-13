@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Order #{{ $order->id }} — {{ $order->order_code }}</h5>
  <a href="{{ route('orders.index') }}" class="btn btn-sm btn-secondary">Back</a>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header"><strong>Order Info</strong></div>
      <div class="card-body">
        <div><strong>Ref:</strong> {{ $order->ref }}</div>
        <div><strong>Client:</strong> {{ optional($order->client)->name ?? '-' }}</div>
        <div><strong>Date Received:</strong> {{ $order->date_received?->format('Y-m-d') ?? '-' }}</div>
        <div><strong>Priority:</strong> {{ $order->priority ?? '-' }}</div>
        <div><strong>Region:</strong> {{ $order->region ?? '-' }}</div>
        <div><strong>Overall Code:</strong> {{ $order->overall_code ?? '-' }}</div>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header"><strong>Notes</strong></div>
      <div class="card-body">
        <div class="mb-2"><strong>Inquiry Mail:</strong><br>{{ $order->inquiry_mail ?? '-' }}</div>
        <div class="mb-2"><strong>Notes to Purchasing:</strong><br>{{ $order->notes_to_purchasing ?? '-' }}</div>
        <div class="mb-2"><strong>Notes to Elias:</strong><br>{{ $order->notes_to_elias ?? '-' }}</div>
      </div>
    </div>
  </div>
</div>

<div class="card mt-4">
  <div class="card-header"><strong>Items</strong></div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th>Part Number</th>
          <th>Manufacturer</th>
          <th>Qty</th>
          <th>UOM</th>
          <th>Target Price (USD)</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($order->items as $idx => $it)
          <tr>
            <td>{{ $idx+1 }}</td>
            <td>{{ $it->part_number ?? '-' }}</td>
            <td>{{ $it->manufacturer ?? '-' }}</td>
            <td>{{ $it->quantity ?? '-' }}</td>
            <td>{{ $it->uom ?? '-' }}</td>
            <td>{{ $it->target_price_usd ?? '-' }}</td>
            <td>{{ $it->item_notes ?? '-' }}</td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center text-muted p-3">No items.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
