<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('sub_category')->nullable()->after('category');
            $table->integer('lifespan_months')->nullable()->after('purchase_price'); // Durée de vie en mois
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['sub_category', 'lifespan_months']);
        });
    }
};
