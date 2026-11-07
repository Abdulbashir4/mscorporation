@extends('layouts.app')
@section('title', 'New Purchase Invoice')
@section('content')
@php($fieldStyle = 'w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900')
@php($oldItems = array_slice((array) old('items', []), 0, 100) ?: [[]])
<section class="overflow-hidden rounded-xl border border-indigo-100 bg-white shadow-sm">
    <header class="flex flex-wrap items-center justify-between gap-3 bg-linear-to-r from-slate-950 to-indigo-700 px-5 py-4 text-white">
        <div>
            <p class="text-xs font-semibold text-indigo-200">Inventory / Purchase</p>
            <h1 class="text-xl font-bold">New Purchase Invoice</h1>
        </div>
        <a class="rounded-md bg-white/10 px-3 py-2 text-sm font-semibold text-white!" href="{{ route('inventory.index') }}">Back to Inventory</a>
    </header>

    <form id="purchase-invoice-form" class="space-y-5 p-4 sm:p-5" method="post" action="{{ route('purchase.invoices.store') }}">
        @csrf
        <input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">
        @if($errors->any())
            <ul class="space-y-1 rounded-md bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        @endif
        @if($suppliers->isEmpty() || $products->isEmpty())
            <p class="rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-900">Add at least one supplier and one active product before recording a purchase.</p>
        @endif
        <div class="grid gap-3 sm:grid-cols-3">
            <label class="space-y-1 font-semibold">Supplier *
                <select class="{{ $fieldStyle }}" name="supplier_id" required>
                    <option value="">Select supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-1 font-semibold">Purchase Date *
                <input class="{{ $fieldStyle }}" type="date" name="purchase_date" value="{{ old('purchase_date', now('Asia/Dhaka')->toDateString()) }}" required>
            </label>
            <label class="space-y-1 font-semibold">Supplier Invoice No.
                <input class="{{ $fieldStyle }}" name="supplier_reference" maxlength="100" value="{{ old('supplier_reference') }}">
            </label>
        </div>
        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full min-w-[570px] text-left text-sm">
                <thead class="bg-indigo-50 text-indigo-950">
                    <tr><th class="p-2">Product *</th><th class="p-2">Unit</th><th class="p-2">Qty *</th><th class="p-2">Unit Price *</th><th class="p-2 text-right">Line Total</th><th class="p-2"><span class="sr-only">Actions</span></th></tr>
                </thead>
                <tbody id="purchase-invoice-lines" class="divide-y divide-slate-100">
                    @foreach($oldItems as $index => $item)
                        <tr>
                            <td class="p-2"><select data-purchase-field="product_id" name="items[{{ $index }}][product_id]" class="{{ $fieldStyle }}" aria-label="Product {{ $loop->iteration }}" required>
                                <option value="">Select product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->product_id }}" data-unit="{{ $product->unit }}" @selected((string) ($item['product_id'] ?? '') === (string) $product->product_id)>{{ $product->product_name }} · {{ $product->product_code ?: $product->product_id }}</option>
                                @endforeach
                            </select></td>
                            <td class="p-2" data-purchase-unit>—</td>
                            <td class="p-2"><input data-purchase-field="quantity" name="items[{{ $index }}][quantity]" class="{{ $fieldStyle }} max-w-24" type="number" min="1" max="100000" step="1" value="{{ $item['quantity'] ?? 1 }}" aria-label="Quantity {{ $loop->iteration }}" required></td>
                            <td class="p-2"><input data-purchase-field="unit_price" name="items[{{ $index }}][unit_price]" class="{{ $fieldStyle }} max-w-32" type="number" min="0" max="999999.99" step="0.01" inputmode="decimal" value="{{ $item['unit_price'] ?? '' }}" aria-label="Unit price {{ $loop->iteration }}" required></td>
                            <td class="p-2 text-right font-semibold tabular-nums"><output data-purchase-line-total>৳ 0.00</output></td>
                            <td class="p-2"><button type="button" data-purchase-remove class="rounded-md p-1 text-rose-700 hover:bg-rose-50" aria-label="Remove line {{ $loop->iteration }}"><x-icon name="trash" class="size-4"/></button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <button id="add-purchase-invoice-line" type="button" class="rounded-md bg-indigo-50! px-3 py-2 text-sm font-semibold text-indigo-800">+ Add Product</button>
            <div class="rounded-md bg-indigo-50 px-4 py-2 text-sm font-bold text-indigo-950">Total Purchase <output id="purchase-invoice-total" class="ml-3 tabular-nums">৳ 0.00</output></div>
        </div>
        <label class="block space-y-1 font-semibold">Notes
            <textarea class="{{ $fieldStyle }}" name="notes" maxlength="2000" rows="2">{{ old('notes') }}</textarea>
        </label>
        <div class="flex justify-end">
            <button type="submit" class="rounded-md bg-indigo-600! px-4 py-2 text-sm font-semibold text-white disabled:opacity-50" @disabled($suppliers->isEmpty() || $products->isEmpty())>Save Purchase Invoice</button>
        </div>
    </form>
</section>
@endsection
