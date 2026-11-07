<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->increments('product_id');
                $table->string('product_name', 300);
                $table->string('product_image', 30);
                $table->string('model', 100);
                $table->string('brand', 100);
                foreach (['price_a', 'price_b', 'price_c', 'price_d'] as $price) {
                    $table->string($price);
                }
            });
        }

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('stock_attributes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
