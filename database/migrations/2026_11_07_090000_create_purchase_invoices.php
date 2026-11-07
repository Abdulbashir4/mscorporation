<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('submission_key')->unique();
            $table->unsignedBigInteger('supplier_id');
            $table->string('supplier_name', 300);
            $table->date('purchase_date');
            $table->string('supplier_reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->decimal('total', 16, 2);
            $table->timestamps();
        });

        Schema::create('purchase_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('product_id')->index();
            $table->string('product_name', 300);
            $table->string('unit', 40);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 16, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice_items');
        Schema::dropIfExists('purchase_invoices');
    }
};
