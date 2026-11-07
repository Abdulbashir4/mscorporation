<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#09294d"><title>@yield('title', 'Dashboard') | MS Corporation</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@php
$modules = config('dashboard.modules');
$badge = fn($status) => match($status) {'Pending'=>'amber', 'Partial', 'Partially Paid'=>'sky', default=>'green'};
@endphp
<header class="topbar flex items-center justify-between">
    <a href="/" class="brand flex items-center">@if($companyProfile['logo'])<img class="company-header-logo" src="{{ $companyProfile['logo'] }}" alt="{{ $companyProfile['name'] }} logo">@else<span class="monogram">MS</span>@endif<span><strong>{{ $companyProfile['name'] }}</strong><small>{{ $companyProfile['business'] }}</small></span></a>
    <label class="search flex items-center"><x-icon name="search"/><input id="dashboard-search" type="search" placeholder="{{ request()->routeIs('purchase.*') ? 'Search purchase orders, suppliers, products, etc...' : (request()->routeIs('sales.*') ? 'Search customers, products, invoices, etc...' : 'Search products, invoices, customers, etc...') }}" aria-label="Search dashboard"><kbd>/</kbd></label>
    <div class="header-actions flex items-center">
        <button class="mobile-menu" aria-label="Open navigation" aria-expanded="false"><x-icon name="menu"/></button>
        <button class="notification-button relative" data-panel="notifications" aria-label="Notifications"><x-icon name="bell"/><span class="notification-count">3</span></button>
        <button data-panel="calendar" class="calendar-button" aria-label="Open calendar"><x-icon name="calendar"/></button>
        <button class="profile flex items-center" data-panel="profile"><span class="avatar"><x-icon name="user"/></span><span><strong>Admin</strong><small>Administrator</small></span><x-icon name="down"/></button>
    </div>
