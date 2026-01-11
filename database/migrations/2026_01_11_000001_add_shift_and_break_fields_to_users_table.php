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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('monthly_salary', 10, 2)->nullable()->after('is_banned');
            $table->time('shift_start')->nullable()->after('monthly_salary');
            $table->time('shift_end')->nullable()->after('shift_start');
            $table->unsignedInteger('grace_period_minutes')->default(10)->after('shift_end');
            $table->unsignedInteger('break_allowance_minutes')->default(60)->after('grace_period_minutes');
            $table->json('working_days')->nullable()->after('break_allowance_minutes'); // e.g., ["sun","mon","tue"]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'monthly_salary',
                'shift_start',
                'shift_end',
                'grace_period_minutes',
                'break_allowance_minutes',
                'working_days',
            ]);
        });
    }
};
