<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_code', 100)->nullable()->unique();
            $table->unsignedInteger('stock_quantity')->nullable();
            $table->string('photo_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['product_code']);
            $table->dropColumn(['product_code', 'stock_quantity', 'photo_path']);
        });
    }
};
