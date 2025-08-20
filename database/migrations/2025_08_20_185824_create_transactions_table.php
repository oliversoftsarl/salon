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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique()->nullable();
            $table->enum('type', ['sale', 'refund'])->default('sale');
            $table->decimal('total', 10, 2);
            $table->string('payment_method')->nullable();
//            $table->foreignId('cashier_id')
//                ->nullable()
//                ->constrained('users')
//                ->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
