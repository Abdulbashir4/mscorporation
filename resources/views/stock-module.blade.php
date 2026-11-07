@extends('layouts.app')
@section('title', 'Stock Module')
@section('content')
<section class="space-y-5 text-slate-900">
    <header class="flex flex-wrap items-center justify-between gap-5 rounded-2xl bg-gradient-to-br from-slate-950 to-indigo-900 p-6 text-white! shadow-sm sm:p-8">
        <div>
            <p class="mb-2 flex items-center gap-2 text-xs font-semibold tracking-widest text-indigo-200 uppercase">
                <x-icon name="warehouse" class="size-4"/> Inventory settings
            </p>
            <h1 class="text-3xl font-bold">Stock Module</h1>
            <p class="mt-2 text-sm text-slate-200">Manage the products and classifications used in your inventory.</p>
        </div>
        <a class="rounded-lg bg-white/10 px-4 py-2 text-sm font-semibold text-white! hover:bg-white/20" href="{{ route('inventory.index') }}">Back to Inventory</a>
    </header>

    <nav class="grid grid-cols-2 gap-2 rounded-xl border border-slate-200 bg-white p-2 shadow-sm sm:grid-cols-5" aria-label="Stock Module">
        @foreach(['products' => 'Products'] + $types as $key => $label)
            <a @class(['rounded-lg px-3 py-2.5 text-center text-sm font-semibold', 'bg-indigo-600 text-white!' => $key === $type, 'text-slate-600! hover:bg-slate-100' => $key !== $type])
                href="{{ route('inventory.stock.index', ['type' => $key]) }}"
                @if($key === $type) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    @if($type === 'products')
        @include('stock-products')
    @else
    @if(session('stock_status'))
        <p class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('stock_status') }}</p>
    @endif
    @error('delete')
        <p class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">{{ $message }}</p>
    @enderror

    @php($addUrl = route('inventory.stock.index', ['type' => $type, 'add' => 1]).'#stock-editor')
    <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 p-5">
            <h2 class="text-lg font-bold">{{ $types[$type] }} List</h2>
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold whitespace-nowrap text-indigo-700">{{ $attributes->count() }} {{ Str::plural('record', $attributes->count()) }}</span>
                <a class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-1.5 text-emerald-700! hover:bg-emerald-100"
                    href="{{ $addUrl }}" aria-label="Add {{ $types[$type] }}" title="Add {{ $types[$type] }}">
                    <x-icon name="plus" class="size-4"/>
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold tracking-wide text-slate-600 uppercase">
                    <tr>
                        <th scope="col" class="w-14 px-4 py-3">#</th>
                        <th scope="col" class="px-4 py-3">Name</th>
                        <th scope="col" class="px-4 py-3">Parent Category</th>
                        <th scope="col" class="px-4 py-3">Child Categories</th>
                        <th scope="col" class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @if($editing || request()->boolean('add') || $errors->hasAny(['name', 'parent_id']))
                        <tr id="stock-editor" class="bg-indigo-50/50">
                            <td colspan="5" class="px-4 py-5">
                                <section aria-labelledby="stock-editor-title" class="mx-auto max-w-lg rounded-xl border border-indigo-100 bg-white shadow-sm">
                                    <header class="rounded-t-xl border-b border-indigo-100 bg-indigo-50 px-5 py-4">
                                        <h2 id="stock-editor-title" class="flex items-center gap-2 text-lg font-bold text-indigo-950">
                                            <x-icon :name="$editing ? 'edit' : 'plus'" class="size-5 text-indigo-600"/>
                                            {{ $editing ? 'Edit' : 'Add' }} {{ $types[$type] }}
                                        </h2>
                                        <p class="mt-1 text-sm text-slate-500">{{ $editing ? 'Update the details below.' : 'Create a record for your inventory.' }}</p>
                                    </header>
                                    <form class="space-y-4 p-5" method="post"
                                        action="{{ $editing ? route('inventory.stock.update', $editing) : route('inventory.stock.store') }}">
                                        @csrf
                                        @if($editing)
                                            @method('PATCH')
                                        @else
                                            <input type="hidden" name="type" value="{{ $type }}">
                                        @endif
                                        <div>
                                            <label for="stock-name" class="mb-1.5 block text-sm font-semibold">Name</label>
                                            <input id="stock-name" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-indigo-500"
                                                name="name" required maxlength="150" placeholder="Enter {{ strtolower($types[$type]) }} name"
                                                value="{{ old('name', $editing?->name) }}"
                                                autofocus
                                                @error('name') aria-invalid="true" aria-describedby="stock-name-error" @enderror>
                                            @error('name')
                                                <p id="stock-name-error" class="mt-1 text-sm text-rose-700" role="alert">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        @if($type === 'subcategory')
                                            <div>
                                                <label for="stock-category" class="mb-1.5 block text-sm font-semibold">Parent Category</label>
                                                <select id="stock-category" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-indigo-500"
                                                    name="parent_id" required @disabled($categories->isEmpty())
                                                    @error('parent_id') aria-invalid="true" aria-describedby="stock-category-error" @enderror>
                                                    <option value="">Select a category</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}" @selected((string) old('parent_id', $editing?->parent_id) === (string) $category->id)>{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('parent_id')
                                                    <p id="stock-category-error" class="mt-1 text-sm text-rose-700" role="alert">{{ $message }}</p>
                                                @enderror
                                                @if($categories->isEmpty())
                                                    <a class="mt-2 inline-block text-sm font-medium text-indigo-700! underline!" href="{{ route('inventory.stock.index', ['type' => 'category']) }}">Add a category first</a>
                                                @endif
                                            </div>
                                        @endif
                                        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-4">
                                            <a class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600! hover:bg-slate-100" href="{{ route('inventory.stock.index', ['type' => $type]) }}">Cancel</a>
                                            <button class="cursor-pointer rounded-lg bg-indigo-600! px-4 py-2.5 text-sm font-semibold text-white! hover:bg-indigo-700! disabled:cursor-not-allowed disabled:opacity-50"
                                                type="submit" @disabled($type === 'subcategory' && $categories->isEmpty())>
                                                {{ $editing ? 'Save Changes' : 'Add '.$types[$type] }}
                                            </button>
                                        </div>
                                    </form>
                                </section>
                            </td>
                        </tr>
                    @endif
                    @forelse($attributes as $attribute)
                        <tr @class(['hover:bg-slate-50', 'bg-indigo-50' => $editing?->is($attribute)])>
                            <td class="px-4 py-3 text-slate-500 tabular-nums">{{ $loop->iteration }}</td>
                            <th scope="row" class="px-4 py-3 font-semibold break-words [overflow-wrap:anywhere]">{{ $attribute->name }}</th>
                            <td class="px-4 py-3 text-slate-600 [overflow-wrap:anywhere]">{{ $attribute->parent?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600 [overflow-wrap:anywhere]">
                                @if($attribute->children->isEmpty())
                                    —
                                @else
                                    <ul class="flex flex-wrap gap-1.5">
                                        @foreach($attribute->children as $child)
                                            <li class="rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700">{{ $child->name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2.5 py-1.5 font-medium text-indigo-700! hover:bg-indigo-100"
                                        href="{{ route('inventory.stock.edit', $attribute) }}" aria-label="Edit {{ $attribute->name }}">
                                        <x-icon name="edit" class="size-4"/> Edit
                                    </a>
                                    <form method="post" action="{{ route('inventory.stock.destroy', $attribute) }}" data-stock-delete="{{ $attribute->name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="inline-flex cursor-pointer items-center gap-1 rounded-md bg-rose-50! px-2.5 py-1.5 font-medium text-rose-700 hover:bg-rose-100!" type="submit" aria-label="Delete {{ $attribute->name }}">
                                            <x-icon name="trash" class="size-4"/> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <x-icon name="box" class="mx-auto mb-3 size-8 text-indigo-400"/>
                                <p class="font-semibold">No {{ Str::plural(strtolower($types[$type])) }} yet</p>
                                <a class="mt-3 inline-flex items-center gap-1 rounded-md bg-emerald-50 px-3 py-2 font-semibold text-emerald-700! hover:bg-emerald-100"
                                    href="{{ $addUrl }}">
                                    <x-icon name="plus" class="size-4"/> Add {{ $types[$type] }}
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>
    @endif
</section>
@endsection
