<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('event_analytics', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('event_id');
            $table->date('date');
            $table->integer('page_views')->default(0);
            $table->integer('unique_visitors')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->integer('tickets_sold')->default(0);
            $table->jsonb('metadata')->default('{}');
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');

            // Chave única para evitar duplicatas
            $table->unique(['event_id', 'date']);

            // Índices
            $table->index(['event_id', 'date']);
            $table->index(['date']);
        });

        // Adicionar constraints via DB::statement para evitar problemas
        DB::statement('ALTER TABLE event_analytics ADD CONSTRAINT event_analytics_page_views_check CHECK (page_views >= 0)');
        DB::statement('ALTER TABLE event_analytics ADD CONSTRAINT event_analytics_unique_visitors_check CHECK (unique_visitors >= 0)');
        DB::statement('ALTER TABLE event_analytics ADD CONSTRAINT event_analytics_tickets_sold_check CHECK (tickets_sold >= 0)');
        DB::statement('ALTER TABLE event_analytics ADD CONSTRAINT event_analytics_total_revenue_check CHECK (total_revenue >= 0)');
    }

    public function down()
    {
        Schema::dropIfExists('event_analytics');
    }
};
