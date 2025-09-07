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
        Schema::table('addresses', function (Blueprint $table) {
            $table->bigInteger('longitude')->nullable()->change();
            $table->string('latitude')->nullable()->change();

            $table->string('phone', 20)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->bigInteger('longitude')->nullable(false)->change();
            $table->string('latitude')->nullable(false)->change();

            $table->string('phone', 20)->nullable(false)->change();
        });
    }
};
