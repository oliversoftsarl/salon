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
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE cash_movements MODIFY COLUMN category VARCHAR(100) NOT NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE cash_movements ALTER COLUMN category TYPE VARCHAR(100)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE cash_movements MODIFY COLUMN category ENUM('sale','other_income','expense','bank_deposit','salary_advance','salary_payment','staff_loan','internal_expense','purchase','supplier_payment','tax','rent','socode_electricity','snel_electricity','regideso','security','plumber','electrician','internet','water_punctual','other_exit') NOT NULL");
            return;
        }

        // Pas de rollback automatique vers ENUM pour PostgreSQL.
    }
};

