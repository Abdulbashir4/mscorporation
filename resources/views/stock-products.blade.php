@if(session('product_status'))
    <p class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('product_status') }}</p>
@endif

<article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-5">
        <div>
            <h2 class="text-lg font-bold">Product List</h2>
            <p class="mt-1 text-sm text-slate-500">Active products appear in purchase and sales invoices.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $products->count() }} {{ Str::plural('record', $products->count()) }}</span>
            <a class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700! hover:bg-emerald-100" href="{{ route('inventory.products.create', ['from' => 'stock']) }}">
                <x-icon name="plus" class="size-4"/> Add Product
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs tracking-wide text-slate-600 uppercase">
                <tr>
                    @foreach(['Product Name', 'Code', 'Category', 'Subcategory', 'Brand', 'Model', 'Unit'] as $heading)
                        <th scope="col" class="px-4 py-3">{{ $heading }}</th>
                    @endforeach
                    <th scope="col" class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($products as $product)
                    <tr class="hover:bg-slate-50">
                        <th scope="row" class="px-4 py-3 font-semibold whitespace-normal">{{ $product->product_name }}</th>
                        <td class="px-4 py-3">{{ $product->product_code ?: $product->product_id }}</td>
                        <td class="px-4 py-3">{{ $product->category ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $product->subcategory ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $product->brand ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $product->model ?: '—' }}</td>
                        <td class="px-4 py-3">{{ $product->unit }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a class="rounded-md bg-indigo-50 px-2.5 py-1.5 font-medium text-indigo-700! hover:bg-indigo-100" href="{{ route('inventory.products.edit', ['product' => $product->product_id, 'from' => 'stock']) }}" aria-label="Edit {{ $product->product_name }}">Edit</a>
                                <form method="post" action="{{ route('inventory.products.destroy', ['product' => $product->product_id, 'from' => 'stock']) }}" data-stock-delete="{{ $product->product_name }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md bg-rose-50! px-2.5 py-1.5 font-medium text-rose-700 hover:bg-rose-100!" aria-label="Delete {{ $product->product_name }}">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-slate-500">No products yet. Use Add Product to list your first item.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</article>
