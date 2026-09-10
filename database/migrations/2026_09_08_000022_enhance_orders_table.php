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
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_custom')->default(false)->after('order_status');
            $table->text('special_instructions')->nullable()->after('is_custom');
            $table->dateTime('pickup_date')->nullable()->after('special_instructions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_custom', 'special_instructions', 'pickup_date']);
        });
    }
};
