<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseInvoiceController extends Controller
{
    public function create(): View
    {
        return view('purchase-invoice-form', [
            'suppliers' => Schema::hasTable('suppliers') ? DB::table('suppliers')->orderBy('name')->get(['id', 'name']) : collect(),
            'products' => DB::table('products')->whereNull('deleted_at')->orderBy('product_name')->get(['product_id', 'product_code', 'product_name', 'unit']),
            'submissionKey' => (string) Str::uuid(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('suppliers')) {
            throw ValidationException::withMessages(['supplier_id' => 'Add a supplier before recording a purchase.']);
        }

        $data = $request->validate([
            'submission_key' => ['required', 'uuid'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.now('Asia/Dhaka')->toDateString()],
            'supplier_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'distinct', Rule::exists('products', 'product_id')->whereNull('deleted_at')],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:999999.99', 'decimal:0,2'],
        ]);

        $id = DB::transaction(function () use ($data) {
            $supplier = DB::table('suppliers')->where('id', $data['supplier_id'])->lockForUpdate()->first();
            if (! $supplier) {
                throw ValidationException::withMessages(['supplier_id' => 'This supplier is no longer available.']);
            }

            $existing = DB::table('purchase_invoices')->where('submission_key', $data['submission_key'])->value('id');
            if ($existing) {
                return $existing;
            }

            $products = DB::table('products')->whereNull('deleted_at')
                ->whereIn('product_id', array_column($data['items'], 'product_id'))
                ->orderBy('product_id')->lockForUpdate()->get()->keyBy('product_id');

            $total = 0;
            $items = [];
            foreach ($data['items'] as $index => $item) {
                $product = $products->get($item['product_id']);
                if (! $product) {
                    throw ValidationException::withMessages(["items.{$index}.product_id" => 'This product is no longer available.']);
                }
                if ($product->stock_quantity !== null && $product->stock_quantity + $item['quantity'] > 2147483647) {
                    throw ValidationException::withMessages(["items.{$index}.quantity" => 'This purchase exceeds the stock quantity limit.']);
                }

                $price = (int) round((float) $item['unit_price'] * 100);
                $lineTotal = $price * (int) $item['quantity'];
                $total += $lineTotal;
                $items[] = [
                    'product_id' => $product->product_id,
                    'product_name' => $product->product_name,
                    'unit' => $product->unit,
                    'quantity' => $item['quantity'],
                    'unit_price' => $this->decimal($price),
                    'line_total' => $this->decimal($lineTotal),
                ];
            }

            $id = DB::table('purchase_invoices')->insertGetId([
                'submission_key' => $data['submission_key'],
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'purchase_date' => $data['purchase_date'],
                'supplier_reference' => $data['supplier_reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'total' => $this->decimal($total),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('purchase_invoice_items')->insert(array_map(fn (array $item) => ['purchase_invoice_id' => $id] + $item, $items));
            foreach ($items as $item) {
                DB::table('products')->where('product_id', $item['product_id'])->whereNotNull('stock_quantity')
                    ->increment('stock_quantity', (int) $item['quantity']);
            }

            return $id;
        }, 3);

        return redirect()->route('purchase.invoices.show', $id);
    }

    public function show(string $invoice): View
    {
        return view('purchase-invoice', [
            'invoice' => DB::table('purchase_invoices')->where('id', $invoice)->firstOrFail(),
            'items' => DB::table('purchase_invoice_items')->where('purchase_invoice_id', $invoice)->orderBy('id')->get(),
        ]);
    }

    private function decimal(int $minor): string
    {
        return intdiv($minor, 100).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }
}
