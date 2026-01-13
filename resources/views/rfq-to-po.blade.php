{{-- resources/views/rfq_po.blade.php --}}
@extends('layouts.app')

@section('content')

<style>
/* ================= SAME STYLES YOU ALREADY HAVE ================= */
    .rfq20-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:36px;align-items:flex-start}
@media (max-width:992px){.rfq20-grid{grid-template-columns:1fr}}
.rfqbar-row{display:flex;align-items:center;gap:18px;margin:8px 0}
.rfqbar-label{flex:0 0 320px;text-align:right;color:#333;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.rfqbar-track{flex:1;background:#f2f2f2;height:10px;position:relative;border-radius:6px}
.rfqbar-fill{position:absolute;left:0;top:0;height:100%;border-radius:6px;background:#c56000}

    :root{
      --gray1:#8b8b8b; --gray2:#666; --gray3:#4d4d4d;
      --orange1:#f57c00; --orange2:#c56000; --orange3:#8e4300;
      --po-h:68px;   /* section 1 bar height (set via <style> below) */
      --po2-h:68px;  /* section 2 bar height (set via <style> below) */
    }
    
    .navbar{ margin-bottom: 8px; }

    /* page container */
    /* .page-wrap{ padding: 42px 54px 64px; max-width: 1200px; margin: 0 auto; } */
.page-wrap {
  padding: 20px;
}

    /* .title{
      font-family: Fraunces, Georgia, serif;
      font-size: clamp(28px, 4vw, 56px);
      line-height: 1.05;
      margin: 12px 0 6px;
      letter-spacing: .2px;
    }
    .title::before{
      content:"";
      display:inline-block;
      width:6px; height:48px; background:#111; margin-right:16px; transform: translateY(6px);
    } */
      .title {
  font-size: 28px;
  font-weight: bold;
  margin-bottom: 10px;
}

    .subtitle{ color:#555; margin: 0 0 28px; font-size: 18px; }

.viz {
  display: flex;
  gap: 60px;
  align-items: center;   /* key */
  padding-left: 32px;
}


    /* RFQ block */
    .rfq-block{
      position:relative; width:190px; height: clamp(260px, 38vh, 360px);
      background: linear-gradient(135deg, var(--gray1), var(--gray2));
      box-shadow: 0 12px 22px rgba(0,0,0,.15);
      border-radius: 6px; cursor: default;
    }
    .rfq-block:before{
      content:""; position:absolute; left:-16px; bottom:8px;
      width:16px; height: calc(100% - 8px);
      background: linear-gradient(180deg, var(--gray2), var(--gray3));
      border-radius: 6px 0 0 6px;
      filter: brightness(.95);
    }
    .rfq-block:after{
      content:""; position:absolute; left:-8px; top:-12px;
      width:100%; height:14px;
      background: linear-gradient(180deg, #b0b0b0, #9a9a9a);
      border-radius: 6px 6px 0 0;
      transform: skewX(-18deg);
    }
    .rfq-label{
      position:absolute; inset:0; display:flex; align-items:center; justify-content:center;
      text-align:center; color:#fff; font-weight:600; font-size:16px; letter-spacing:.3px;
      padding:0 14px;
    }

    /* PO block (section 1) */
    .po-wrap{ position:relative; margin-left:-48px; margin-bottom:-8px; }
    .po-block{
      position:relative; width:220px; height: var(--po-h, 68px);
      background: linear-gradient(135deg, var(--orange1), var(--orange2));
      box-shadow: 0 10px 18px rgba(0,0,0,.15);
      border-radius: 6px; cursor: default;
    }
    .po-block:before{
      content:""; position:absolute; right:-18px; bottom:8px; width:18px; height: calc(100% - 8px);
      background: linear-gradient(180deg, var(--orange2), var(--orange3));
      border-radius: 0 6px 6px 0;
      filter: brightness(.95);
    }
    .po-block:after{
      content:""; position:absolute; right:-9px; top:-10px; width:100%; height:12px;
      background: linear-gradient(180deg, #ffb066, #f58c28);
      border-radius: 6px 6px 0 0; transform: skewX(18deg);
    }
    .po-label{
      position:absolute; inset:0; display:flex; align-items:center; justify-content:center;
      text-align:center; color:#fff; font-weight:700; letter-spacing:.3px;
      padding:0 12px;
    }

    /* PO block (section 2 - items) — exact same design */
    .po2-wrap{ position:relative; margin-left:-48px; margin-bottom:-8px; }
    .po2-block{
      position:relative; width:220px; height: var(--po2-h, 68px);
      background: linear-gradient(135deg, var(--orange1), var(--orange2));
      box-shadow: 0 10px 18px rgba(0,0,0,.15);
      border-radius: 6px; cursor: default;
    }
    .po2-block:before{
      content:""; position:absolute; right:-18px; bottom:8px; width:18px; height: calc(100% - 8px);
      background: linear-gradient(180deg, var(--orange2), var(--orange3));
      border-radius: 0 6px 6px 0;
      filter: brightness(.95);
    }
    .po2-block:after{
      content:""; position:absolute; right:-9px; top:-10px; width:100%; height:12px;
      background: linear-gradient(180deg, #ffb066, #f58c28);
      border-radius: 6px 6px 0 0; transform: skewX(18deg);
    }

    .desc h3{
      font-family: Fraunces, Georgia, serif;
      color:#a55a22; font-size:26px; margin-bottom:14px;
    }
    /* .desc p{ font-size:22px; line-height:1.6; color:#222; } */
    .desc p {
  font-size: 16px;
  line-height: 1.5;
}

    .muted{ color:#888; font-size:14px; }

    .section-sep{ border-top: 1px dashed #ddd; margin: 48px 0 36px; }

    @media (max-width: 992px){
      .viz{ flex-direction:column; align-items:flex-start; gap:28px; min-height:auto; padding-left:12px; }
      .po-wrap, .po2-wrap{ margin-left:0; }
    }

    /* ---- Top 20 clients chart ---- */
.top20-grid{ display:grid; grid-template-columns: 1.1fr .9fr; gap:36px; align-items:flex-start; }
@media (max-width: 992px){ .top20-grid{ grid-template-columns:1fr; } }

.bar-row{ display:flex; align-items:center; gap:18px; margin:8px 0; }
.bar-label{
  flex:0 0 320px; text-align:right; color:#333; font-size:14px;
  white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.bar-track{ flex:1; background:#f2f2f2; height:10px; position:relative; border-radius:6px; }
.bar-fill{ position:absolute; left:0; top:0; height:100%; border-radius:6px; background:#c56000; }
.legend{ color:#888; font-size:13px; margin-top:10px; }

  </style>
{{-- ================= EXPORT BUTTON (SAME AS OTHER PAGE) ================= --}}
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">RFQ-to-PO Dashboard</h4>
  <button id="exportPDF" class="btn btn-danger">📄 Export to PDF</button>
</div>

{{-- ================= PDF CONTENT ================= --}}
<div id="exportContent">
<div class="page-wrap">

  {{-- ========== SECTION 1: RFQ-to-PO Conversion (IDs) ========== --}}
    <h1 class="title">RFQ‑to‑PO Conversion Performance</h1>
    <p class="subtitle">Based on unique RFQ IDs vs unique PO IDs</p>

    @php
      $rfqTotal = (int)($quotedCount ?? 0);
      $poTotal  = (int)($poCount ?? 0);
      $conv     = number_format($conversion ?? 0, 1);
      $maxH     = 340;
      $poH      = max(14, min($maxH, $rfqTotal > 0 ? ($poTotal / $rfqTotal) * $maxH : 0));
    @endphp
    <style>:root{ --po-h: {{ $poH }}px; }</style>

    <div class="viz">
      <!-- Left: bars -->
      <div class="d-flex align-items-end">
        <div class="rfq-block" data-bs-toggle="tooltip" data-bs-placement="top"
             title="{{ number_format($rfqTotal) }} RFQs">
          <div class="rfq-label">Total number<br>of RFQs</div>
        </div>

        <div class="po-wrap">
          <div class="po-block" data-bs-toggle="tooltip" data-bs-placement="top"
               title="{{ number_format($poTotal) }} POs • Conversion {{ $conv }}%">
            <div class="po-label">Total Number<br>of POs</div>
          </div>
        </div>
      </div>

      <!-- Right: description -->
      <div class="desc" style="max-width: 720px;">
        <h3>Description</h3>
        <p>
          Out of around <strong>{{ number_format($rfqTotal) }}</strong> quoted RFQs,
          we received around <strong>{{ number_format($poTotal) }}</strong> POs,
          resulting in an RFQ‑to‑PO <strong>conversion rate of {{ $conv }}%</strong>.
        </p>
        <div class="muted">
          Data sources (IDs): <code>Priced Items info!B:B</code> and <code>extended RFQs!B:B</code> from <code>1DWxMnz…</code>,
          and <code>Limited!B3:B</code> from <code>14SRB6t…</code>.
        </div>
      </div>
    </div>

    <div class="section-sep"></div>

    {{-- ========== SECTION 2: RFQ-to-PO Items Conversion (rows/items) ========== --}}
    <h1 class="title">RFQ‑to‑PO Items Conversion Performance</h1>
    <p class="subtitle">Based on total item rows (RFQ items vs PO items)</p>

    @php
      $rfqItems  = (int)($quotedCountItems ?? 0);
      $poItems   = (int)($poCountItems ?? 0);
      $convItems = number_format($conversionItems ?? 0, 1);
      $maxH2     = 340;
      $poH2      = max(14, min($maxH2, $rfqItems > 0 ? ($poItems / $rfqItems) * $maxH2 : 0));
    @endphp
    <style>:root{ --po2-h: {{ $poH2 }}px; }</style>

    <div class="viz">
      <!-- Left: bars -->
      <div class="d-flex align-items-end">
        <div class="rfq-block" data-bs-toggle="tooltip" data-bs-placement="top"
             title="{{ number_format($rfqItems) }} RFQ items">
          <div class="rfq-label">Total number<br>of RFQ items</div>
        </div>

        <div class="po2-wrap">
          <div class="po2-block" data-bs-toggle="tooltip" data-bs-placement="top"
               title="{{ number_format($poItems) }} PO items • Items conversion {{ $convItems }}%">
            <div class="po-label">Total Number<br>of PO items</div>
          </div>
        </div>
      </div>

      <!-- Right: description -->
      <div class="desc" style="max-width: 720px;">
        <h3>What this shows</h3>
        <p>
          From approximately <strong>{{ number_format($rfqItems) }}</strong> RFQ item rows,
          we converted about <strong>{{ number_format($poItems) }}</strong> items into POs,
          giving an <strong>items conversion rate of {{ $convItems }}%</strong>.
        </p>
        <div class="muted">
          Data sources (items): <code>Priced Items info!A:A</code> and <code>extended RFQs!A:A</code> from <code>1DWxMnz…</code>,
          and <code>Limited!A3:A</code> from <code>14SRB6t…</code>.
        </div>
      </div>
    </div>
 <!-- /.page-wrap -->

  {{-- ========== SECTION 3: RFQ-to-PO volume ($) Conversion ========== --}}
<div class="section-sep"></div>

<h1 class="title">RFQ‑to‑PO volume ($) Conversion Performance</h1>
<p class="subtitle">Based on total quoted value ($) vs PO value ($)</p>

@php
  // Helper to format large money numbers as 1.3 Billion $, 11 million $, etc.
  $fmtMoney = function($n) {
      $abs = abs($n);
      if ($abs >= 1_000_000_000) return number_format($n/1_000_000_000, 1) . ' Billion $';
      if ($abs >= 1_000_000)     return number_format($n/1_000_000, 1) . ' million $';
      if ($abs >= 1_000)         return number_format($n/1_000, 1) . 'K $';
      return number_format($n, 0) . ' $';
  };

  $quotedVol = (float)($quotedVolume ?? 0.0);
  $poVol     = (float)($poVolume ?? 0.0);
  $convVol   = number_format($conversionVolume ?? 0, 1);

  $maxH3   = 340;
  $poH3    = max(14, min($maxH3, $quotedVol > 0 ? ($poVol / $quotedVol) * $maxH3 : 0));
@endphp
<style>:root{ --po2-h: {{ $poH3 }}px; }</style>

<div class="viz">
  <!-- Left: bars -->
  <div class="d-flex align-items-end">
    <div class="rfq-block"
         data-bs-toggle="tooltip" data-bs-placement="top"
         title="{{ number_format($quotedVol, 0, '.', ',') }} $ quoted">
      <div class="rfq-label">Total Volume<br>($) of RFQs</div>
    </div>

    <div class="po2-wrap">
      <div class="po2-block"
           data-bs-toggle="tooltip" data-bs-placement="top"
           title="{{ number_format($poVol, 0, '.', ',') }} $ in POs • Conversion {{ $convVol }}%">
        <div class="po-label">Total Volume<br>($) of POs</div>
      </div>
    </div>
  </div>

  <!-- Right: description -->
  <div class="desc" style="max-width: 720px;">
    <h3>Description</h3>
    <p>
      Out of the around <strong>{{ $fmtMoney($quotedVol) }}</strong> quoted items,
      we received around <strong>{{ $fmtMoney($poVol) }}</strong>,
      resulting in a <strong>conversion rate of {{ $convVol }}%</strong>.
    </p>
    <div class="muted">
      Data sources (value): <code>Priced Items info!S:S</code> + <code>extended RFQs!S:S</code> for quoted volume,
      and <code>Limited!T3:T</code> for PO volume.
    </div>
  </div>
</div>

 </div>

<div class="section-sep"></div>
<h1 class="title">Top 20 Clients by Total RFQ Volume ($)</h1>
<p class="subtitle">Source: Priced Items info (Client = E, Value = S)</p>

@php
  $fmtMoney = function($n){
    $a = abs($n);
    if ($a >= 1_000_000_000) return number_format($n/1_000_000_000,1).' Billion $';
    if ($a >= 1_000_000)     return number_format($n/1_000_000,1).' million $';
    return number_format($n,0,'.',',').' $';
  };
@endphp

<div class="rfq20-grid">
  <!-- Left: bars -->
  <div>
    @forelse(($top20Rfq ?? []) as $r)
      @php $w = round(($r['rfq_total'] / ($maxRfqTotal ?? 1)) * 100, 1); @endphp
      <div class="rfqbar-row" title="{{ $r['client'] }} — {{ $fmtMoney($r['rfq_total']) }}">
        <div class="rfqbar-label">{{ $r['client'] }}</div>
        <div class="rfqbar-track">
          <div class="rfqbar-fill" style="width: {{ $w }}%;"></div>
        </div>
      </div>
    @empty
      <div class="text-muted">No data found.</div>
    @endforelse
    <div class="legend">Bars are scaled to the top client (100%).</div>
  </div>

  <!-- Right: description -->
  <div class="desc">
    <h3>Description</h3>
    @if(!empty($top20Rfq))
      <p>
        <strong>{{ $top20Rfq[0]['client'] }}</strong> has the highest total RFQ volume at
        <strong>{{ $fmtMoney($top20Rfq[0]['rfq_total']) }}</strong>.
      </p>
      @if(count($top20Rfq) > 1)
      <p>
        The 20th place is <strong>{{ $top20Rfq[count($top20Rfq)-1]['client'] }}</strong> with
        <strong>{{ $fmtMoney($top20Rfq[count($top20Rfq)-1]['rfq_total']) }}</strong>.
      </p>
      @endif
    @endif
  </div>
</div>

<div class="section-sep"></div>
<h1 class="title">Breakdown of POs by top 20 Clients</h1>

@php
  $fmtMoney = function($n){
    $a = abs($n);
    if ($a >= 1_000_000_000) return number_format($n/1_000_000_000,1).' million $' /* adjust if you want Billion */;
    if ($a >= 1_000_000)     return number_format($n/1_000_000,1).' million $';
    return number_format($n,0,'.',',').' $';
  };
@endphp

<div class="rfq20-grid">  {{-- reuse the same grid layout --}}
  <!-- Left: bars -->
  <div>
    @forelse(($top20Po ?? []) as $r)
      @php $w = round(($r['po_total'] / ($maxPoTotal ?? 1)) * 100, 1); @endphp
      <div class="rfqbar-row" title="{{ $r['client'] }} — {{ $fmtMoney($r['po_total']) }}">
        <div class="rfqbar-label">{{ $r['client'] }}</div>
        <div class="rfqbar-track">
          <div class="rfqbar-fill" style="width: {{ $w }}%;"></div>
        </div>
      </div>
    @empty
      <div class="text-muted">No PO data found.</div>
    @endforelse
    <div class="legend">Bars are scaled to the highest PO volume (100%).</div>
  </div>

  <!-- Right: description -->
  <div class="desc">
    <h3>Description</h3>
    @if(!empty($topPoClient))
      <p>
        <strong>{{ $topPoClient['client'] }}</strong> has the highest volume at around
        <strong>{{ $fmtMoney($topPoClient['po_total']) }}</strong>,
        @if(!empty($bottomPoClient))
          while <strong>{{ $bottomPoClient['client'] }}</strong> has the lowest among the top 20
          at around <strong>{{ $fmtMoney($bottomPoClient['po_total']) }}</strong>.
        @endif
      </p>
    @endif
  </div>
</div>



<!-- PDF Export Dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
    document.getElementById("exportPDF").addEventListener("click", function () {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'pt', 'a4');

    // Grab everything except the navbar
    const page = document.getElementById("exportContent");

    html2canvas(page, { scale: 2, useCORS: true }).then(canvas => {
        const imgData = canvas.toDataURL("image/png");
        const imgProps = doc.getImageProperties(imgData);
        const pdfWidth = doc.internal.pageSize.getWidth();
        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

        // Multi-page support
        let heightLeft = pdfHeight;
        let position = 0;

        doc.addImage(imgData, "PNG", 0, position, pdfWidth, pdfHeight);
        heightLeft -= doc.internal.pageSize.getHeight();

        while (heightLeft > 0) {
            position = heightLeft - pdfHeight;
            doc.addPage();
            doc.addImage(imgData, "PNG", 0, position, pdfWidth, pdfHeight);
            heightLeft -= doc.internal.pageSize.getHeight();
        }

        doc.save("RFQ-to-PO_Report.pdf");
    });
  });
  </script>




  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
    });
  </script>
@endsection


