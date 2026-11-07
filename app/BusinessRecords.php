<?php

namespace App;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BusinessRecords
{
    public function forView(string $view): array
    {
        return match ($view) {
            'inventory' => $this->inventory(),
            'contacts' => $this->contacts(),
            'purchase' => $this->purchases(),
            'sales' => $this->sales(),
            'accounting' => $this->accounts(),
            'reports' => $this->inventory() + $this->contacts() + $this->purchases() + $this->sales() + $this->accounts(),
            'welcome' => $this->dashboard(),
        };
    }

    private function rows(string $table, string $order, bool $latest = false): Collection
    {
        return Schema::hasTable($table)
            ? DB::table($table)->orderBy($order, $latest ? 'desc' : 'asc')->get()
            : collect();
    }

    private function inventory(): array
    {
        $products = $this->rows('products', 'product_id');
        $categories = $this->rows('stock_attributes', 'name')->where('type', 'category')->pluck('name', 'id');
        $stock = $products->isEmpty() ? collect() : $this->stockQuantities($products);

        return [
            'inventory.products' => $products->whereNull('deleted_at')->values()->map(function ($row) use ($categories, $stock) {
                $quantity = isset($row->stock_quantity) ? (int) $row->stock_quantity : $stock->get($row->product_id);

                return [
                    $row->product_name, '', (string) $row->product_id, $row->brand, $row->model,
                    $categories->get($row->category_id ?? '', '—'), $quantity ?? '—',
                    (float) str_replace(',', '', $row->price_a), match (true) {
                        $quantity === null => '—',
                        $quantity < 0 => 'Stock mismatch',
                        $quantity === 0 => 'Out of Stock',
                        default => 'In Stock',
                    }, '', $row->product_code ?? (string) $row->product_id, $row->photo_path ?? null,
                ];
            })->all(),
            'inventory.alerts' => [],
        ];
    }

    private function stockQuantities(Collection $products): Collection
    {
        $productIds = $products->keyBy('product_id');
        $normalize = fn (?string $name): string => mb_strtolower(trim($name ?? ''));
        $uniqueNames = $products->groupBy(fn ($row) => $normalize($row->product_name))
            ->filter(fn ($matches, $name) => $name !== '' && $matches->count() === 1)
            ->map(fn ($matches) => $matches->first()->product_id);

        $purchases = $this->rows('bye_info', 'bye_id')->map(fn ($row) => [
            'product' => $productIds->has(trim($row->product_name)) ? trim($row->product_name) : $uniqueNames->get($normalize($row->product_name)),
            'quantity' => $row->qty,
        ])->concat($this->rows('store', 'id')->map(fn ($row) => [
            'product' => $uniqueNames->get($normalize($row->product_name)), 'quantity' => $row->quantity,
        ]))->concat($this->rows('purchase_invoice_items', 'id')->map(fn ($row) => [
            'product' => $row->product_id, 'quantity' => $row->quantity,
        ]))->filter(fn ($row) => $row['product'] !== null && $row['quantity'] !== null)->groupBy('product');

        $invoiceNumbers = $this->rows('invoices', 'id')->pluck('invoice_no')->filter()->flip();
        $legacySales = $this->rows('sales', 'sale_id')->reject(fn ($row) => $invoiceNumbers->has($row->invoice_no))
            ->groupBy(fn ($row) => $uniqueNames->get($normalize($row->product_name)));
        $invoiceItems = $this->rows('invoice_items', 'id')->groupBy('product_id');

        return $purchases->map(fn ($lines, $id) => (int) ($lines->sum('quantity')
            - $invoiceItems->get($id, collect())->sum('qty') - $legacySales->get($id, collect())->sum('qty')));
    }

