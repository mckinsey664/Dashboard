@extends('layouts.app')

@section('content')
<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Create Order</h5>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('orders.store') }}">
      @csrf

      <div class="row g-3 align-items-end">
        <div class="col-md-6">
          <label class="form-label">Client</label>
          <div class="input-group">
            <select name="client_id" id="clientSelect" class="form-select" required>
              <option value="">-- Select client --</option>
              @foreach ($clients as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
              @endforeach
            </select>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#newClientModal">+ New</button>
          </div>
          @error('client_id') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3">
          <label class="form-label">Order Code</label>
          <input type="text" name="order_code" class="form-control" required>
          @error('order_code') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3">
          <label class="form-label">Ref</label>
          <input type="text" name="ref" class="form-control" required>
          @error('ref') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3">
          <label class="form-label">Date Received</label>
          <input type="date" name="date_received" class="form-control">
          @error('date_received') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3">
          <label class="form-label">Priority</label>
          <input type="text" name="priority" class="form-control" placeholder="High / Medium / Low">
        </div>

        <div class="col-md-3">
          <label class="form-label">Region</label>
          <input type="text" name="region" class="form-control">
        </div>

        <div class="col-md-12">
          <label class="form-label">Inquiry Mail (notes)</label>
          <textarea name="inquiry_mail" class="form-control" rows="2"></textarea>
        </div>
      </div>

      <hr class="my-4">

      <h6>Items</h6>
      <div class="table-responsive">
        <table class="table" id="items-table">
          <thead>
            <tr>
              <th style="width:22%">Part Number</th>
              <th style="width:22%">Manufacturer</th>
              <th style="width:12%">Qty</th>
              <th style="width:12%">UOM</th>
              <th style="width:18%">Target Price (USD)</th>
              <th style="width:14%"></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><input class="form-control" name="items[0][part_number]"></td>
              <td><input class="form-control" name="items[0][manufacturer]"></td>
              <td><input class="form-control" type="number" step="0.0001" name="items[0][quantity]"></td>
              <td><input class="form-control" name="items[0][uom]" placeholder="pcs"></td>
              <td><input class="form-control" type="number" step="0.0001" name="items[0][target_price_usd]"></td>
              <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">Remove</button></td>
            </tr>
          </tbody>
        </table>
      </div>

      <button type="button" class="btn btn-outline-secondary mb-3" onclick="addRow()">+ Add item</button>

      <div class="d-flex gap-2">
        <a href="{{ route('orders.index') }}" class="btn btn-light">Cancel</a>
        <button type="submit" class="btn btn-primary">Save Order</button>
      </div>
    </form>
  </div>
</div>

{{-- New Client Modal --}}
<div class="modal fade" id="newClientModal" tabindex="-1" aria-labelledby="newClientModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="newClientForm">
      <div class="modal-header">
        <h5 class="modal-title" id="newClientModalLabel">Add New Client</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div id="nc-errors" class="alert alert-danger d-none"></div>

        <div class="mb-3">
          <label class="form-label">Client Name <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Region</label>
          <input type="text" name="region" class="form-control" placeholder="e.g., MENA">
        </div>

        <div class="mb-1">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="client@company.com">
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Client</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Items table helpers
  let rowIdx = 1;
  function addRow() {
    const tbody = document.querySelector('#items-table tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input class="form-control" name="items[${rowIdx}][part_number]"></td>
      <td><input class="form-control" name="items[${rowIdx}][manufacturer]"></td>
      <td><input class="form-control" type="number" step="0.0001" name="items[${rowIdx}][quantity]"></td>
      <td><input class="form-control" name="items[${rowIdx}][uom]" placeholder="pcs"></td>
      <td><input class="form-control" type="number" step="0.0001" name="items[${rowIdx}][target_price_usd]"></td>
      <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">Remove</button></td>
    `;
    tbody.appendChild(tr);
    rowIdx++;
  }
  function removeRow(btn) { btn.closest('tr').remove(); }

  // New Client modal submit (AJAX)
  document.getElementById('newClientForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const form = e.target;
    const errors = document.getElementById('nc-errors');
    errors.classList.add('d-none'); errors.innerHTML = '';

    const payload = {
      name:   form.name.value.trim(),
      region: form.region.value.trim() || null,
      email:  form.email.value.trim() || null,
    };

    const res = await fetch(`{{ route('clients.store') }}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    if (res.ok) {
      const json = await res.json();
      // Add to select and select it
      const sel = document.getElementById('clientSelect');
      const opt = new Option(json.client.name, json.client.id, true, true);
      sel.add(opt);
      // Close modal + reset
      const modalEl = document.getElementById('newClientModal');
      const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      modal.hide();
      form.reset();
    } else if (res.status === 422) {
      const data = await res.json();
      const msgs = Object.values(data.errors || {}).flat().join('<br>');
      errors.innerHTML = msgs || 'Validation error.';
      errors.classList.remove('d-none');
    } else {
      errors.innerHTML = 'Unexpected error. Please try again.';
      errors.classList.remove('d-none');
    }
  });
</script>
@endpush
