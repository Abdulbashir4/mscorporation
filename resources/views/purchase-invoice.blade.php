@extends('layouts.app')
@section('title', 'Purchase Invoice')
@section('content')
<section class="overflow-hidden rounded-xl border border-indigo-100 bg-white shadow-sm">
    <header class="flex flex-wrap items-center justify-between gap-3 bg-linear-to-r from-slate-950 to-indigo-700 px-5 py-4 text-white">
        <div><p class="text-xs font-semibold text-indigo-200">Purchase Invoice</p><h1 class="text-xl font-bold">PI-{{ str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</h1></div>
        <a class="rounded-md bg-white/10 px-3 py-2 text-sm font-semibold text-white!" href="{{ route('purchase.invoices.create') }}">+ New Purchase</a>
    </header>
    <div class="space-y-4 p-4 sm:p-5">
        <dl class="grid gap-3 rounded-lg bg-slate-50 p-3 text-sm sm:grid-cols-3">
            <div><dt class="text-slate-600">Supplier</dt><dd class="font-semibold">{{ $invoice->supplier_name }}</dd></div>
            <div><dt class="text-slate-600">Purchase Date</dt><dd class="font-semibold">{{ $invoice->purchase_date }}</dd></div>
            <div><dt class="text-slate-600">Supplier Invoice No.</dt><dd class="font-semibold">{{ $invoice->supplier_reference ?: '—' }}</dd></div>
        </dl>
        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full text-left text-sm">
                <thead class="bg-indigo-50 text-indigo-950"><tr><th class="p-2">#</th><th class="p-2">Product</th><th class="p-2">Unit</th><th class="p-2">Qty</th><th class="p-2 text-right">Unit Price</th><th class="p-2 text-right">Line Total</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($items as $item)
                        <tr><td class="p-2">{{ $loop->iteration }}</td><td class="p-2">{{ $item->product_name }}</td><td class="p-2">{{ $item->unit }}</td><td class="p-2">{{ $item->quantity }}</td><td class="p-2 text-right">৳ {{ number_format($item->unit_price, 2) }}</td><td class="p-2 text-right">৳ {{ number_format($item->line_total, 2) }}</td></tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-indigo-50 font-bold"><tr><th colspan="5" class="p-2 text-right">Total Purchase</th><td class="p-2 text-right">৳ {{ number_format($invoice->total, 2) }}</td></tr></tfoot>
            </table>
        </div>
        @if($invoice->notes)<p class="whitespace-pre-wrap text-sm"><b>Notes:</b> {{ $invoice->notes }}</p>@endif
        <a class="inline-block rounded-md bg-slate-100 px-3 py-2 text-sm font-semibold" href="{{ route('purchase.index') }}">Back to Purchase</a>
    </div>
</section>
@endsection
