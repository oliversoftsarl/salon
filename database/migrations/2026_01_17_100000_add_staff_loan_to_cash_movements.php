<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier l'ENUM pour ajouter 'staff_loan'
        DB::statement("ALTER TABLE cash_movements MODIFY COLUMN category ENUM(
            'sale',
            'other_income',
            'expense',
            'bank_deposit',
            'salary_advance',
            'staff_loan',
            'internal_expense',
            'purchase',
            'supplier_payment'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'ancien ENUM sans 'staff_loan'
        DB::statement("ALTER TABLE cash_movements MODIFY COLUMN category ENUM(
            'sale',
            'other_income',
            'expense',
            'bank_deposit',
            'salary_advance',
            'internal_expense',
            'purchase',
            'supplier_payment'
        )");
    }
};

