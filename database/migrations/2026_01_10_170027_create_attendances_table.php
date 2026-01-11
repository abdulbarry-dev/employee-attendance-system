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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date')->index(); // To quickly find daily records
            $table->timestamp('check_in');
            $table->timestamp('check_out')->nullable();
            $table->string('status')->default('present'); // present, absent, late, on_break, left_early
            $table->integer('work_duration')->nullable(); // in minutes
            $table->softDeletes();
            $table->timestamps();

            // Ensure one attendance record per user per day
            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
