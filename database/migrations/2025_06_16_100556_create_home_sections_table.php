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
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->enum(
                'type',
                [
                    'banner',
                    'category_list',
                    'category_products',
                    'brand_list',
                    'brand_products',
                    'recommended_products',
                    'video',
                    'new_products',
                    'most_sold_products',
                    'featured_sections',
                ]
            );
            $table->enum('status', ['static', 'dynamic'])->default('static');
            $table->integer('limit')->nullable();
            $table->boolean('can_show_more')->default(false);
            $table->string('show_more_path')->nullable();
            $table->integer('orders')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('data')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_sections');
    }
};
