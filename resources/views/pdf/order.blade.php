<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        color: #1a1a1a;
        background: #ffffff;
    }

    /* ─── HEADER ──────────────────────────────────────────────── */
    .header {
        padding: 22px 32px 20px;
        border-bottom: 3px solid #c9920a;
    }

    .header-table {
        width: 100%;
        border-collapse: collapse;
    }

    .header-left {
        width: 55%;
        vertical-align: middle;
    }

    .header-right {
        width: 45%;
        vertical-align: middle;
        text-align: right;
    }

    .logo-row {
        /* flex-like via table */
    }

    .logo-img {
        max-height: 44px;
        max-width: 220px;
        vertical-align: middle;
    }

    .company-name {
        font-size: 20px;
        font-weight: 700;
        color: #ffffff;
        line-height: 1.1;
        letter-spacing: 0.2px;
    }

    .company-alias {
        font-size: 12px;
        color: #fdd805;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-top: 3px;
    }

    .company-details {
        font-size: 12px;
        text-align: right;
    }

    .company-details .highlight {
        color: #d1d5db;
    }

    /* ─── DOC BAND ────────────────────────────────────────────── */
    .doc-band {
        background-color: #fdd805;
        padding: 10px 0;
    }

    .doc-band-table {
        width: 100%;
        border-collapse: collapse;
    }

    .doc-band-left {
        padding: 11px 32px;
        vertical-align: middle;
        width: 50%;
    }

    .doc-band-right {
        padding: 11px 32px;
        vertical-align: middle;
        width: 50%;
        text-align: right;
    }

    .doc-label {
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #7c4f00;
    }

    .doc-number {
        font-size: 16px;
        font-weight: 700;
        color: #111111;
        margin-top: 1px;
    }

    .doc-dates {
        font-size: 12px;
        color: #111111;
    }

    .doc-dates strong {
        font-weight: 700;
    }

    /* ─── CONTENT ─────────────────────────────────────────────── */
    .content {
        padding: 24px 32px 16px;
    }

    /* ─── INFO CARDS ──────────────────────────────────────────── */
    .cards-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 22px;
    }

    .card {
        width: 50%;
        vertical-align: top;
        border-left: 3px solid #fdd805;
        padding: 12px 14px;
    }

    .card-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #c9920a;
    }

    .card-value {
        font-size: 14px;
        font-weight: 700;
        color: #111111;
    }

    .card-sub {
        font-size: 12px;
        color: #4b5563;
    }

    .card-ref {
        font-size: 12px;
        color: #4b5563;
        font-style: italic;
    }

    /* ─── SECTION LABEL ───────────────────────────────────────── */
    .section-label {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #4b5563;
        margin-bottom: 10px;
    }

    /* ─── ITEMS TABLE ─────────────────────────────────────────── */
    table.items {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 18px;
    }

    table.items thead tr {
        background-color: #d1d5db;
    }

    table.items thead th {
        padding: 8px 10px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #4b5563;
        text-align: left;
    }

    table.items thead th.r { text-align: right; }

    table.items tbody tr {
        border-bottom: 1px solid #f0f0f0;
    }

    table.items tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    table.items tbody td {
        padding: 8px 10px;
        font-size: 10.5px;
        color: #1a1a1a;
        vertical-align: middle;
    }

    table.items tbody td.r { text-align: right; }

    table.items tbody td.code {
        font-family: DejaVu Sans Mono, monospace;
        font-size: 9px;
        color: #6b7280;
    }

    table.items tfoot tr {
        background-color: #f3f4f6;
    }

    table.items tfoot td {
        padding: 6px 10px;
        font-size: 10px;
        color: #6b7280;
    }

    /* ─── TOTALS ──────────────────────────────────────────────── */
    .totals-wrap {
        text-align: right;
        margin-top: 2px;
    }

    table.totals {
        display: inline-table;
        border-collapse: collapse;
        min-width: 280px;
    }

    table.totals td {
        padding: 0px 14px;
        font-size: 12px;
        border-bottom: 1px solid #f0f0f0;
    }

    table.totals .t-label { color: #6b7280; text-align: left; }

    table.totals tr.total-row td {
        background-color: #d1d5db;
        color: #4b5563;
        font-size: 14px;
        font-weight: 700;
        border-bottom: none;
        padding: 5px 14px;
    }

    table.totals tr.total-row td.t-accent {
        color: #4b5563;
    }

    /* ─── FOOTER ──────────────────────────────────────────────── */
    .footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 7px 32px;
        border-top: 1px solid #e5e7eb;
        background: #ffffff;
    }

    .footer-table {
        width: 100%;
        border-collapse: collapse;
    }

    .footer-left  { font-size: 8px; color: #9ca3af; text-align: left;  vertical-align: middle; }
    .footer-right { font-size: 8px; color: #9ca3af; text-align: right; vertical-align: middle; }

    .footer-dot {
        display: inline-block;
        width: 4px;
        height: 4px;
        border-radius: 2px;
        background-color: #c9920a;
        vertical-align: middle;
        margin: 0 5px;
    }
</style>
</head>
<body>

{{-- ── HEADER ─────────────────────────────────────────────────── --}}
<div class="header">
    <table class="header-table">
        <tr>
            <td class="header-left">
                @if($logoData)
                    <img src="{{ $logoData }}" class="logo-img" alt="{{ $company->name }}" />
                @else
                    <div class="company-name">{{ $company->name }}</div>
                    @if($company->alias)
                        <div class="company-alias">{{ $company->alias }}</div>
                    @endif
                @endif
            </td>
            <td class="header-right">
                <div class="company-details">
                    @if($company->document_type && $company->document_number)
                        {{ $company->document_number }}<br/>
                    @endif
                    @if($company->address)
                        {{ $company->address }}<br/>
                    @endif
                    @if($company->phone)
                        Tel: {{ $company->phone }}<br/>
                    @endif
                    @if($company->email)
                        {{ $company->email }}
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- ── DOC BAND ────────────────────────────────────────────────── --}}
<div class="doc-band">
    <table class="doc-band-table">
        <tr>
            <td class="doc-band-left">
                <div class="doc-label">Pedido</div>
                <div class="doc-number">{{ $order->name }}</div>
            </td>
            <td class="doc-band-right">
                <div class="doc-dates">
                    <strong>Fecha de creación:</strong> {{ $order->created_at?->format('d/m/Y H:i') ?? '—' }}<br/>
                    @if($order->delivery_date)
                        <strong>Fecha de entrega:</strong> {{ $order->delivery_date->format('d/m/Y') }}
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- ── CONTENT ─────────────────────────────────────────────────── --}}
<div class="content">

    {{-- Cards: cliente + dirección --}}
    <table class="cards-table">
        <tr>
            <td class="card" style="width: 30%;">
                <div class="card-title">Cliente</div>
                @if($order->customer)
                    <div class="card-value">{{ $order->customer->full_name }}</div>
                    @if($order->customer->phone)
                        <div class="card-sub">Tel: {{ $order->customer->phone }}</div>
                    @endif
                @else
                    <div class="card-sub">—</div>
                @endif
            </td>
            <td class="card">
                <div class="card-title">Dirección de entrega</div>
                @if($order->address)
                    @php $addr = $order->address; @endphp
                    @if($addr->address)
                        <div class="card-value">{{ $addr->address }}</div>
                    @endif
                    @php
                        $cityLine = implode(', ', array_filter([
                            $addr->district_name ?? null,
                            $addr->city_name ?? null,
                        ]));
                        $regionLine = implode(', ', array_filter([
                            $addr->state_name ?? null,
                            $addr->country_name ?? null,
                        ]));
                    @endphp
                    @if($cityLine)
                        <div class="card-sub">{{ $cityLine }}</div>
                    @endif
                    @if($regionLine)
                        <div class="card-sub">{{ $regionLine }}</div>
                    @endif
                    @if($addr->zip_code)
                        <div class="card-sub">CP: {{ $addr->zip_code }}</div>
                    @endif
                    @if($addr->reference)
                        <div class="card-ref">Ref: {{ $addr->reference }}</div>
                    @endif
                @else
                    <div class="card-sub">—</div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Líneas --}}
    <div class="section-label">Líneas del pedido</div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:80px;">Código</th>
                <th style="width:72px;">SKU</th>
                <th>Producto</th>
                <th class="r" style="width:52px;">Cant.</th>
                <th class="r" style="width:90px;">Precio unit.</th>
                <th class="r" style="width:96px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($order->items as $item)
                <tr>
                    <td class="code">{{ $item->product?->code ?? '—' }}</td>
                    <td class="code">{{ $item->product?->sku ?? '—' }}</td>
                    <td>{{ $item->product?->name ?? '—' }}</td>
                    <td class="r">{{ $item->quantity }}</td>
                    <td class="r">{{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
                    <td class="r">{{ number_format((float) $item->line_total, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:18px 0; color:#9ca3af;">
                        Sin líneas de detalle.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Totales --}}
    <div class="totals-wrap">
        <table class="totals">
            <tr class="total-row">
                <td class="t-label">IMPORTE TOTAL</td>
                <td class="t-accent">{{ number_format((float) $order->total_amount, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

</div>

{{-- ── FOOTER ──────────────────────────────────────────────────── --}}
<div class="footer">
    <table class="footer-table">
        <tr>
            <td class="footer-left">
                {{ $company->name }}
                <span class="footer-dot"></span>
                Generado el {{ now()->format('d/m/Y H:i') }}
            </td>
            <td class="footer-right">Pedido {{ $order->name }}</td>
        </tr>
    </table>
</div>

</body>
</html>
