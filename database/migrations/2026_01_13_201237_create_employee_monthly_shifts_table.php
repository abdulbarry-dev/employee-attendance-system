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
        Schema::create('employee_monthly_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('employee_shift_id')->nullable()->constrained('employee_shifts')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Unique constraint: one shift per employee per date
            $table->unique(['user_id', 'date']);

            // Index for efficient lookups
            $table->index(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_monthly_shifts');
    }
};
