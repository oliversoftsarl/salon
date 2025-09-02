<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'client_id')) {
                $table->foreignId('client_id')
                    ->nullable()
                    ->after('payment_method')
                    ->constrained('clients')
                    ->nullOnDelete()
                    ->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'client_id')) {
                $table->dropConstrainedForeignId('client_id');
            }
        });
    }
};
