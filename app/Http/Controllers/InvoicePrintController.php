<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class InvoicePrintController extends Controller
{
    public function __invoke(string $source, string $id): JsonResponse
    {
        $legacy = $source === 'legacy';
        $invoice = DB::table($legacy ? 'sales' : 'invoices')->where($legacy ? 'sale_id' : 'id', $id)->firstOrFail();
        $customer = DB::table('customers')->where('id', $invoice->customer_id)->first();

        $items = $legacy ? collect([$invoice]) : DB::table('invoice_items')
            ->leftJoin('products', 'invoice_items.product_id', '=', 'products.product_id')
            ->where('invoice_items.invoice_id', $id)
            ->orderBy('invoice_items.id')
            ->get(['invoice_items.*', 'products.product_name', 'products.unit']);

        $lines = $items->map(function ($item) use ($legacy) {
            $gross = round((float) $item->qty * (float) $item->unit_price, 2);
            $discount = round((float) ($item->discount_tk ?? 0) + $gross * (float) ($item->discount_per ?? 0) / 100, 2);
            $total = $legacy ? ($item->sale_amount ?? $gross - $discount) : ($item->total_price ?? $gross - $discount);

            return [
                'product' => $item->product_name ?? '—',
                'quantity' => (float) $item->qty,
                'unit' => $item->unit ?? '—',
                'price' => (float) $item->unit_price,
                'gross' => $gross,
                'discount' => $legacy ? $discount : round($gross - (float) $total, 2),
                'total' => (float) $total,
            ];
        });

        $total = (float) ($legacy ? $invoice->sale_amount : $invoice->total_amount);
        $paid = (float) $invoice->paid_amount;

        return response()->json([
            'number' => $invoice->invoice_no ?: (string) $id,
            'date' => $invoice->sale_date,
            'customer' => $customer?->name,
            'address' => $customer?->address,
            'phone' => $customer?->phone,
            'lines' => $lines,
            'subtotal' => $lines->sum('gross'),
            'discount' => $lines->sum('discount') + (float) ($legacy ? 0 : $invoice->discount),
            'total' => $total,
            'paid' => $paid,
            'due' => $legacy && isset($invoice->due_amount) ? (float) $invoice->due_amount : max(0, round($total - $paid, 2)),
        ]);
    }
}
