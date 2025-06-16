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

        Schema::create('seo', function (Blueprint $table) {
            $table->id();
            $table->text('meta_title')->nullable();
            $table->longText('meta_description')->nullable();
            $table->text('keywords')->nullable();
            $table->string('image', 100)->nullable();
            $table->unsignedBigInteger('seoable_id');
            $table->string('seoable_type');
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
        Schema::dropIfExists('seo');
    }
};
