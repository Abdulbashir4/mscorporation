@extends('layouts.app')
@section('title', $product ? 'Edit Product' : 'Add New Product')
@section('content')
@php($inputStyle = 'h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-indigo-500 aria-invalid:border-rose-500')
@php($fromStock = request()->query('from') === 'stock')
@php($formContext = $fromStock ? ['from' => 'stock'] : [])
<section class="mx-auto max-w-3xl overflow-hidden rounded-xl border border-indigo-100 bg-white shadow-sm">
    <header class="flex items-center gap-3 bg-linear-to-r from-slate-950 to-indigo-700 px-4 py-4 text-white sm:px-5">
        <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-white/15"><x-icon name="box"/></span>
        <div>
            <p class="text-xs font-medium text-indigo-200">Inventory</p>
            <h1 class="text-lg font-bold">{{ $product ? 'Edit Product' : 'Add New Product' }}</h1>
        </div>
    </header>
    <form class="space-y-4 p-4 sm:p-5" method="post" enctype="multipart/form-data" action="{{ $product ? route('inventory.products.update', ['product' => $product->product_id] + $formContext) : route('inventory.products.store', $formContext) }}">
        @csrf
        @if($product)
            @method('PATCH')
        @endif

        <div class="grid gap-x-4 gap-y-3 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-2.5 sm:col-span-full">
                <div class="flex items-center gap-3">
                    @if($product?->photo_path)
                        <img class="size-10 shrink-0 rounded-md border border-indigo-200 object-cover" src="{{ route('inventory.products.image', $product->product_id) }}" alt="{{ $product->product_name }}">
                    @endif
                    <div class="min-w-0">
                        <label for="image" class="mb-1 block text-xs font-semibold text-indigo-950">Product Image</label>
                        <input id="image" name="image" type="file" accept="image/*" class="max-w-full text-xs text-slate-600 file:mr-2 file:cursor-pointer file:rounded-md file:border-0 file:bg-white file:px-2.5 file:py-1.5 file:font-semibold file:text-indigo-700">
                    </div>
                </div>
                @error('image')
                    <p class="mt-1 text-sm text-rose-700" role="alert">{{ $message }}</p>
                @enderror
            </div>
            @foreach([
                'product_name' => ['Product Name', 300, true, $product?->product_name],
                'product_code' => ['Code', 100, true, $product?->product_code ?? $product?->product_id],
                'brand' => ['Brand', 100, false, $product?->brand],
                'model' => ['Model', 100, false, $product?->model],
                'stock_quantity' => ['Stock Qty', null, true, $quantity],
                'price_a' => ['Unit Price (BDT)', null, true, str_replace(',', '', $product?->price_a ?? '')],
            ] as $field => [$label, $maxLength, $required, $value])
                @if(! $product && in_array($field, ['stock_quantity', 'price_a'], true))
                    @continue
                @endif
                <div @class(['sm:col-span-2' => $field === 'product_name'])>
                    <label for="{{ $field }}" class="mb-1 block text-xs font-semibold text-slate-700">{{ $label }} @if($required)<span class="text-rose-600" aria-hidden="true">*</span>@endif</label>
                    <input id="{{ $field }}" name="{{ $field }}" class="{{ $inputStyle }}"
                        value="{{ ! $product && $field === 'product_code' ? 'MS- (Auto generated)' : old($field, $value) }}"
                        @readonly(! $product && $field === 'product_code')
                        @if($maxLength) maxlength="{{ $maxLength }}" @else type="number" min="0" step="{{ $field === 'price_a' ? '0.01' : '1' }}" @endif
                        @if(in_array($field, ['brand', 'model'], true)) list="product-{{ $field }}-options" @endif
                        @required($required)
                        @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>
                    @error($field)
                        <p id="{{ $field }}-error" class="mt-1 text-sm text-rose-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            @foreach(['brand', 'model'] as $type)
                <datalist id="product-{{ $type }}-options">
                    @foreach($options->get($type, []) as $option)
                        <option value="{{ $option->name }}"></option>
                    @endforeach
                </datalist>
            @endforeach

            <div>
                <label for="unit" class="mb-1 block text-xs font-semibold text-slate-700">Unit</label>
                <select id="unit" name="unit" class="{{ $inputStyle }}"
                    @error('unit') aria-invalid="true" aria-describedby="unit-error" @enderror>
                    @foreach($units as $unit)
                        <option value="{{ $unit }}" @selected(old('unit', $product?->unit ?? 'pcs') === $unit)>{{ $unit }}</option>
                    @endforeach
                </select>
                @error('unit')
                    <p id="unit-error" class="mt-1 text-sm text-rose-700" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="category_id" class="mb-1 block text-xs font-semibold text-slate-700">Category</label>
                <select id="category_id" name="category_id" class="{{ $inputStyle }}"
                    @error('category_id') aria-invalid="true" aria-describedby="category_id-error" @enderror>
                    <option value="">Select a category</option>
                    @foreach($options->get('category', []) as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id', $product?->category_id) === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <p id="category_id-error" class="mt-1 text-sm text-rose-700" role="alert">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="subcategory_id" class="mb-1 block text-xs font-semibold text-slate-700">Subcategory</label>
                <select id="subcategory_id" name="subcategory_id" class="{{ $inputStyle }}"
                    @error('subcategory_id') aria-invalid="true" aria-describedby="subcategory_id-error" @enderror>
                    <option value="">Select a subcategory</option>
                    @foreach($options->get('subcategory', []) as $subcategory)
                        <option value="{{ $subcategory->id }}" data-parent-category="{{ $subcategory->parent_id }}" @selected((string) old('subcategory_id', $product?->subcategory_id) === (string) $subcategory->id)>{{ $subcategory->name }}</option>
                    @endforeach
                </select>
                @error('subcategory_id')
                    <p id="subcategory_id-error" class="mt-1 text-sm text-rose-700" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-indigo-100 pt-3">
            <a class="rounded-md px-3 py-2 text-xs font-semibold text-slate-700! hover:bg-slate-100" href="{{ $fromStock ? route('inventory.stock.index', ['type' => 'products']) : route('inventory.index') }}">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600! px-4 py-2 text-xs font-semibold text-white! hover:bg-indigo-700!">{{ $product ? 'Save Changes' : 'Add Product' }}</button>
        </div>
    </form>
</section>
@endsection
