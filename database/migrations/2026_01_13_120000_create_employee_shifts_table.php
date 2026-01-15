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
        Schema::create('employee_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('day_of_week', 3); // sun, mon, tue, wed, thu, fri, sat
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('grace_period_minutes')->default(10);
            $table->unsignedInteger('break_allowance_minutes')->default(60);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'day_of_week']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('employee_shift_id')
                ->nullable()
                ->after('user_id')
                ->constrained('employee_shifts')
                ->nullOnDelete();

            // Replace daily uniqueness with shift-based uniqueness
            $table->dropUnique('attendances_user_id_date_unique');
            $table->unique(['user_id', 'date', 'employee_shift_id'], 'attendances_user_shift_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_user_shift_unique');
            $table->unique(['user_id', 'date'], 'attendances_user_id_date_unique');
            $table->dropConstrainedForeignId('employee_shift_id');
        });

        Schema::dropIfExists('employee_shifts');
    }
};
