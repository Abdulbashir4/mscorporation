@extends('layouts.app')
@section('content')
@php
$modules = config('dashboard.modules');
$badge = fn($status) => match($status) {'Pending'=>'amber', 'Partial', 'Partially Paid'=>'sky', default=>'green'};
@endphp
    <div class="page-heading flex items-start justify-between"><div><h1>Dashboard</h1><p>Welcome back, Admin! <span>Here's what's happening with your business today.</span></p></div><div class="date flex items-center"><x-icon name="calendar"/>Tuesday, 15 April 2025</div></div>
    <section class="stats-grid grid" aria-label="Business overview">
    @foreach(config('dashboard.stats') as [$title,$period,$amount,$percent,$icon,$color])
        <article class="stat-card card flex items-start"><div class="stat-icon {{ $color }}"><x-icon :name="$icon"/></div><div><p>{{ $title }} <span>{{ $period ? '('.$period.')' : '' }}</span></p><strong class="stat-value">৳ {{ $amount }}</strong><div class="stat-change"><b class="{{ $loop->last ? 'negative' : 'positive' }}">{{ $loop->last ? '↓' : '↑' }} {{ $percent }}%</b><span>vs last month</span></div></div></article>
    @endforeach
    </section>
    <section class="middle-grid grid">
        <article class="card overview"><div class="section-heading flex items-center justify-between"><h2>Sales &amp; Purchase Overview</h2><div class="chart-legend flex"><span><i class="sales-dot"></i>Sales</span><span><i class="purchase-dot"></i>Purchase</span></div></div>
            <svg class="sales-chart" viewBox="0 0 440 242" role="img" aria-label="Sales increased from 6.5 lakh in January to 16 lakh in April. Purchases increased from 3.5 lakh to 12 lakh.">
                <defs><linearGradient id="sales-fill" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#2786da" stop-opacity=".16"/><stop offset="1" stop-color="#2786da" stop-opacity=".015"/></linearGradient><linearGradient id="purchase-fill" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#dfb441" stop-opacity=".13"/><stop offset="1" stop-color="#dfb441" stop-opacity=".02"/></linearGradient></defs>
                <g class="chart-grid"><path d="M35 20H425M35 70H425M35 120H425M35 170H425M35 220H425M35 20V220M158 20V220M284 20V220M408 20V220"/></g>
                <g class="chart-labels"><text x="5" y="24">20L</text><text x="5" y="74">15L</text><text x="5" y="124">10L</text><text x="11" y="174">5L</text><text x="17" y="222">0</text><text x="27" y="240">Jan</text><text x="148" y="240">Feb</text><text x="273" y="240">Mar</text><text x="398" y="240">Apr</text></g>
                <path d="M35 154 Q95 131 158 119 C204 110 245 116 284 107 L408 60V220H35Z" fill="url(#sales-fill)"/><path d="M35 184 Q94 166 158 159 C213 153 250 162 284 153 L408 103V220H35Z" fill="url(#purchase-fill)"/>
                <path d="M35 154 Q95 131 158 119 C204 110 245 116 284 107 L408 60" fill="none" stroke="#1477d5" stroke-width="1.9"/><path d="M35 184 Q94 166 158 159 C213 153 250 162 284 153 L408 103" fill="none" stroke="#e5b637" stroke-width="1.9"/>
                @foreach([[35,154],[158,119],[284,107],[408,60]] as [$x,$y])<circle cx="{{ $x }}" cy="{{ $y }}" r="4.2" fill="#1477d5" stroke="white" stroke-width="1.5"/>@endforeach
                @foreach([[35,184],[158,159],[284,153],[408,103]] as [$x,$y])<circle cx="{{ $x }}" cy="{{ $y }}" r="4.2" fill="#e5b637" stroke="white" stroke-width="1.5"/>@endforeach
            </svg>
        </article>
        <article class="card stock-panel"><div class="section-heading flex items-center justify-between"><h2>Stock Alert</h2><button class="text-link" data-panel="stock">View All</button></div><div class="table-scroll"><table class="stock-table"><thead><tr><th>Product</th><th>Current Stock</th><th>Status</th></tr></thead><tbody>
            @foreach(config('dashboard.stock') as [$product,$quantity,$status,$color])<tr data-searchable="{{ $product }}"><td><span class="product-cell"><span class="product-image"><x-icon name="monitor"/></span>{{ $product }}</span></td><td class="{{ $color === 'red' ? 'negative' : '' }}">{{ $quantity }}</td><td><span class="badge {{ $color }}">{{ $status === 'In Stock' ? '✦' : '♥' }} {{ $status }}</span></td></tr>@endforeach
        </tbody></table></div></article>
        <article class="card activities-panel"><div class="section-heading flex items-center justify-between"><h2>Recent Activities</h2><button class="text-link" data-panel="activities">View All</button></div><div class="activities">
            @foreach(config('dashboard.activities') as [$title,$subtitle,$time,$icon,$color])<div class="activity flex items-center" data-searchable="{{ $title }} {{ $subtitle }}"><span class="activity-icon {{ $color }}"><x-icon :name="$icon"/></span><div><strong>{{ $title }}</strong><p>{{ $subtitle }}</p></div><time>{{ $time }}</time></div>@endforeach
        </div></article>
    </section>
    <section class="module-grid grid" aria-label="Business modules">
        @foreach($modules as [$title,$icon,$description])<article class="card module-card"><div class="module-title flex items-center"><span class="module-icon"><x-icon :name="$icon"/></span><h2>{{ $title }}</h2></div><p>{{ $description }}</p><button class="primary-button flex items-center justify-center" data-module="{{ $title }}">Go to {{ $title === 'Customer / Supplier' ? 'Customers/Suppliers' : $title }} <x-icon name="arrow"/></button></article>@endforeach
    </section>
    <section class="bottom-grid grid">
        <article class="card orders-panel"><div class="section-heading flex items-center justify-between"><h2>Recent Purchase Orders</h2><button class="text-link" data-panel="purchases">View All</button></div><div class="table-scroll"><table><thead><tr><th>PO No.</th><th>Supplier</th><th>Date</th><th>Status</th><th>Amount</th></tr></thead><tbody>
            @foreach(config('dashboard.purchases') as [$number,$supplier,$date,$status,$amount])<tr data-searchable="{{ $number }} {{ $supplier }}"><td>{{ $number }}</td><td>{{ $supplier }}</td><td>{{ $date }}</td><td><span class="badge {{ $badge($status) }}">{{ $status }}</span></td><td class="amount">৳ {{ $amount }}</td></tr>@endforeach
        </tbody></table></div></article>
        <article class="card invoices-panel"><div class="section-heading flex items-center justify-between"><h2>Recent Sales Invoices</h2><button class="text-link" data-panel="invoices">View All</button></div><div class="table-scroll"><table><thead><tr><th>Invoice No.</th><th>Customer</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead><tbody>
            @foreach(config('dashboard.invoices') as [$number,$customer,$date,$amount,$status])<tr data-searchable="{{ $number }} {{ $customer }}"><td>{{ $number }}</td><td>{{ $customer }}</td><td>{{ $date }}</td><td class="amount">৳ {{ $amount }}</td><td><span class="badge {{ $badge($status) }}">{{ $status === 'Paid' ? '✦ ' : '' }}{{ $status }}</span></td></tr>@endforeach
        </tbody></table></div></article>
        <article class="card finance-panel"><div class="section-heading flex items-center justify-between"><h2>Financial Summary</h2><select id="finance-period" aria-label="Financial summary period"><option>This Month</option><option>Last Month</option></select></div>
            @foreach(config('dashboard.finances') as [$title,$amount,$change,$icon,$color])<div class="finance-row flex items-center"><span class="finance-icon {{ $color }}"><x-icon :name="$icon"/></span><span>{{ $title }}</span><strong data-current="{{ $amount }}">৳ {{ $amount }}</strong><b class="{{ $color === 'red' ? 'negative' : 'positive' }}">↑ {{ $change }}%</b></div>@endforeach
        </article>
    </section>
    <p id="search-message" role="status" hidden></p>

@endsection

