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
        Schema::create('product_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity_used'); // unité de stock
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('used_at')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'used_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_consumptions');
    }
};