    private function contacts(): array
    {
        $data = ['contacts.activities' => []];
        $payments = $this->rows('payment_account', 'id')->groupBy(['party_type', 'party_id']);

        foreach (['customers' => 'Customer', 'suppliers' => 'Supplier'] as $table => $type) {
            $data['contacts.'.$table] = $this->rows($table, 'id')->map(function ($row) use ($payments, $type) {
                $entries = $payments->get($type, collect())->get($row->id, collect());

                return [
                    'id' => $type[0].'-'.$row->id,
                    'name' => $row->name,
                    'initials' => mb_substr($row->name, 0, 2),
                    'type' => $row->class ?? $type,
                    'phone' => $row->phone ?? '',
                    'email' => $row->email ?? '',
                    'address' => $row->address ?? '',
                    'contact' => $row->contact_person ?? '',
                    'due' => $entries->sum('due_amunt'),
                    'total' => $entries->sum('total_amunt'),
                    'orders' => $entries->count(),
                    'color' => 'blue',
                ];
            })->all();
        }

        return $data;
    }

    private function purchases(): array
    {
        $purchases = $this->rows('store', 'purchase_datetime', true);
        $orderLines = $this->rows('bye_info', 'sale_date', true)->groupBy('invoice_no');
        $suppliers = $this->contacts()['contacts.suppliers'];

        return [
            'purchase.invoices' => Schema::hasTable('purchase_invoices') ? DB::table('purchase_invoices')->latest('id')->limit(20)->get() : collect(),
            'purchase.orders' => $orderLines->map(function ($lines, $invoice) {
                $first = $lines->first();
                $total = $lines->sum(function ($row) {
                    $subtotal = $row->qty * $row->unit_price;

                    return $subtotal - $row->discount_tk - ($subtotal * $row->discount_per / 100);
                });

                return [(string) $invoice, $first->shop_name, $this->date($first->sale_date, 'd-m-Y'), '—', $this->money($total)];
            })->values()->all(),
            'purchase.receipts' => $purchases->map(fn ($row) => [
                (string) $row->id, '—', $row->shop_name ?? '—', $this->date($row->purchase_datetime, 'd-m-Y'),
                $row->quantity, 'Received',
            ])->all(),
            'purchase.suppliers' => array_map(fn ($row) => [
                $row['name'], '—', $row['contact'], $row['phone'], $this->money($row['total']), '—',
            ], $suppliers),
            'purchase.payments' => [],
            'purchase.products' => $purchases->map(fn ($row) => [
                $row->product_name ?? '—', $row->quantity, $this->money($row->price),
            ])->all(),
        ];
    }

    private function sales(): array
    {
        $customerRecords = $this->rows('customers', 'id');
        $customers = $customerRecords->pluck('name', 'id');
        $invoices = $this->rows('invoices', 'sale_date', true);
        $invoiceNumbers = $invoices->pluck('invoice_no')->filter()->flip();
        $legacyInvoices = $this->rows('sales', 'sale_date', true)
            ->reject(fn ($row) => $invoiceNumbers->has($row->invoice_no))
            ->map(fn ($row) => (object) [
                'id' => $row->sale_id,
                'invoice_no' => $row->invoice_no,
                'sale_date' => $row->sale_date,
                'customer_id' => $row->customer_id,
                'total_amount' => $row->sale_amount,
                'paid_amount' => $row->paid_amount,
                'source' => 'legacy',
            ]);
        $topProducts = $this->rows('invoice_items', 'id')->groupBy('product_id')
            ->sortByDesc(fn ($items) => $items->sum('qty'))->take(5);
        $products = $this->rows('products', 'product_id');
        $productNames = $products->pluck('product_name', 'product_id');
        $classifications = $this->rows('stock_attributes', 'name')->keyBy('id');
        $sales = $invoices->concat($legacyInvoices)->sortByDesc('sale_date')->values();

        return [
            'sales.total' => $this->money($sales->sum('total_amount')),
            'sales.receivable' => $this->money($sales->sum(fn ($row) => max(0, $row->total_amount - $row->paid_amount))),
            'sales.invoices' => $sales->map(fn ($row) => [
                $row->invoice_no ?: (string) $row->id, $this->date($row->sale_date, 'd M Y'),
                $customers->get($row->customer_id, '—'), $this->money($row->total_amount),
                $row->paid_amount >= $row->total_amount ? 'Paid' : ($row->paid_amount > 0 ? 'Partially Paid' : 'Pending'),
                $row->source ?? 'invoice', $row->id,
            ])->all(),
            'sales.orders' => [],
            'sales.quotations' => [],
            'sales.customers' => $customers->values()->all(),
            'sales.customer_details' => $customerRecords->groupBy('name')->filter(fn ($matches) => $matches->count() === 1)
                ->map(fn ($matches) => $matches->first())->map(fn ($customer) => ['address' => $customer->address, 'phone' => $customer->phone])->all(),
            'sales.products' => $products->whereNull('deleted_at')->values()->map(function ($row) use ($classifications) {
                $category = $classifications->get($row->category_id ?? '');
                $subcategory = $classifications->get($row->subcategory_id ?? '');

                return [
                    $row->product_name, (float) str_replace(',', '', $row->price_a), $row->unit ?? 'pcs',
                    $row->product_id, $row->product_code ?: (string) $row->product_id,
                    $category?->name, $subcategory && $category && $subcategory->parent_id === $category->id ? $subcategory->name : null,
                    $row->brand, $row->model,
                ];
            })->all(),
            'sales.top_products' => $topProducts->map(fn ($items, $id) => [
                $productNames[$id] ?? '—', $items->sum('qty'), $this->money($items->sum('total_price')),
            ])->values()->map(fn ($row, $index) => [$index + 1, ...$row])->all(),
        ];
    }

