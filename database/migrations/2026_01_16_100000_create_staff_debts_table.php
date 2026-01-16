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
        Schema::create('staff_debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Staff concerné
            $table->enum('type', ['product_consumption', 'loan', 'advance', 'other'])->default('loan');
            $table->decimal('amount', 12, 2); // Montant de la dette
            $table->decimal('paid_amount', 12, 2)->default(0); // Montant déjà remboursé
            $table->string('description')->nullable(); // Description/raison
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null'); // Si consommation produit
            $table->integer('quantity')->nullable(); // Quantité de produit consommé
            $table->date('debt_date'); // Date de la dette
            $table->date('due_date')->nullable(); // Date d'échéance prévue
            $table->enum('status', ['pending', 'partial', 'paid', 'cancelled'])->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // Qui a enregistré
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('debt_date');
        });

        // Table pour les remboursements
        Schema::create('staff_debt_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_debt_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('payment_method')->default('cash'); // cash, salary_deduction, etc.
            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_debt_payments');
        Schema::dropIfExists('staff_debts');
    }
};

