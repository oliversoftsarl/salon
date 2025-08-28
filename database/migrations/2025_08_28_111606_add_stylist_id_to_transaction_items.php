<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            if (!Schema::hasColumn('transaction_items', 'stylist_id')) {
                $table->foreignId('stylist_id')
                    ->nullable()
                    ->after('service_id')
                    ->constrained('users')
                    ->nullOnDelete()
                    ->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            if (Schema::hasColumn('transaction_items', 'stylist_id')) {
                $table->dropConstrainedForeignId('stylist_id');
            }
        });
    }
};
