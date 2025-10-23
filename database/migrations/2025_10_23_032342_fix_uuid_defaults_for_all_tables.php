<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Lista de tabelas que usam UUID como chave primária
        $tables = [
            'events',
            'price_tiers',
            'participants',
            'payment_transactions',
            'ai_suggestions',
            'suppliers',
            'event_analytics',
            'notifications',
            'webhook_logs'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN id SET DEFAULT gen_random_uuid()");
            }
        }
    }

    public function down()
    {
        // Reverter se necessário
        $tables = [
            'events',
            'price_tiers',
            'participants',
            'payment_transactions',
            'ai_suggestions',
            'suppliers',
            'event_analytics',
            'notifications',
            'webhook_logs'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN id DROP DEFAULT");
            }
        }
    }
};
