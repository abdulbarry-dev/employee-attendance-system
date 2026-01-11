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
        Schema::create('employee_penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('late'); // late, break_overage
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->date('occurred_on');
            $table->unsignedInteger('minutes_late')->default(0);
            $table->unsignedInteger('break_overage_minutes')->default(0);
            $table->unsignedInteger('penalty_steps')->default(0); // each step = 5 minutes chunk
            $table->decimal('penalty_amount', 10, 2)->default(0);
            $table->text('reason')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'occurred_on']);
            $table->index(['period_start', 'period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_penalties');
    }
};
