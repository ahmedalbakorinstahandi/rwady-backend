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

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('key');
            $table->longText('value')->nullable();
            $table->enum('type', ["int","float","text","long_text","list","json","image","file","bool","time","date","datetime","html"]);
            $table->boolean('allow_null')->default(false);
            $table->boolean('is_setting');
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
        Schema::dropIfExists('settings');
    }
};
