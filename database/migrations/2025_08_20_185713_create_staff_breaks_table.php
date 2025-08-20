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
        Schema::create('staff_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->boolean('recurring')->default(false);
            $table->timestamps();

            $table->index(['staff_id', 'start_at']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_breaks');
    }
};
