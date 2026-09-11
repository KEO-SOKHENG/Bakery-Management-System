<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('shelf_life')->nullable()->after('minimum_stock')->comment('Shelf life in days');
            $table->date('expiry_date')->nullable()->after('shelf_life')->comment('Current batch or stock expiration date');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['shelf_life', 'expiry_date']);
        });
    }
};
