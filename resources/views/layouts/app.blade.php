<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#09294d"><title>@yield('title', 'Dashboard') | MS Corporation</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@php
$modules = config('dashboard.modules');
$badge = fn($status) => match($status) {'Pending'=>'amber', 'Partial', 'Partially Paid'=>'sky', default=>'green'};
@endphp
<header class="topbar flex items-center justify-between">
    <a href="/" class="brand flex items-center"><span class="monogram">MS</span><span><strong>MS Corporation</strong><small>Medical Equipment Importer &amp; Supplier</small></span></a>
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
            'Inventory' => ['Product List', 'Stock Adjustment', 'Stock Transfer', 'Serial / Batch', 'Expiry Tracking', 'Warehouse'],
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
                <a href="{{ route('inventory.index') }}" @if(request()->routeIs('inventory.*')) class="selected" aria-current="page" @endif>{{ $section }}</a>
                @elseif($title === 'Inventory')
                <button type="button" data-inventory-action="{{ $section }}">{{ $section }}</button>
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
    <div class="sidebar-caption"><div><strong>MS Corporation</strong><p>Better Equipment. Better Healthcare.</p></div><span class="sidebar-version">ERP v1.0</span></div>
</aside>
<div class="sidebar-backdrop" hidden></div>
<div class="workspace"><main class="dashboard">
@yield('content')
</main></div>
<dialog id="detail-dialog"><div class="dialog-heading flex items-center justify-between"><h2 id="dialog-title"></h2><button id="close-dialog" aria-label="Close dialog"><x-icon name="close"/></button></div><div id="dialog-body"></div></dialog>
</body></html>













