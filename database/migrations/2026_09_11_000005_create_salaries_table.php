<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('salary_period', 20); // e.g. "2026-09"
            $table->decimal('base_salary', 12, 2);
            $table->decimal('allowance', 12, 2)->default(0.00);
            $table->decimal('deduction', 12, 2)->default(0.00);
            $table->decimal('net_salary', 12, 2);
            $table->string('payment_status', 30)->default('pending'); // pending, paid
            $table->date('payment_date')->nullable();
            $table->string('payment_method', 50)->nullable(); // bank_transfer, cash, check
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'salary_period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
