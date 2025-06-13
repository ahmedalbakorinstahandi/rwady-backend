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

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('path', 500);
            $table->string('type');
            $table->enum('source', ["file","link"]);
            $table->bigInteger('orders');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('Products');
            $table->unsignedBigInteger('product_color_id')->nullable();
            $table->foreign('product_color_id')->references('id')->on('ProductColors');
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
        Schema::dropIfExists('media');
    }
};
