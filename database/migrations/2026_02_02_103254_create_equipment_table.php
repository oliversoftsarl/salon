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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable(); // Code interne
            $table->string('category'); // sechoir, tondeuse, fauteuil, miroir, etc.
            $table->string('brand')->nullable(); // Marque
            $table->string('model')->nullable(); // Modèle
            $table->string('serial_number')->nullable(); // Numéro de série
            $table->date('purchase_date')->nullable(); // Date d'achat
            $table->decimal('purchase_price', 12, 2)->nullable(); // Prix d'achat
            $table->string('supplier')->nullable(); // Fournisseur
            $table->string('status')->default('operational'); // operational, maintenance, broken, retired
            $table->string('condition')->default('good'); // new, good, fair, poor
            $table->string('location')->nullable(); // Emplacement dans le salon
            $table->date('warranty_expiry')->nullable(); // Fin de garantie
            $table->date('last_maintenance')->nullable(); // Dernière maintenance
            $table->date('next_maintenance')->nullable(); // Prochaine maintenance prévue
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // Staff assigné
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Table pour l'historique des maintenances
        Schema::create('equipment_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->date('maintenance_date');
            $table->string('type'); // preventive, corrective, cleaning
            $table->string('performed_by')->nullable(); // Technicien
            $table->decimal('cost', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->text('parts_replaced')->nullable(); // Pièces remplacées
            $table->date('next_maintenance')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_maintenances');
        Schema::dropIfExists('equipment');
    }
};
