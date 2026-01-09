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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('from_currency', 3)->default('USD'); // Devise source
            $table->string('to_currency', 3)->default('CDF');   // Devise cible
            $table->decimal('rate', 15, 4);                      // Taux de change (ex: 1 USD = 2800 CDF)
            $table->date('effective_date');                      // Date d'effet
            $table->boolean('is_active')->default(true);         // Taux actif
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['from_currency', 'to_currency', 'effective_date']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};

