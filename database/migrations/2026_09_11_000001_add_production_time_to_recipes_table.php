<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->integer('production_time')->nullable()->after('instructions')->comment('Estimated production time in minutes');
            $table->integer('yield_quantity')->default(1)->after('production_time')->comment('Expected output units from recipe');
        });
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn(['production_time', 'yield_quantity']);
        });
    }
};
