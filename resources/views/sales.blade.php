@extends('layouts.app')
@section('title', 'Sales & Invoice')
@section('content')
<div class="sales-page">
    <div id="sales-overview-content">
    <div class="purchase-heading flex items-center justify-between">
        <div class="flex items-center"><span class="purchase-page-icon"><x-icon name="cart"/></span><div><h1>Sales &amp; Invoice</h1><p>Manage your quotations, sales orders, invoices and customer information.</p></div></div>
        <div class="purchase-heading-actions flex items-center"><button class="purchase-date sales-date flex items-center" data-sales-action="Date Range"><x-icon name="calendar"/><span>All dates</span><x-icon name="down"/></button><button class="primary-button create-purchase flex items-center" data-sales-action="Create Invoice"><span>＋</span>Create New Invoice<x-icon name="down"/></button></div>
    </div>
    <section class="sales-stats purchase-stats grid" aria-label="Sales summary">
        @foreach([['Total Sales',$businessRecords['sales.total'],'','clipboard','blue'],['Total Invoices',count($businessRecords['sales.invoices']),'','calculator','green'],['Total Customers',count($businessRecords['sales.customers']),'','users','purple'],['Outstanding Receivables',$businessRecords['sales.receivable'],'','clock','amber']] as [$label,$value,$change,$icon,$color])
        <article class="card purchase-stat flex items-start"><span class="purchase-stat-icon {{ $color }}"><x-icon :name="$icon"/></span><div><p>{{ $label }}</p><strong>{{ $value }}</strong><small><b class="{{ $loop->last ? 'negative' : 'positive' }}">{{ $change ?: "—" }}</b></small></div></article>
        @endforeach
    </section>
    <section class="sales-top-grid grid">
        <article class="card sales-overview"><div class="purchase-section-heading flex items-center justify-between"><h2>Sales Overview (Reference)</h2><div class="purchase-chart-legend flex"><span><i class="order-dot"></i>Sales</span><span><i class="payable-dot"></i>Invoices</span></div></div>
            <svg class="sales-bar-chart" viewBox="0 0 500 230" role="img" aria-label="Reference sales increased from 3 lakh in January to 12 lakh in April; invoice amounts increased from 1.5 lakh to 9 lakh.">
                <defs><linearGradient id="sales-bar-blue" x2="1" y2="0"><stop stop-color="#278bec"/><stop offset="1" stop-color="#0870dd"/></linearGradient></defs>
                <g stroke="#eaf0f5" stroke-width=".8"><path d="M32 16H490M32 41H490M32 66H490M32 91H490M32 116H490M32 141H490M32 166H490M32 191H490M32 16V191"/></g>
                <g class="purchase-chart-labels"><text x="3" y="20">14L</text><text x="3" y="45">12L</text><text x="3" y="70">10L</text><text x="9" y="95">8L</text><text x="9" y="120">6L</text><text x="9" y="145">4L</text><text x="9" y="170">2L</text><text x="15" y="195">0</text><text x="72" y="214">Jan</text><text x="193" y="214">Feb</text><text x="313" y="214">Mar</text><text x="433" y="214">Apr</text></g>
                @foreach([[65,153],[185,116],[305,79],[425,41]] as [$x,$y])<rect x="{{ $x }}" y="{{ $y }}" width="33" height="{{ 191-$y }}" rx="3" fill="url(#sales-bar-blue)"/>@endforeach
                <path d="M82 172 202 141 322 116 442 79" fill="none" stroke="#dcae34" stroke-width="1.7"/>
                @foreach([[82,172],[202,141],[322,116],[442,79]] as [$x,$y])<circle cx="{{ $x }}" cy="{{ $y }}" r="3.8" fill="#e6b846" stroke="white" stroke-width="1.2"/>@endforeach
            </svg>
        </article>
        <article class="card sales-top-products"><div class="purchase-section-heading flex items-center justify-between"><h2>Top Selling Products</h2><button class="text-link" data-sales-action="Top Selling Products">View All</button></div><x-purchase-table :headers="['#','Product Name','Quantity','Sales Amount']" :rows="$businessRecords['sales.top_products']"/></article>
        <article class="card sales-quick-actions"><h2>Quick Actions</h2>
            @foreach([['Create Quotation','clipboard','purple'],['Create Sales Order','cart','blue'],['Create Invoice','calculator','green'],['Add Customer','users','teal'],['View Price List','clipboard','orange']] as [$action,$icon,$color])<button class="sales-quick-action flex items-center" data-sales-action="{{ $action }}"><span class="purchase-stat-icon {{ $color }}"><x-icon :name="$icon"/></span><span>{{ $action }}<x-icon name="chevron"/></span></button>@endforeach
        </article>
    </section>
    <section class="sales-bottom-grid grid">
        <div class="sales-records-column flex flex-col">
            <article class="card sales-records">
                <div class="sales-tabs flex" role="tablist" aria-label="Sales records">@foreach(['invoices'=>'Recent Invoices','orders'=>'Sales Orders','quotations'=>'Quotations','customers'=>'Customers'] as $key=>$label)<button role="tab" id="sales-tab-{{ $key }}" aria-controls="sales-panel-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" tabindex="{{ $loop->first ? '0' : '-1' }}" data-sales-tab="{{ $key }}">{{ $label }}</button>@endforeach</div>
                @foreach(['invoices','orders','quotations'] as $key)
                <div id="sales-panel-{{ $key }}" role="tabpanel" aria-labelledby="sales-tab-{{ $key }}" @if(!$loop->first) hidden @endif>
                    <div class="table-scroll"><table class="sales-records-table"><thead><tr><th>{{ $key === 'invoices' ? 'Invoice No.' : ($key === 'orders' ? 'Order No.' : 'Quotation No.') }}</th><th>Date</th><th>Customer</th><th>Total Amount</th><th>Status</th><th>Action</th></tr></thead><tbody>
                    @foreach($businessRecords['sales.'.$key] as [$number,$date,$customer,$amount,$status,$source,$recordId])<tr data-sales-record data-searchable="{{ $number.' '.$customer }}" data-date="{{ $date === '—' ? '' : \Carbon\Carbon::parse($date)->format('Y-m-d') }}"><td><strong>{{ $number }}</strong></td><td>{{ $date }}</td><td>{{ $customer }}</td><td>{{ $amount }}</td><td><span class="badge {{ in_array($status,['Paid','Confirmed','Accepted']) ? 'green' : ($status === 'Pending' ? 'amber' : 'sky') }}">{{ $status === 'Paid' ? '✦ ' : '' }}{{ $status }}</span></td><td><button class="invoice-view" data-sales-view="{{ $number }}">View</button> @if($key === 'invoices')<button type="button" class="invoice-view" data-invoice-print="{{ route('sales.invoices.print', ['source' => $source, 'id' => $recordId]) }}">Print</button>@endif</td></tr>@endforeach
                    </tbody></table></div>
                </div>
                @endforeach
                <div id="sales-panel-customers" role="tabpanel" aria-labelledby="sales-tab-customers" hidden><div class="table-scroll"><table class="sales-records-table"><thead><tr><th>#</th><th>Customer</th><th>Status</th><th>Action</th></tr></thead><tbody>@foreach($businessRecords['sales.customers'] as $customer)<tr data-sales-record data-searchable="{{ $customer }}"><td>{{ $loop->iteration }}</td><td>{{ $customer }}</td><td><span class="badge green">Active</span></td><td><button class="invoice-view" data-sales-view="{{ $customer }}">View</button></td></tr>@endforeach</tbody></table></div></div>
                <p id="sales-print-status" role="status" class="text-sm text-rose-700"></p><div class="sales-records-footer flex items-center justify-between"><span id="sales-record-count" role="status">{{ count($businessRecords['sales.invoices']) }} records</span><div class="flex"><button disabled aria-label="Previous page">‹</button><button aria-current="page">1</button><button disabled aria-label="Next page">›</button></div></div>
            </article>
            <div class="sales-tip flex items-center"><x-icon name="help"/><p>Track your sales performance, manage customer payments and generate<br class="sales-tip-break"> professional invoices — all in one place.</p><span>Better Equipment<br>Better Healthcare</span></div>
        </div>
    </section>
    </div>
    <section id="invoice-builder-home" aria-labelledby="invoice-canvas-title" hidden>
        <header class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h1 id="invoice-canvas-title" class="text-2xl font-bold">Create Invoice</h1>
            <button type="button" id="back-to-sales" class="primary-button">Back to Sales</button>
        </header>
        <article class="card invoice-builder" id="invoice-builder">
            <form id="invoice-form">
                <div class="invoice-fields grid">
                    <div class="invoice-customer relative">
                        <label for="invoice-customer-name">Customer <b>*</b></label>
                        <input id="invoice-customer-name" name="customer" type="text" required autocomplete="off"
                            placeholder="Type or select a customer" role="combobox" aria-autocomplete="list"
                            aria-expanded="false" aria-controls="invoice-customer-suggestions">
                        <ul id="invoice-customer-suggestions" role="listbox" aria-label="Customer suggestions"
                            class="absolute z-10 mt-1 max-h-52 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white p-1 shadow-lg" hidden></ul>
                        <template id="invoice-customer-options">
                            @foreach($businessRecords['sales.customers'] as $customer)
                                <span data-address="{{ $businessRecords['sales.customer_details'][$customer]['address'] ?? '' }}" data-phone="{{ $businessRecords['sales.customer_details'][$customer]['phone'] ?? '' }}">{{ $customer }}</span>
                            @endforeach
                        </template>
                    </div>
                    <label>Invoice Date <b>*</b><input name="date" type="date" value="{{ now('Asia/Dhaka')->toDateString() }}" required></label>
                </div>
                <section id="invoice-product-picker" class="invoice-product-picker" aria-labelledby="invoice-product-picker-title" hidden>
                    <header>
                        <div>
                            <h2 id="invoice-product-picker-title">Select Product</h2>
                            <p>Category / Subcategory / Brand / Model</p>
                        </div>
                        <button type="button" id="close-invoice-product-picker" aria-label="Close product picker"><x-icon name="close"/></button>
                    </header>
                    <div class="invoice-product-search">
                        <x-icon name="search"/>
                        <input id="invoice-product-search" type="search" placeholder="Search name, code, category, brand or model" aria-label="Search products">
                    </div>
                    <p id="invoice-product-count" class="invoice-product-count" role="status"></p>
                    <div id="invoice-product-tree" class="invoice-product-tree"></div>
                </section>
                <div class="table-scroll invoice-items-scroll"><table class="invoice-items"><thead><tr><th>#</th><th>Product</th><th>Unit</th><th>Qty</th><th>Unit Price</th><th>Discount</th><th>Total</th><th></th></tr></thead><tbody id="invoice-lines">
                    <tr class="invoice-line">
                        <td class="line-number">1</td>
                        <td><button type="button" class="line-product-picker" aria-expanded="false" aria-controls="invoice-product-picker"><span>Select Product</span><x-icon name="down"/></button>
                        <select class="line-product" hidden>
                            <option value="">Select Product</option>
                            @foreach($businessRecords['sales.products'] as [$product, $price, $unit, $id, $code, $category, $subcategory, $brand, $model])
                                <option value="{{ $id }}" data-price="{{ $price }}" data-unit="{{ $unit }}" data-code="{{ $code }}"
                                    data-category="{{ $category }}" data-subcategory="{{ $subcategory }}" data-brand="{{ $brand }}" data-model="{{ $model }}">{{ $product }}</option>
                            @endforeach
                        </select></td>
                        <td><input class="line-unit" type="text" maxlength="40" placeholder="e.g. pcs" aria-label="Unit 1"></td>
                        <td><input class="line-quantity" type="number" min="1" step="1" value="1" required aria-label="Quantity 1"></td>
                        <td><input class="line-price" type="number" min="0" step="0.01" required disabled aria-label="Unit price 1"></td>
                        <td><input class="line-discount" type="number" min="0" step="0.01" value="0" inputmode="decimal" aria-label="Discount 1 in BDT"></td>
                        <td class="line-total"></td>
                        <td><button type="button" class="remove-invoice-line" aria-label="Remove item 1"><x-icon name="trash"/></button></td>
                    </tr>
                </tbody></table></div>
                <div class="invoice-calculation flex items-start justify-between"><button type="button" class="add-invoice-item flex items-center" id="add-invoice-item"><x-icon name="plus"/>Add Item</button><div class="invoice-totals"><div><span>Sub Total</span><output id="invoice-subtotal">৳ 0</output></div><div><label for="invoice-discount">Invoice Discount</label><span class="invoice-discount-wrap">৳ <input id="invoice-discount" name="discount" type="number" inputmode="decimal" min="0" value="0" step="0.01" aria-label="Discount in BDT"></span></div><div class="invoice-grand-total"><strong>Total Amount</strong><output id="invoice-total">৳ 0</output></div><div><label for="invoice-paid">Paid Amount</label><span class="invoice-discount-wrap">৳ <input id="invoice-paid" name="paid" type="number" inputmode="decimal" min="0" value="0" step="0.01"></span></div><div><strong>Balance Due</strong><output id="invoice-balance">৳ 0</output></div></div></div>
                <div class="invoice-buttons flex"><button type="submit" class="primary-button flex items-center"><x-icon name="printer"/>Save &amp; Print</button><button type="button" class="save-invoice-draft flex items-center" id="save-invoice-draft"><x-icon name="clipboard"/>Save Draft</button></div>
                <p class="invoice-status" id="invoice-status" role="status">Drafts are saved in this browser.</p>
            </form>
        </article>
    </section>
    <p id="search-message" role="status" hidden></p>
</div>
@include('invoice-print')
@endsection
