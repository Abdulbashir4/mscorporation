<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['customers', 'suppliers'] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'email')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('email', 254)->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['customers', 'suppliers'] as $tableName) {
            if (Schema::hasColumn($tableName, 'email')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('email');
                });
            }
        }
    }
};
