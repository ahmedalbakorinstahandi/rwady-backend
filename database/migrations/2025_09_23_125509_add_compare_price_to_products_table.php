<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // add compare_price, compare_price_start, compare_price_end
            $table->float('compare_price')->nullable();
            $table->dateTime('compare_price_start')->nullable();
            $table->dateTime('compare_price_end')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // drop compare_price, compare_price_start, compare_price_end
            $table->dropColumn('compare_price');
            $table->dropColumn('compare_price_start');
            $table->dropColumn('compare_price_end');
        });
    }
};
