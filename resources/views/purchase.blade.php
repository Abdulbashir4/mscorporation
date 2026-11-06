@extends('layouts.app')
@section('title', 'Purchase')
@section('content')
<div class="purchase-page">
    <div class="purchase-heading flex items-center justify-between">
        <div class="flex items-center"><span class="purchase-page-icon"><x-icon name="cart"/></span><div><h1>Purchase</h1><p>Manage your purchase orders, suppliers, goods receive and payments.</p></div></div>
        <div class="purchase-heading-actions flex items-center"><button class="purchase-date flex items-center" data-purchase-action="Date Range"><x-icon name="calendar"/><span>01 Apr 2025 <i>→</i> 30 Apr 2025</span><x-icon name="down"/></button><button class="primary-button create-purchase flex items-center" data-purchase-action="Create Purchase Order"><span>＋</span>Create Purchase Order<x-icon name="down"/></button></div>
    </div>
    <section class="purchase-stats grid" aria-label="Purchase summary">
        @foreach([['Total Purchase Order','৳ 9,75,000','↑ 12%','clipboard','blue',false],['Total Received','৳ 8,20,000','↑ 8%','truck','green',false],['Total Payable','৳ 3,45,000','↓ 5%','money','purple',true],['Suppliers','12','↑ 1 new','users','orange',false],['Pending Orders','3','↓ 25%','clipboard','teal',true]] as [$label,$amount,$change,$icon,$color,$negative])
        <article class="card purchase-stat flex items-start"><span class="purchase-stat-icon {{ $color }}"><x-icon :name="$icon"/></span><div><p>{{ $label }}</p><strong>{{ $amount }}</strong><small><b class="{{ $negative ? 'negative' : 'positive' }}">{{ $change }}</b>vs last month</small></div></article>
        @endforeach
    </section>
    <section class="purchase-top-grid grid">
        <article class="card purchase-overview"><div class="purchase-section-heading flex items-center justify-between"><h2>Purchase Overview</h2><div class="purchase-chart-legend flex"><span><i class="order-dot"></i>Purchase Order</span><span><i class="received-dot"></i>Received</span><span><i class="payable-dot"></i>Payable</span></div></div>
            <svg class="purchase-bar-chart" viewBox="0 0 500 230" role="img" aria-label="Monthly purchase orders, received stock and payables increased from January to April. April purchase orders: 10 lakh, received: 8.9 lakh, payable: 9 lakh.">
                <defs><linearGradient id="purchase-blue" x2="1" y2="0"><stop stop-color="#087cda"/><stop offset="1" stop-color="#0060b9"/></linearGradient><linearGradient id="purchase-green" x2="1" y2="0"><stop stop-color="#10b178"/><stop offset="1" stop-color="#089159"/></linearGradient></defs>
                <g stroke="#eaf0f5" stroke-width=".8"><path d="M29 15H490M29 42H490M29 69H490M29 96H490M29 123H490M29 150H490M29 177H490"/><path d="M29 15V177" stroke="#dce6ef"/></g>
                <g class="purchase-chart-labels"><text x="3" y="19">12L</text><text x="3" y="46">10L</text><text x="8" y="73">8L</text><text x="8" y="100">6L</text><text x="8" y="127">4L</text><text x="8" y="154">2L</text><text x="13" y="181">0</text><text x="64" y="201">Jan</text><text x="187" y="201">Feb</text><text x="310" y="201">Mar</text><text x="433" y="201">Apr</text></g>
                @foreach([[48,116,75,130],[171,89,198,97],[294,70,321,80],[417,43,444,57]] as [$x,$y,$rx,$ry])<rect x="{{ $x }}" y="{{ $y }}" width="24" height="{{ 177-$y }}" rx="3" fill="url(#purchase-blue)"/><rect x="{{ $rx }}" y="{{ $ry }}" width="24" height="{{ 177-$ry }}" rx="3" fill="url(#purchase-green)"/>@endforeach
                <path d="M75 133 198 111 321 94 444 58" stroke="#e9b121" stroke-width="1.9" fill="none"/>
                @foreach([[75,133],[198,111],[321,94],[444,58]] as [$x,$y])<circle cx="{{ $x }}" cy="{{ $y }}" r="4" fill="#f3bd31" stroke="#fff" stroke-width="1"/>@endforeach
            </svg>
        </article>
        <article class="card purchase-orders"><div class="purchase-section-heading flex items-center justify-between"><h2>Recent Purchase Orders</h2><button class="text-link" data-purchase-action="Purchase Order">View All</button></div><x-purchase-table :headers="['PO No.','Supplier','Date','Status','Amount']" :rows="config('purchase.orders')"/></article>
        <article class="card purchase-quick-actions"><h2>Quick Actions</h2>
            @foreach([['Create Purchase Order','plus'],['Goods Receive','truck'],['Create Purchase Invoice','clipboard'],['Make Payment','money'],['Import Tracking','upload'],['Supplier Management','users']] as [$action,$icon])<button class="purchase-quick-action flex items-center" data-purchase-action="{{ $action }}"><span><x-icon :name="$icon"/></span><span>{{ $action }}</span><x-icon name="chevron"/></button>@endforeach
        </article>
    </section>
    <section class="purchase-middle-grid grid">
        <article class="card purchase-suppliers"><div class="purchase-section-heading flex items-center justify-between"><h2>Supplier List</h2><button class="text-link" data-purchase-action="Supplier">View All</button></div><x-purchase-table :headers="['Supplier Name','Country','Contact Person','Phone','Total Purchase','Status']" :rows="config('purchase.suppliers')"/></article>
        <article class="card purchase-payments"><div class="purchase-section-heading flex items-center justify-between"><h2>Pending Payments</h2><button class="text-link" data-purchase-action="Payment">View All</button></div><x-purchase-table :headers="['Supplier','Invoice No.','Due Date','Amount']" :rows="config('purchase.payments')" total="৳ 6,55,000"/></article>
    </section>
    <section class="purchase-bottom-grid grid">
        <article class="card purchase-receipts"><div class="purchase-section-heading flex items-center justify-between"><h2>Recent Goods Receive</h2><button class="text-link" data-purchase-action="Goods Receive">View All</button></div><x-purchase-table :headers="['GR No.','PO No.','Supplier','Date','Received Qty','Status']" :rows="config('purchase.receipts')"/></article>
        <article class="card purchase-products"><div class="purchase-section-heading flex items-center justify-between"><h2>Top Purchased Products</h2><button class="text-link" data-purchase-action="Top Purchased Products">View All</button></div><x-purchase-table :headers="['Product Name','Qty','Total Amount']" :rows="config('purchase.products')"/></article>
        <article class="card purchase-analytics"><h2>Purchase Analytics</h2><div class="purchase-analytics-content flex items-center"><div class="purchase-donut" role="img" aria-label="Medical Equipment 52%, Diagnostic 18%, Surgical 12%, Consumables 10%, Others 8%"><div><strong>৳ 9,75,000</strong><small>Total Purchase</small></div></div><ul>@foreach([['Medical Equipment',52,'#0955b3'],['Diagnostic',18,'#13b384'],['Surgical',12,'#f7b823'],['Consumables',10,'#a643bb'],['Others',8,'#dce3e9']] as [$name,$percent,$color])<li><i style="background:{{ $color }}"></i><span>{{ $name }}</span><b>{{ $percent }}%</b></li>@endforeach</ul></div></article>
    </section>
    <p class="purchase-preview-note">Reference preview data · Live purchases and payments require a connected MySQL database.</p>
    <p id="search-message" role="status" hidden></p>
</div>
@endsection
