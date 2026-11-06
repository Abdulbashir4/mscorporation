@extends('layouts.app')
@section('title', 'Inventory')
@section('content')
@php
$products = config('inventory.products');
$categoryColors = ['Monitoring'=>'red','Life Support'=>'red','Imaging'=>'sky','Diagnostic'=>'green','Emergency'=>'red','OT Equipment'=>'sky','Furniture'=>'purple'];
$money = function (int $value): string { $digits = (string) $value; $last = substr($digits, -3); $prefix = substr($digits, 0, -3); return ($prefix ? preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $prefix).',' : '').$last; };
@endphp
<div class="inventory-page">
    <div class="inventory-heading flex items-center justify-between">
        <div class="flex items-center"><span class="inventory-heading-icon"><x-icon name="box"/></span><div><h1>Inventory</h1><p>Manage your products, stock and warehouse</p></div></div>
        <button class="primary-button add-product" data-inventory-action="Add New Product"><span>＋</span> Add New Product</button>
    </div>
    <section class="inventory-stats grid" aria-label="Inventory summary">
        @foreach([['Total Products','248','12','box','blue'],['Total Stock Quantity','5,842','8','box','green'],['Low Stock Items','7','3','warning','amber'],['Total Warehouses','2','','warehouse','purple'],['Total Inventory Value','৳ 1,28,45,000','10','money','teal']] as [$label,$value,$change,$icon,$color])
        <article class="card inventory-stat flex items-start"><span class="inventory-stat-icon {{ $color }}"><x-icon :name="$icon"/></span><div><p>{{ $label }}</p><strong>{{ $value }}</strong><small><b class="{{ $label === 'Low Stock Items' ? 'negative' : 'positive' }}">{{ $change ? '↑ '.$change.'%' : '—' }}</b> {{ $change ? 'vs last month' : 'no change' }}</small></div></article>
        @endforeach
    </section>
    <div class="inventory-columns grid">
        <section class="card product-list" aria-label="Product list">
            <div class="inventory-filters flex items-center">
                <label class="product-search flex items-center"><input type="search" id="product-search" placeholder="Search by product name, code, brand, model..." aria-label="Search products"><x-icon name="search"/></label>
                <select id="category-filter" aria-label="Filter by category"><option value="">All Categories</option>@foreach(array_unique(array_column($products, 5)) as $category)<option>{{ $category }}</option>@endforeach</select>
                <select id="brand-filter" aria-label="Filter by brand"><option value="">All Brands</option>@foreach(array_unique(array_column($products, 3)) as $brand)<option>{{ $brand }}</option>@endforeach</select>
                <select id="warehouse-filter" aria-label="Filter by warehouse"><option value="">All Warehouses</option><option>Main Warehouse</option><option>Secondary Warehouse</option></select>
                <select id="status-filter" aria-label="Filter by status"><option value="">All Status</option><option>In Stock</option><option>Low Stock</option></select>
                <button class="export-icon" data-inventory-action="Export Products" aria-label="Export products"><x-icon name="download"/></button>
            </div>
            <div class="table-scroll"><table class="products-table"><thead><tr><th>#</th><th>Image</th><th>Product Name</th><th>Code</th><th>Brand</th><th>Model</th><th>Category</th><th>Stock Qty</th><th>Unit Price</th><th>Status</th><th>Actions</th></tr></thead><tbody>
                @foreach($products as [$name,$description,$code,$brand,$model,$category,$quantity,$price,$status,$warehouse])
                <tr data-product data-name="{{ $name }}" data-code="{{ $code }}" data-description="{{ $description }}" data-brand="{{ $brand }}" data-model="{{ $model }}" data-category="{{ $category }}" data-warehouse="{{ $warehouse }}" data-status="{{ $status }}" data-quantity="{{ $quantity }}" data-price="{{ $price }}">
                    <td>{{ $loop->iteration }}</td><td><span class="equipment-thumbnail equipment-{{ $loop->iteration }}"><x-icon :name="in_array($category, ['Furniture','OT Equipment']) ? ($category === 'Furniture' ? 'bed' : 'light') : 'monitor'"/></span></td>
                    <td class="product-name"><strong>{{ $name }}</strong><small>{{ $description }}</small></td><td>{{ $code }}</td><td>{{ $brand }}</td><td>{{ $model }}</td><td><span class="badge {{ $categoryColors[$category] }}">{{ $category }}</span></td><td>{{ $quantity }}</td><td>৳ {{ $money($price) }}</td><td><span class="badge {{ $status === 'Low Stock' ? 'amber' : 'green' }}">{{ $status === 'Low Stock' ? '♥' : '✦' }} {{ $status }}</span></td>
                    <td><div class="row-actions flex"><button data-product-edit="{{ $code }}" aria-label="Edit {{ $name }}"><x-icon name="edit"/></button><button data-product-details="{{ $code }}" aria-label="Details for {{ $name }}"><x-icon name="more"/></button></div></td>
                </tr>
                @endforeach
            </tbody></table></div>
            <div class="product-empty" id="product-empty" hidden>No products match your search. Try different filters.</div>
            <div class="inventory-pagination flex items-center justify-between"><span id="product-result-count" role="status">Showing 1 to 10 of 10 preview products</span><div class="flex items-center"><button disabled aria-label="Previous page">‹</button><button class="current" aria-current="page">1</button><button disabled aria-label="Next page">›</button></div></div>
        </section>
        <aside class="inventory-right flex flex-col">
            <section class="card quick-actions"><h2><span class="quick-heading-icon">✦</span> Quick Actions</h2>
                @foreach([['Add New Product','plus'],['Stock Adjustment','balance'],['Stock Transfer','arrow'],['Import Products','upload'],['Export Products','download']] as [$action,$icon])<button class="quick-action flex items-center" data-inventory-action="{{ $action }}"><span class="quick-action-icon"><x-icon :name="$icon"/></span><span>{{ $action }}</span><x-icon name="chevron"/></button>@endforeach
            </section>
            <section class="card inventory-alerts"><div class="flex items-center justify-between"><h2><x-icon name="warning"/>Low Stock Alert</h2><button class="text-link" data-inventory-action="Low Stock Alert">View All</button></div><table><thead><tr><th>Product</th><th>Current Stock</th><th>Min. Stock</th></tr></thead><tbody>@foreach(config('inventory.alerts') as [$name,$current,$minimum])<tr><td>{{ $name }}</td><td class="negative">{{ $current }}</td><td>{{ $minimum }}</td></tr>@endforeach</tbody></table></section>
            <section class="card inventory-value"><h2>Stock Value by Category</h2><div class="value-chart flex items-center"><div class="inventory-donut" role="img" aria-label="Stock value: Monitoring 28%, Imaging 22%, Life Support 18%, Diagnostic 15%, OT Equipment 10%, Others 7%"><div><strong>৳ 1.28 Cr</strong><small>Total Value</small></div></div><ul>@foreach([['Monitoring','28','#2483cf'],['Imaging','22','#efb43f'],['Life Support','18','#27b995'],['Diagnostic','15','#ffc33c'],['OT Equipment','10','#ef5b71'],['Others','7','#9147c7']] as [$category,$percent,$color])<li><i style="background:{{ $color }}"></i><span>{{ $category }}</span><b>{{ $percent }}%</b></li>@endforeach</ul></div></section>
        </aside>
    </div>
    <p class="inventory-preview-note">Reference preview data · MySQL connection required for live inventory.</p>
</div>
@endsection
