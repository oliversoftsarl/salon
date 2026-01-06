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
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('type', ['entry', 'exit']); // Entrée ou Sortie
            $table->enum('category', [
                // Entrées
                'sale',              // Vente (produits/services)
                'other_income',      // Autres revenus
                // Sorties
                'expense',           // Dépenses générales
                'bank_deposit',      // Dépôt banque
                'salary_advance',    // Avance sur salaire
                'internal_expense',  // Dépenses internes
                'purchase',          // Acquisition produits/autres
                'supplier_payment',  // Paiement fournisseur
            ]);
            $table->decimal('amount', 12, 2);
            $table->string('description')->nullable();
            $table->string('reference')->nullable(); // Référence externe (facture, reçu, etc.)
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'mobile_money', 'check'])->default('cash');

            // Relations optionnelles
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Pour avances salaire
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['date', 'type']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};

