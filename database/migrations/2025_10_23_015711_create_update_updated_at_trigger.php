<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Função para atualizar o updated_at
        DB::statement("
            CREATE OR REPLACE FUNCTION update_updated_at_column()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.updated_at = NOW();
                RETURN NEW;
            END;
            $$ language 'plpgsql';
        ");

        // Triggers para cada tabela que tem updated_at
        $tables = [
            'profiles',
            'events',
            'price_tiers',
            'participants',
            'payment_transactions',
            'suppliers'
        ];

        foreach ($tables as $table) {
            DB::statement("
                CREATE TRIGGER update_{$table}_updated_at 
                BEFORE UPDATE ON {$table} 
                FOR EACH ROW 
                EXECUTE FUNCTION update_updated_at_column();
            ");
        }
    }

    public function down()
    {
        // Remover triggers
        $tables = [
            'profiles',
            'events',
            'price_tiers',
            'participants',
            'payment_transactions',
            'suppliers'
        ];

        foreach ($tables as $table) {
            DB::statement("DROP TRIGGER IF EXISTS update_{$table}_updated_at ON {$table}");
        }

        // Remover função
        DB::statement("DROP FUNCTION IF EXISTS update_updated_at_column");
    }
};
