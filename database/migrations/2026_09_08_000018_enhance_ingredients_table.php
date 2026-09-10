<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            if (!Schema::hasColumn('ingredients', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('minimum_quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            if (Schema::hasColumn('ingredients', 'expiry_date')) {
                $table->dropColumn('expiry_date');
            }
        });
    }
};
