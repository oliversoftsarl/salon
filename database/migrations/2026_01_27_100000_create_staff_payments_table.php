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
        Schema::create('staff_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('payment_type', ['weekly', 'monthly'])->default('monthly');
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0); // Dettes déduites
            $table->decimal('shortage_deduction', 12, 2)->default(0); // Manquants déduits
            $table->decimal('net_amount', 12, 2); // Montant net payé
            $table->string('period'); // ex: "2026-W04" pour semaine ou "2026-01" pour mois
            $table->date('period_start');
            $table->date('period_end');
            $table->date('payment_date');
            $table->string('payment_method')->default('cash');
            $table->text('notes')->nullable();
            $table->json('debt_details')->nullable(); // Détails des dettes déduites
            $table->json('shortage_details')->nullable(); // Détails des manquants déduits
            $table->foreignId('cash_movement_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['user_id', 'period']);
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_payments');
    }
};
