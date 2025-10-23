<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE MATERIALIZED VIEW event_summary_view AS
            SELECT 
                e.id,
                e.name,
                e.event_date,
                e.organizer_id,
                COUNT(p.id) as total_participants,
                COUNT(CASE WHEN p.payment_status = 'paid' THEN 1 END) as confirmed_participants,
                COALESCE(SUM(CASE WHEN p.payment_status = 'paid' THEN p.payment_amount ELSE 0 END), 0) as total_revenue,
                COALESCE(SUM(CASE WHEN p.payment_status = 'paid' THEN pt.fee_amount ELSE 0 END), 0) as total_fees
            FROM events e
            LEFT JOIN participants p ON e.id = p.event_id
            LEFT JOIN payment_transactions pt ON p.id = pt.participant_id AND pt.status = 'completed'
            GROUP BY e.id, e.name, e.event_date, e.organizer_id
        ");

        // Criar índices para a view materializada
        DB::statement("CREATE UNIQUE INDEX idx_event_summary_view_id ON event_summary_view (id)");
        DB::statement("CREATE INDEX idx_event_summary_view_organizer ON event_summary_view (organizer_id)");
        DB::statement("CREATE INDEX idx_event_summary_view_date ON event_summary_view (event_date)");
    }

    public function down()
    {
        DB::statement("DROP MATERIALIZED VIEW IF EXISTS event_summary_view");
    }
};
