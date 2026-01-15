{{-- resources/views/sheet_stats.blade.php --}}
@extends('layouts.app')

@section('content')
<style>
  h1 { font-size: 28px; font-weight: bold; margin: 20px 0; }
  .container { display: flex; align-items: center; margin-top: 20px; }
  .bars { display: flex; align-items: flex-end; margin-right: 60px; }
  .bar {
    width: 150px;
    margin-right: 20px;
    display: flex;
    justify-content: center;
    align-items: flex-end;
    text-align: center;
    color: white;
    font-weight: bold;
    padding-bottom: 10px;
  }
  .gray   { background: linear-gradient(to right, #777, #555); }
  .orange { background: linear-gradient(to right, #f57c00, #e65100); }
  .description { max-width: 450px; }
  .description h2 { color: #b3541e; font-size: 20px; }
  #pdf-content {
    background: #fff;
    border: 1px solid #eee;
    border-radius: .5rem;
    padding: 16px;
  }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">RFQ Dashboard</h4>
  <button class="btn btn-danger" onclick="exportToPDF()">📄 Export to PDF</button>
</div>

<div id="pdf-content">

{{-- ========================================================= --}}
{{-- 1️⃣ OVERVIEW --}}
{{-- ========================================================= --}}
<h1>Overview of the Quotation Performance</h1>

@php
  $totalReceived = $results[0]['filled_count'] ?? 0;
  $totalQuoted   = $results[1]['filled_count'] ?? 0;
  $percentage    = $totalReceived > 0
                    ? round(($totalQuoted / $totalReceived) * 100, 1)
                    : 0;
@endphp

<div class="container">
  <div class="bars">
    <div class="bar gray" style="height:200px;">
      <div>Total RFQs<br>{{ number_format($totalReceived) }}</div>
    </div>
    <div class="bar orange" style="height:{{ 200 * ($percentage / 100) }}px;">
      <div>Quoted<br>{{ number_format($totalQuoted) }}</div>
    </div>
  </div>

  <div class="description">
    <h2>Description</h2>
    <p>
      From January 1 to December 31, 2025, the team received
      <b>{{ number_format($totalReceived) }}</b> RFQ items and successfully
      quoted <b>{{ $percentage }}%</b> of them
      (<b>{{ number_format($totalQuoted) }}</b> items).
    </p>
  </div>
</div>

<hr>

{{-- ========================================================= --}}
{{-- 2️⃣ COMPONENT TYPE BREAKDOWN --}}
{{-- ========================================================= --}}
<h1>Breakdown of RFQs Items by Component Type</h1>

@php
  $activeCount  = $activeCount ?? 0;
  $passiveCount = $passiveCount ?? 0;
  $total        = $activeCount + $passiveCount;
@endphp

<div class="container">
  <div class="bars">
    <div class="bar gray" style="height:200px;">
      <div>Active<br>{{ number_format($activeCount) }}</div>
    </div>
    <div class="bar orange" style="height:200px;">
      <div>Passive<br>{{ number_format($passiveCount) }}</div>
    </div>
  </div>

  <div class="description">
    <h2>Description</h2>
    <p>
      Active components represent
      <b>{{ $total > 0 ? round(($activeCount / $total) * 100, 1) : 0 }}%</b>
      of RFQs, while passive components represent
      <b>{{ $total > 0 ? round(($passiveCount / $total) * 100, 1) : 0 }}%</b>.
    </p>
  </div>
</div>

<hr>

{{-- ========================================================= --}}
{{-- 3️⃣ SUPPLIER PREFERENCES --}}
{{-- ========================================================= --}}
<h1>Suppliers’ Quoting Preferences by Category</h1>

<table class="table table-bordered">
  <thead class="table-dark">
    <tr>
      <th>Supplier</th>
      <th>Best Category</th>
      <th>2nd Best</th>
      <th>3rd Best</th>
    </tr>
  </thead>
  <tbody>
    @forelse($supplierPreferences as $pref)
      <tr>
        <td>{{ $pref['supplier'] }}</td>
        <td>{{ $pref['best'] }}</td>
        <td>{{ $pref['second'] }}</td>
        <td>{{ $pref['third'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="4" class="text-center text-muted">
          No supplier preference data available
        </td>
      </tr>
    @endforelse
  </tbody>
</table>

<hr>

{{-- ========================================================= --}}
{{-- 4️⃣ MOST REQUESTED CATEGORIES --}}
{{-- ========================================================= --}}
<h1>Most Requested Categories (Top 3 Clients)</h1>

<table class="table table-bordered">
  <thead class="table-dark">
    <tr>
      <th>Category</th>
      <th>Top Client</th>
      <th>Second</th>
      <th>Third</th>
    </tr>
  </thead>
  <tbody>
    @forelse($topClientsPerCategory as $row)
      <tr>
        <td>{{ $row['category'] }}</td>
        <td>{{ $row['top1'] }}</td>
        <td>{{ $row['top2'] }}</td>
        <td>{{ $row['top3'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="4" class="text-center text-muted">
          No category data available
        </td>
      </tr>
    @endforelse
  </tbody>
</table>

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function exportToPDF() {
  html2pdf().from(document.getElementById('pdf-content')).save('RFQ_Dashboard.pdf');
}
</script>
@endpush
