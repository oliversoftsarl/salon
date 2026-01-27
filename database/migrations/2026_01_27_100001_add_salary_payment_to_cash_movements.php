<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier l'ENUM pour ajouter 'salary_payment'
        DB::statement("ALTER TABLE cash_movements MODIFY COLUMN category ENUM(
            'sale',
            'other_income',
            'expense',
            'bank_deposit',
            'salary_advance',
            'salary_payment',
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
        // Revenir à l'ancien ENUM sans 'salary_payment'
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
};
