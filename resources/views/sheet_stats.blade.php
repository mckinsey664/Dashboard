{{-- resources/views/sheet_stats.blade.php --}}
@extends('layouts.app')

@section('content')
<style>
  body { padding: 0; } /* layout already handles fonts */
  h1 { font-size: 28px; font-weight: bold; padding: 10px 0; }
  .container { display: flex; align-items: center; margin-top: 20px; }
  .bars { display: flex; align-items: flex-end; margin-right: 60px; }
  .bar { width: 150px; margin-right: 20px; display: flex; justify-content: center;
         align-items: flex-end; text-align: center; color: white; font-weight: bold;
         padding-bottom: 10px; position: relative; }
  .gray   { background: linear-gradient(to right, #777, #555); }
  .orange { background: linear-gradient(to right, #f57c00, #e65100); }
  .description { max-width: 450px; }
  .description h2 { color: #b3541e; font-size: 20px; margin-bottom: 10px; }
  .description p { font-size: 16px; line-height: 1.5; }
  .description b { font-size: 18px; }
  /* minor tweak so content breathes inside app layout */
  #pdf-content { background: #fff; border: 1px solid #eee; border-radius: .5rem; padding: 16px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">RFQ Dashboard</h4>
  <button class="btn btn-danger" onclick="exportToPDF()">📄 Export to PDF</button>
</div>

<div id="pdf-content">
<h1>Overview of the Quotation Performance</h1>

<div class="container">
    <div class="bars">
        @php
            $totalReceived = $results[0]['filled_count']; // Items for Quotation
            $totalQuoted = $results[1]['filled_count'];   // Quoted Items
            $maxHeight = 300; // px
            $receivedHeight = $totalReceived / max($totalReceived, $totalQuoted) * $maxHeight;
            $quotedHeight = $totalQuoted / max($totalReceived, $totalQuoted) * $maxHeight;
            $percentage = $totalReceived > 0 ? round(($totalQuoted / $totalReceived) * 100, 1) : 0;
        @endphp

        <div class="bar gray" style="height: {{ $receivedHeight }}px;">
            <div>Total number<br>of RFQs<br>items received</div>
        </div>

        <div class="bar orange" style="height: {{ $quotedHeight }}px;">
            <div>Total Number<br>of RFQs<br>items quoted</div>
        </div>
    </div>

    <div class="description">
        <h2>Description</h2>
        <p>
            Between January 1 and  December 31 2025, we received around 
            <b>{{ number_format($totalReceived) }} items</b> for quotation. 
            The team successfully quoted and shared 
            <b>{{ $percentage }}%</b>, totaling around 
            <b>{{ number_format($totalQuoted) }} items</b>.
        </p>
    </div>
</div>

<hr style="margin: 40px 0;">

<h1>Breakdown of RFQs items by Component Type</h1>

<div class="container" style="display: flex; align-items: center;">
    <div class="bars" style="display: flex; align-items: flex-end; margin-right: 60px;">
        @php
            $maxHeight2 = 300; // px
            $activeHeight = $activeCount / max($activeCount, $passiveCount) * $maxHeight2;
            $passiveHeight = $passiveCount / max($activeCount, $passiveCount) * $maxHeight2;
            $totalComponents = $activeCount + $passiveCount;
            $activePercentage = $totalComponents > 0 ? round(($activeCount / $totalComponents) * 100, 1) : 0;
            $passivePercentage = $totalComponents > 0 ? round(($passiveCount / $totalComponents) * 100, 1) : 0;
        @endphp

        <!-- Active Components Bar -->
<div class="bar gray"
     title="{{ number_format($activeCount) }} Active Components"
     style="width:150px;height:{{ $activeHeight }}px;display:flex;align-items:flex-end;justify-content:center;padding-bottom:10px;color:white;font-weight:bold;">
    <div>
        Active<br>
        {{ number_format($activeCount) }}
    </div>
</div>


        <!-- Passive Components Bar -->
<div class="bar orange"
     title="{{ number_format($passiveCount) }} Passive Components"
     style="width:150px;height:{{ $passiveHeight }}px;display:flex;align-items:flex-end;justify-content:center;padding-bottom:10px;color:white;font-weight:bold;margin-left:20px;">
    <div>
        Passive<br>
        {{ number_format($passiveCount) }}
    </div>
</div>

    </div>

    <div class="description" style="max-width: 450px;">
        <h2 style="color: #b3541e;">Description</h2>
        <p>
            Of the requested items, <b>{{ $activePercentage }}%</b> are active components,
            while <b>{{ $passivePercentage }}%</b> are passive components.
        </p>
    </div>
</div>



<hr style="margin: 40px 0;">

<h1>Breakdown of RFQs by top 20 Clients</h1>

<div style="display: flex; align-items: flex-start;">
    <div style="flex: 1;">
        @php
            $maxValue = max(array_column($topClientsData, 'total'));
        @endphp
        @foreach($topClientsData as $client)
            @php
                $barWidth = ($client['total'] / $maxValue) * 100;
            @endphp
            <div style="display: flex; align-items: center; margin-bottom: 6px;">
                <div style="width: 250px; font-size: 14px;">{{ $client['name'] }}</div>
<div style="flex: 1; background: #e67e22; height: 8px; max-width: {{ $barWidth }}%;" 
     title="{{ number_format($client['total'], 2) }}">
</div>

            </div>
        @endforeach
    </div>

    <div class="description" style="max-width: 450px; margin-left: 40px;">
        <h2 style="color: #b3541e;">Description</h2>
        <p>
            <b>{{ $topClientName }}</b> having the highest volume at around 
            <b>{{ number_format($topClientValue, 2) }}</b>, 
            and <b>{{ $lastClientName }}</b> at around 
            <b>{{ number_format($lastClientValue, 2) }}</b>.
        </p>
    </div>
</div>
<hr style="margin: 40px 0;">

<h1>Requested Active Components in RFQs by Client</h1>

<div style="display: flex; align-items: flex-start;">
    <div style="flex: 1;">
@php
    $counts = array_column($topActiveClientsData, 'count');
    $maxQty = !empty($counts) ? max($counts) : 0;
@endphp
        @foreach($topActiveClientsData as $client)
            @php
                $barWidth = $maxQty > 0 ? ($client['count'] / $maxQty) * 100 : 0;
            @endphp
            <div style="display: flex; align-items: center; margin-bottom: 6px;">
                <div style="width: 250px; font-size: 14px;">{{ $client['name'] }}</div>
                <div style="flex: 1; background: #e67e22; height: 8px; max-width: {{ $barWidth }}%;" 
                     title="{{ number_format($client['count']) }} items ({{ number_format($client['percentage'], 1) }}%)">
                </div>
            </div>
        @endforeach
    </div>

    <div class="description" style="max-width: 450px; margin-left: 40px;">
        <h2 style="color: #b3541e;">Description</h2>
        <p>
            <b>{{ $topActiveClientName }}</b> leads with 
            <b>{{ number_format($topActiveClientPercent, 1) }}%</b> of the total requested active components,    Crystals
            and <b>{{ $lastActiveClientName }}</b> with 
            <b>{{ number_format($lastActiveClientPercent, 1) }}%</b>.
        </p>
    </div>
</div>
<hr style="margin: 40px 0;">

<h1>Requested Passive Components in RFQs by Client</h1>

<div style="display: flex; align-items: flex-start;">
    <div style="flex: 1;">
@php
    $counts = array_column($topPassiveClientsData, 'count');
    $maxQty = !empty($counts) ? max($counts) : 0;
@endphp
        @foreach($topPassiveClientsData as $client)
            @php
                $barWidth = $maxQty > 0 ? ($client['count'] / $maxQtyPassive) * 100 : 0;
            @endphp
            <div style="display: flex; align-items: center; margin-bottom: 6px;">
                <div style="width: 250px; font-size: 14px;">{{ $client['name'] }}</div>
                <div style="flex: 1; background: #e67e22; height: 8px; max-width: {{ $barWidth }}%;" 
                     title="{{ number_format($client['count']) }} items ({{ number_format($client['percentage'], 1) }}%)">
                </div>
            </div>
        @endforeach
    </div>

    <div class="description" style="max-width: 450px; margin-left: 40px;">
        <h2 style="color: #b3541e;">Description</h2>
        <p>
            <b>{{ $topPassiveClientName }}</b> leads with 
            <b>{{ number_format($topPassiveClientPercent, 1) }}%</b> of the total requested passive components,
            and <b>{{ $lastPassiveClientName }}</b> with 
            <b>{{ number_format($lastPassiveClientPercent, 1) }}%</b>.
        </p>
    </div>
</div>
<hr style="margin: 40px 0;">

<h1>Best Quoted Active Components by Supplier</h1>

<div style="display: flex; align-items: flex-start;">
    <div style="flex: 1;">
@php
    $counts = array_column($topActiveSuppliersData, 'count');
    $maxQty = !empty($counts) ? max($counts) : 0;
@endphp
        @foreach($topActiveSuppliersData as $supplier)
            @php
                $barWidth = ($supplier['count'] / $maxQty) * 100;
            @endphp
            <div style="display: flex; align-items: center; margin-bottom: 6px;">
                <div style="width: 250px; font-size: 14px;">{{ $supplier['name'] }}</div>
                <div style="flex: 1; background: #e67e22; height: 8px; max-width: {{ $barWidth }}%;" 
                     title="{{ number_format($supplier['count'], 0) }} items ({{ number_format($supplier['percentage'], 1) }}%)">
                </div>
            </div>
        @endforeach
    </div>

    <div class="description" style="max-width: 450px; margin-left: 40px;">
        <h2 style="color: #b3541e;">Description</h2>
        <p>
            <b>{{ $topActiveSupplierName }}</b> leads with 
            <b>{{ number_format($topActiveSupplierPercent, 1) }}%</b> of the total requested active components,
            and <b>{{ $lastActiveSupplierName }}</b> with 
            <b>{{ number_format($lastActiveSupplierPercent, 4) }}%</b>.
        </p>
    </div>
</div>

<hr style="margin: 40px 0;">

<h1>Best Quoted Passive Components by Supplier</h1>

<div style="display: flex; align-items: flex-start;">
    <div style="flex: 1;">
@php
    $counts = array_column($topPassiveSuppliersData, 'count');
    $maxQty = !empty($counts) ? max($counts) : 0;
@endphp
        @foreach($topPassiveSuppliersData as $supplier)
            @php
                $barWidth = ($supplier['count'] / $maxQty) * 100;
            @endphp
            <div style="display: flex; align-items: center; margin-bottom: 6px;">
                <div style="width: 250px; font-size: 14px;">{{ $supplier['name'] }}</div>
                <div style="flex: 1; background: #e67e22; height: 8px; max-width: {{ $barWidth }}%;" 
                     title="{{ number_format($supplier['count'], 0) }} items ({{ number_format($supplier['percentage'], 1) }}%)">
                </div>
            </div>
        @endforeach
    </div>

    <div class="description" style="max-width: 450px; margin-left: 40px;">
        <h2 style="color: #b3541e;">Description</h2>
        <p>
            <b>{{ $topPassiveSupplierName }}</b> leads with 
            <b>{{ number_format($topPassiveSupplierPercent, 1) }}%</b> of the total requested passive components,
            and <b>{{ $lastPassiveSupplierName }}</b> with 
            <b>{{ number_format($lastPassiveSupplierPercent, 4) }}%</b>.
        </p>
    </div>
</div>

<hr style="margin: 40px 0;">

<h1>Suppliers’ Quoting Preferences by Category</h1>

<table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; width: 100%;">
    <thead style="background-color: #f57c00; color: white;">
        <tr>
            <th>Suppliers</th>
            <th>Best Priced Category</th>
            <th>2nd Best Priced Category</th>
            <th>3rd Best Priced Category</th>
        </tr>
    </thead>
    <tbody>
        @foreach($supplierPreferences as $pref)
            <tr>
                <td>{{ $pref['supplier'] }}</td>
                <td>{{ $pref['best'] }}</td>
                <td>{{ $pref['second'] }}</td>
                <td>{{ $pref['third'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<hr style="margin: 40px 0;">

<h1>Most Requested Categories (Top 3 Clients per Category)</h1>

<table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; width: 100%;">
    <thead style="background-color: #f57c00; color: white;">
        <tr>
            <th>Most Requested Categories</th>
            <th>Top 1 Client requesting the category</th>
            <th>Top 2 Client requesting the category</th>
            <th>Top 3 Client requesting the category</th>
        </tr>
    </thead>
    <tbody>
        @foreach($topClientsPerCategory as $row)
            <tr style="{{ $loop->index % 2 == 0 ? 'background-color: #f5f5f5;' : '' }}">
                <td>{{ $row['category'] }}</td>
                <td>{{ $row['top1'] }}</td>
                <td>{{ $row['top2'] }}</td>
                <td>{{ $row['top3'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

    </div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
        integrity="sha512-YcsIPR8vM3eXqg3I4Q3mC8Tt0tWZC1xS8TgIWh1u5m1q9fS7iV4cR4u3tZQ8m1dkq6b2p4vXQ2CjA0C4Jv3G2Q=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
  function exportToPDF() {
    const element = document.getElementById('pdf-content');
    const opt = {
      margin: 0.4,
      filename: 'RFQ_Dashboard.pdf',
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2, useCORS: true, logging: false },
      jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    };
    html2pdf().set(opt).from(element).save();
  }
</script>
@endpush