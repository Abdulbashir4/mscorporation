<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class PurchaseInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_saves_exact_totals_and_adds_stock_once(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        $supplier = DB::table('suppliers')->insertGetId(['name' => 'Test Supplier']);
        $product = DB::table('products')->insertGetId([
            'product_name' => 'Test Product',
            'product_image' => '',
            'brand' => '',
            'model' => '',
            'price_a' => '100',
            'price_b' => '',
            'price_c' => '',
            'price_d' => '',
            'stock_quantity' => 5,
        ]);
        $data = [
            'submission_key' => (string) Str::uuid(),
            'supplier_id' => $supplier,
            'purchase_date' => now('Asia/Dhaka')->toDateString(),
            'supplier_reference' => 'SUP-77',
            'items' => [['product_id' => $product, 'quantity' => 3, 'unit_price' => '12.35']],
        ];

        $this->get(route('purchase.invoices.create'))->assertOk()->assertSee('Test Supplier')->assertSee('Test Product');
        $this->post(route('purchase.invoices.store'), $data)->assertRedirect(route('purchase.invoices.show', 1));
        $this->post(route('purchase.invoices.store'), $data)->assertRedirect(route('purchase.invoices.show', 1));

        $this->assertDatabaseCount('purchase_invoices', 1);
        $this->assertDatabaseHas('purchase_invoice_items', ['purchase_invoice_id' => 1, 'product_id' => $product, 'line_total' => 37.05]);
        $this->assertDatabaseHas('products', ['product_id' => $product, 'stock_quantity' => 8]);
        $this->get(route('purchase.invoices.show', 1))->assertOk()->assertSee('SUP-77')->assertSee('37.05');
        $this->get(route('purchase.index'))->assertOk()->assertSee('PI-000001');
    }
}
