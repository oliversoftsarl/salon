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
        Schema::create('staff_weekly_revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->integer('year');
            $table->integer('week_number'); // Numéro de la semaine (1-53)
            $table->date('week_start'); // Date de début de la semaine
            $table->date('week_end'); // Date de fin de la semaine
            $table->decimal('target_amount', 15, 2); // Montant cible pour cette semaine
            $table->decimal('actual_amount', 15, 2)->default(0); // Montant réellement réalisé
            $table->decimal('difference', 15, 2)->default(0); // Différence (positif = surplus, négatif = manquant)
            $table->decimal('cumulative_shortage', 15, 2)->default(0); // Cumul des manquants
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['staff_id', 'year', 'week_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_weekly_revenues');
    }
};