    private function accounts(): array
    {
        $entries = $this->rows('payment_account', 'date', true);
        $customers = $this->rows('customers', 'id')->pluck('name', 'id');
        $suppliers = $this->rows('suppliers', 'id')->pluck('name', 'id');
        $ledger = $this->rows('statements', 'created_at', true)->map(function ($row) use ($customers, $suppliers) {
            $parties = match (strtolower($row->party_type)) {
                'customer' => $customers,
                'supplier' => $suppliers,
                default => collect(),
            };

            return [
                $this->date($row->created_at, 'd M Y'), $row->invoice_no ?: (string) $row->id,
                $row->pay_type ?: ($row->trx_type ?: 'Ledger'),
                $parties->get($row->party_id, '—'), $row->description ?? '',
                $this->money((float) $row->debit - (float) $row->credit), 'Posted',
            ];
        });

        return [
            'accounting.vouchers' => $ledger->all(),
            'accounting.journals' => [],
            'accounting.transactions' => $entries->map(fn ($row) => [
                $row->payment_type, $row->cus_or_supp_name, $this->money($row->paid_amunt),
                $row->date, 'clipboard', 'blue',
            ])->all(),
            'accounting.accounts' => [],
            'accounting.bank' => [],
            'accounting.reconcile' => [],
        ];
    }

    private function dashboard(): array
    {
        $purchaseData = $this->purchases();
        $salesData = $this->sales();
        $purchases = array_slice($purchaseData['purchase.orders'], 0, 5);
        $invoices = array_slice($salesData['sales.invoices'], 0, 5);

        return [
            'dashboard.sales_total' => $salesData['sales.total'],
            'dashboard.receivable' => $salesData['sales.receivable'],
            'dashboard.purchase_count' => count($purchaseData['purchase.orders']),
            'dashboard.purchases' => array_map(fn ($row) => [$row[0], $row[1], $row[2], $row[3], substr($row[4], 4)], $purchases),
            'dashboard.invoices' => array_map(fn ($row) => [$row[0], $row[2], $row[1], substr($row[3], 4), $row[4]], $invoices),
            'dashboard.stock' => [],
            'dashboard.activities' => [],
        ];
    }

    private function date(?string $date, string $format): string
    {
        return $date && $date !== '0000-00-00' ? Carbon::parse($date)->format($format) : '—';
    }

    private function money(float|string|null $amount): string
    {
        return '৳ '.number_format((float) $amount, 2);
    }
}
