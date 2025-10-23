<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('price_tiers', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('event_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('max_quantity')->nullable();
            $table->integer('current_quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('start_sale_at')->nullable();
            $table->timestamp('end_sale_at')->nullable();
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');

            // Índices
            $table->index(['event_id']);
            $table->index(['is_active', 'start_sale_at', 'end_sale_at']);
        });

        // Adiciona constraints de verificação separadamente
        DB::statement('ALTER TABLE price_tiers ADD CONSTRAINT price_tiers_price_check CHECK (price >= 0)');
        DB::statement('ALTER TABLE price_tiers ADD CONSTRAINT price_tiers_current_quantity_check CHECK (current_quantity >= 0)');
        DB::statement('ALTER TABLE price_tiers ADD CONSTRAINT price_tiers_sale_period_check CHECK (start_sale_at IS NULL OR end_sale_at IS NULL OR start_sale_at < end_sale_at)');
    }

    public function down()
    {
        Schema::dropIfExists('price_tiers');
    }
};
