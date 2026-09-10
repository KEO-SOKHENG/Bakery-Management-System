<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            if (!Schema::hasColumn('productions', 'batch_number')) {
                $table->string('batch_number')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('productions', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('productions', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('scheduled_at');
            }
            if (!Schema::hasColumn('productions', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }
            if (!Schema::hasColumn('productions', 'baker_id')) {
                $table->foreignId('baker_id')->nullable()->after('user_id')->constrained('users')->onDelete('set null');
            }
            // Allow recipe_id to be nullable for products without recipes
            $table->foreignId('recipe_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            if (Schema::hasColumn('productions', 'baker_id')) {
                $table->dropConstrainedForeignId('baker_id');
            }
            if (Schema::hasColumn('productions', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
            if (Schema::hasColumn('productions', 'started_at')) {
                $table->dropColumn('started_at');
            }
            if (Schema::hasColumn('productions', 'scheduled_at')) {
                $table->dropColumn('scheduled_at');
            }
            if (Schema::hasColumn('productions', 'batch_number')) {
                $table->dropColumn('batch_number');
            }
        });
    }
};