</header>
<aside class="sidebar flex flex-col" id="sidebar">
    <nav aria-label="Main navigation">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif><x-icon name="home"/><span>Dashboard</span></a>
        @php
        $submenus = [
            'Inventory' => ['Product List', 'Stock Module', 'Purchase', 'Serial / Batch', 'Expiry Tracking', 'Warehouse'],
            'Purchase' => ['Purchase Order', 'Goods Receive', 'Supplier', 'Import Tracking', 'Landed Cost', 'Purchase Invoice', 'Payment'],
            'Sales' => ['Sales Order', 'Quotation', 'Invoice', 'Customer', 'Price List'],
            'Customer / Supplier' => ['Customers', 'Suppliers', 'Contact Groups'],
            'Accounting' => ['Dashboard', 'Journal Entry', 'Chart of Accounts', 'Ledger', 'Bank & Cash', 'Receivables', 'Payables', 'Financial Reports'],
            'Reports' => ['Sales Reports', 'Purchase Reports', 'Inventory Reports', 'Accounting Reports', 'Customer Reports', 'Supplier Reports', 'Import Reports', 'All Reports'],
        ];
        @endphp
        @foreach($modules as [$title,$icon,$description])
        @php
        $menuId = 'sidebar-menu-'.$loop->index;
        $isOpen = ($title === 'Inventory' && request()->routeIs('inventory.*')) || ($title === 'Purchase' && request()->routeIs('purchase.*')) || ($title === 'Sales' && request()->routeIs('sales.*')) || ($title === 'Customer / Supplier' && request()->routeIs('contacts.*')) || ($title === 'Accounting' && request()->routeIs('accounting.*')) || ($title === 'Reports' && request()->routeIs('reports.*'));
        @endphp
        <button type="button" class="nav-item {{ $isOpen ? 'active' : '' }}" data-sidebar-menu="{{ $menuId }}" @if($title === 'Purchase') data-menu-url="{{ route('purchase.index') }}" @elseif($title === 'Sales') data-menu-url="{{ route('sales.index') }}" @elseif($title === 'Customer / Supplier') data-menu-url="{{ route('contacts.index') }}" @elseif($title === 'Accounting') data-menu-url="{{ route('accounting.index') }}" @elseif($title === 'Reports') data-menu-url="{{ route('reports.index') }}" @endif aria-controls="{{ $menuId }}" aria-expanded="{{ $isOpen ? 'true' : 'false' }}"><x-icon :name="$icon"/><span>{{ $title === 'Customer / Supplier' ? 'Customers / Suppliers' : $title }}</span><x-icon name="chevron" class="nav-chevron"/></button>
        <div id="{{ $menuId }}" class="inventory-subnav" aria-label="{{ $title }} navigation" @if(!$isOpen) hidden @endif>
            @foreach($submenus[$title] as $section)
                @if($title === 'Inventory' && $section === 'Product List')
                <a href="{{ route('inventory.index') }}" @if(request()->routeIs('inventory.index')) class="selected" aria-current="page" @endif>{{ $section }}</a>
                @elseif($title === 'Inventory' && $section === 'Stock Module')
                <a href="{{ route('inventory.stock.index') }}" @if(request()->routeIs('inventory.stock.*')) class="selected" aria-current="page" @endif>{{ $section }}</a>
                @elseif(($title === 'Inventory' && $section === 'Purchase') || ($title === 'Purchase' && $section === 'Purchase Invoice'))
                <a href="{{ route('purchase.invoices.create') }}" @if(request()->routeIs('purchase.invoices.create')) class="selected" aria-current="page" @endif>{{ $section }}</a>
                @elseif($title === 'Inventory')
                <button type="button" data-inventory-action="{{ $section }}">{{ $section }}</button>
                @elseif(($title === 'Purchase' && $section === 'Supplier') || ($title === 'Customer / Supplier' && in_array($section, ['Customers', 'Suppliers'])))
                <a href="{{ route($section === 'Customers' ? 'customers.index' : 'suppliers.index') }}">{{ $section }}</a>
                @elseif($title === 'Purchase')
                <button type="button" data-purchase-action="{{ $section }}">{{ $section }}</button>
                @elseif($title === 'Sales')
                <button type="button" data-sales-action="{{ $section }}">{{ $section }}</button>
                @elseif($title === 'Customer / Supplier')
                <button type="button" data-contact-nav="{{ $section }}">{{ $section }}</button>
                @elseif($title === 'Accounting')
                <button type="button" data-account-action="{{ $section }}">{{ $section }}</button>
                @elseif($title === 'Reports')
                <button type="button" data-report-nav="{{ $section }}">{{ $section }}</button>
                @else
                <button type="button" data-module="{{ $title }}" data-submodule="{{ $section }}">{{ $section }}</button>
                @endif
            @endforeach
        </div>
        @endforeach
        <div class="nav-divider"></div>
        <button class="nav-item" data-panel="settings"><x-icon name="settings"/><span>Settings</span></button>
        <button class="nav-item" data-panel="help"><x-icon name="help"/><span>Help &amp; Support</span></button>
    </nav>
    <div class="sidebar-caption"><div><strong>{{ $companyProfile['name'] }}</strong><p>{{ $companyProfile['tagline'] }}</p></div><span class="sidebar-version">ERP v1.0</span></div>
</aside>
<div class="sidebar-backdrop" hidden></div>
<div class="workspace"><main class="dashboard">
@yield('content')
</main></div>
<template id="company-info-template">
    <article class="company-info-card">
        <div class="company-info-banner"><span class="company-info-mark">MS</span><div><h3>MS Corporation</h3><p>Medical Equipment Importer &amp; Supplier</p></div><span class="company-info-label">Company Profile</span></div>
        <div class="company-info-content"><h4>Company Information</h4><dl class="company-info-fields">
            <div><dt>Company Name</dt><dd>MS Corporation</dd></div>
            <div><dt>Business Type</dt><dd>Medical Equipment Importer &amp; Supplier</dd></div>
            <div><dt>Phone</dt><dd class="company-info-missing">Not provided</dd></div>
            <div><dt>Email</dt><dd class="company-info-missing">Not provided</dd></div>
            <div class="company-info-wide"><dt>Address</dt><dd class="company-info-missing">Not provided</dd></div>
            <div><dt>Trade License No.</dt><dd class="company-info-missing">Not provided</dd></div>
            <div><dt>BIN / VAT No.</dt><dd class="company-info-missing">Not provided</dd></div>
        </dl><div class="company-info-tagline">Better Equipment. Better Healthcare.</div></div>
    </article>
</template>
<dialog id="detail-dialog"><div class="dialog-heading flex items-center justify-between"><h2 id="dialog-title"></h2><button id="close-dialog" aria-label="Close dialog"><x-icon name="close"/></button></div><div id="dialog-body"></div></dialog>
</body></html>
















