<?php

namespace App\Http\Controllers;

use App\BusinessRecords;
use App\Models\StockAttribute;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProductController extends Controller
{
    private const UNITS = ['pcs', 'box', 'set', 'pack', 'pair', 'bottle', 'roll', 'kg', 'g', 'L', 'mL', 'm'];

    public function create(): View
    {
        return $this->form();
    }

    public function edit(string $product): View
    {
        return $this->form($this->activeProduct($product)->firstOrFail());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->save($request, null, [
            'product_image' => '',
            'price_b' => '',
            'price_c' => '',
            'price_d' => '',
        ]);

        return $this->productListRedirect($request, 'Product added.');
    }

    public function update(Request $request, string $product): RedirectResponse
    {
        $this->save($request, $this->activeProduct($product)->firstOrFail());

        return $this->productListRedirect($request, 'Product updated.');
    }

    public function destroy(Request $request, string $product): RedirectResponse
    {
        $deleted = $this->activeProduct($product)->update(['deleted_at' => now()]);
        abort_unless($deleted, 404);

        return $this->productListRedirect($request, 'Product deleted.');
    }

    private function productListRedirect(Request $request, string $message): RedirectResponse
    {
        $redirect = $request->query('from') === 'stock'
            ? redirect()->route('inventory.stock.index', ['type' => 'products'])
            : redirect()->route('inventory.index');

        return $redirect->with('product_status', $message);
    }

    private function activeProduct(string $product): Builder
    {
        return DB::table('products')->where('product_id', $product)->whereNull('deleted_at');
    }

    public function image(string $product): StreamedResponse
    {
        $path = DB::table('products')->where('product_id', $product)->value('photo_path');
        $disk = Storage::disk('local');
        abort_unless($path && $disk->exists($path), 404);

        return $disk->response($path);
    }

    private function form(?object $product = null): View
    {
        $quantity = $product?->stock_quantity;
        if ($product && $quantity === null) {
            $row = collect(app(BusinessRecords::class)->forView('inventory')['inventory.products'])
                ->firstWhere(2, (string) $product->product_id);
            $quantity = isset($row[6]) && is_int($row[6]) && $row[6] >= 0 ? $row[6] : null;
        }

        return view('product-form', [
            'product' => $product,
            'quantity' => $quantity,
            'units' => self::UNITS,
            'options' => StockAttribute::whereIn('type', ['brand', 'model', 'category', 'subcategory'])->orderBy('name')->get()->groupBy('type'),
        ]);
    }

    private function save(Request $request, ?object $product, array $defaults = []): void
    {
        if (! $product) {
            do {
                $code = 'MS-'.strtoupper(bin2hex(random_bytes(5)));
            } while (DB::table('products')->where('product_code', $code)->exists());

            $request->merge(['product_code' => $code, 'stock_quantity' => 0, 'price_a' => 0]);
        }

        $uniqueCode = Rule::unique('products', 'product_code');
        if ($product) {
            $uniqueCode->ignore($product->product_id, 'product_id');
        }

        $data = $request->validate([
            'product_name' => ['required', 'string', 'max:300'],
            'product_code' => ['required', 'string', 'max:100', $uniqueCode],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'unit' => ['sometimes', 'required', Rule::in(self::UNITS)],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'price_a' => ['required', 'numeric', 'min:0', 'max:999999999999.99', 'decimal:0,2'],
            'category_id' => ['nullable', 'integer', Rule::exists('stock_attributes', 'id')->where('type', 'category')],
            'subcategory_id' => ['nullable', 'integer', Rule::exists('stock_attributes', 'id')->where('type', 'subcategory')->where('parent_id', $request->integer('category_id'))],
            'image' => ['nullable', 'image', 'max:5120'],
        ]) + ['category_id' => null, 'subcategory_id' => null];
        $data['brand'] = $data['brand'] ?? '';
        $data['model'] = $data['model'] ?? '';
        unset($data['image']);

        $disk = Storage::disk('local');
        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('product-photos', 'local');
            if ($path === false) {
                throw ValidationException::withMessages(['image' => 'The image could not be saved.']);
            }
            $data['photo_path'] = $path;
        }

        try {
            if ($product) {
                $this->activeProduct((string) $product->product_id)->update($data);
            } else {
                DB::table('products')->insert($data + $defaults);
            }
        } catch (Throwable $exception) {
            if ($path) {
                $disk->delete($path);
            }
            throw $exception;
        }

        if ($path && $product?->photo_path) {
            $disk->delete($product->photo_path);
        }
    }
}
