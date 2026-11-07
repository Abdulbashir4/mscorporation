<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('name', 150);
            $table->foreignId('parent_id')->nullable()->constrained('stock_attributes')->restrictOnDelete();
            $table->unique(['type', 'name']);
            $table->timestamps();
        });

        $categories = ['Monitoring', 'Life Support', 'Imaging', 'Diagnostic', 'Emergency', 'OT Equipment', 'Furniture'];
        foreach ($categories as $name) {
            DB::table('stock_attributes')->insert(['type' => 'category', 'name' => $name]);
        }

        if (Schema::hasTable('products')) {
            foreach (['brand', 'model'] as $type) {
                $names = DB::table('products')->distinct()->pluck($type)
                    ->map(fn ($name) => trim($name))->reject(fn ($name) => $name === '' || $name === '—');
                foreach ($names as $name) {
                    DB::table('stock_attributes')->insertOrIgnore(['type' => $type, 'name' => $name]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_attributes');
    }
};
