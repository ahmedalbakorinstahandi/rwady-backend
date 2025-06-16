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
        Schema::disableForeignKeyConstraints();

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku');
            $table->text('name');
            $table->longText('description')->nullable();
            $table->boolean('is_recommended')->default(false);
            $table->string('ribbon_text', 40)->nullable();
            $table->string('ribbon_color', 10)->nullable();
            $table->float('price')->default(0);
            $table->float('price_after_discount')->nullable();
            $table->dateTime('price_discount_start')->nullable();
            $table->dateTime('price_discount_end')->nullable();
            $table->float('cost_price')->default(0);
            $table->float('cost_price_after_discount')->nullable();
            $table->dateTime('cost_price_discount_start')->nullable();
            $table->dateTime('cost_price_discount_end')->nullable();
            $table->boolean('availability‍')->default(true);
            $table->bigInteger('stock')->default(0);
            $table->boolean('stock_unlimited')->default(false);
            $table->enum('out_of_stock', ["show_on_storefront","hide_from_storefront","show_and_allow_pre_order"])->default("show_on_storefront");
            $table->integer('minimum_purchase')->nullable();
            $table->integer('maximum_purchase')->nullable();
            $table->float('weight')->nullable();
            $table->float('length')->nullable();
            $table->float('width')->nullable();
            $table->float('height')->nullable();
            $table->enum('shipping_type', ["default","fixed_shipping","free_shipping"]);
            $table->float('shipping_rate_single')->nullable();
            $table->float('shipping_rate_multi')->nullable();
            $table->unsignedBigInteger('related_category_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
