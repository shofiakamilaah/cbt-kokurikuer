<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canteen_sales', function (Blueprint $table) {
            $table->unsignedInteger('initial_stock')->default(0)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('canteen_sales', function (Blueprint $table) {
            $table->dropColumn('initial_stock');
        });
    }
};
