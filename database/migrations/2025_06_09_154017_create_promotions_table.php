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

        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->longText('title');
            $table->enum('type', ['product', 'category', 'cart_total', 'shipping']);
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('min_cart_total', 10, 2)->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->enum(column: 'status', allowed: ['draft', 'active', 'inactive']);
            $table->timestamps();
            $table->softDeletes();
        });

        // promotion_products
        Schema::create('promotion_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions');
            $table->foreignId('product_id')->constrained('products');
            $table->timestamps();
        });

        //promotion_categories
        Schema::create('promotion_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions');
            $table->foreignId('category_id')->constrained('categories');
            $table->timestamps();
        });


        // orders
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('promotion_cart_id')->nullable()->constrained('promotions');
            $table->longText('promotion_cart_title')->nullable();
            $table->double('promotion_cart_discount_value')->nullable();
            $table->double('promotion_cart_discount_type')->nullable();
            $table->boolean('promotion_free_shipping')->default(false);
            $table->foreignId('promotion_shipping_id')->nullable()->constrained('promotions');
            $table->longText('promotion_shipping_title')->nullable();
        });


        // alert order_products
        Schema::table('order_products', function (Blueprint $table) {
            $table->foreignId('promotion_id')->nullable()->constrained('promotions');
            $table->longText('promotion_title')->nullable();
            $table->double('promotion_discount_type')->nullable();
            $table->double('promotion_discount_value')->nullable();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
