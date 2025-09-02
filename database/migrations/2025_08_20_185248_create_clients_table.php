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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->index();
            $table->string('email')->unique()->nullable();
            $table->date('birthdate')->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->integer('loyalty_points')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['last_name', 'first_name']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
