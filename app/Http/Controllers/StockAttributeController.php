<?php

namespace App\Http\Controllers;

use App\Models\StockAttribute;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockAttributeController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type', 'brand');
        if ($type === 'products') {
            return view('stock-module', [
                'types' => StockAttribute::TYPES,
                'type' => $type,
                'products' => DB::table('products')
                    ->leftJoin('stock_attributes as category', 'products.category_id', '=', 'category.id')
                    ->leftJoin('stock_attributes as subcategory', 'products.subcategory_id', '=', 'subcategory.id')
                    ->whereNull('products.deleted_at')->orderBy('products.product_name')
                    ->get(['products.product_id', 'products.product_code', 'products.product_name', 'products.brand', 'products.model', 'products.unit', 'category.name as category', 'subcategory.name as subcategory']),
            ]);
        }
        abort_unless(is_string($type) && isset(StockAttribute::TYPES[$type]), 404);

        return $this->page($type);
    }

    public function edit(StockAttribute $attribute): View
    {
        return $this->page($attribute->type, $attribute);
    }

    public function store(Request $request): RedirectResponse
    {
        $type = $request->validate(['type' => ['required', Rule::in(array_keys(StockAttribute::TYPES))]])['type'];
        StockAttribute::create(['type' => $type] + $this->validated($request, $type));

        return $this->backToType($type, 'Item added.');
    }

    public function update(Request $request, StockAttribute $attribute): RedirectResponse
    {
        $attribute->update($this->validated($request, $attribute->type, $attribute));

        return $this->backToType($attribute->type, 'Item updated.');
    }

    public function destroy(StockAttribute $attribute): RedirectResponse
    {
        try {
            $attribute->delete();
        } catch (QueryException $exception) {
            if (! StockAttribute::where('parent_id', $attribute->id)->exists()) {
                throw $exception;
            }

            return redirect()->route('inventory.stock.index', ['type' => $attribute->type])
                ->withErrors(['delete' => 'Move or delete this category’s subcategories first.']);
        }

        return $this->backToType($attribute->type, 'Item deleted.');
    }

    private function page(string $type, ?StockAttribute $editing = null): View
    {
        return view('stock-module', [
            'types' => StockAttribute::TYPES,
            'type' => $type,
            'editing' => $editing,
            'attributes' => StockAttribute::where('type', $type)->with(['parent', 'children'])->orderBy('name')->get(),
            'categories' => $type === 'subcategory' ? StockAttribute::where('type', 'category')->orderBy('name')->get() : collect(),
        ]);
    }

    private function validated(Request $request, string $type, ?StockAttribute $attribute = null): array
    {
        $unique = Rule::unique('stock_attributes')->where('type', $type);
        if ($attribute) {
            $unique->ignore($attribute);
        }

        $rules = ['name' => ['required', 'string', 'max:150', $unique]];
        if ($type === 'subcategory') {
            $rules['parent_id'] = ['required', 'integer', Rule::exists('stock_attributes', 'id')->where('type', 'category')];
        }

        return $request->validate($rules);
    }

    private function backToType(string $type, string $message): RedirectResponse
    {
        return redirect()->route('inventory.stock.index', ['type' => $type])->with('stock_status', $message);
    }
}
