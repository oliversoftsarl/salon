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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, decimal, boolean, json
            $table->string('group')->default('general'); // general, revenue, etc.
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insérer le paramètre par défaut pour le montant cible hebdomadaire
        DB::table('settings')->insert([
            'key' => 'weekly_revenue_target',
            'value' => '150000',
            'type' => 'decimal',
            'group' => 'revenue',
            'description' => 'Montant cible hebdomadaire pour chaque coiffeur (en FC)',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
