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
        Schema::create('product_supplies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity_received');
            $table->decimal('unit_cost', 10, 2)->nullable(); // coût unitaire (optionnel)
            $table->string('supplier', 190)->nullable();
            $table->date('received_at')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'received_at']);
            $table->index(['supplier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_supplies');
    }
};
