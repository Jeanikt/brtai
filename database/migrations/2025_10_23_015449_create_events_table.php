<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('organizer_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->timestamp('event_date');
            $table->text('location')->nullable();
            $table->boolean('location_reveal_after_payment')->default(true);
            $table->text('header_image_url')->nullable();
            $table->string('theme')->nullable();
            $table->text('rules')->nullable();
            $table->integer('max_participants')->default(50);
            $table->enum('status', ['draft', 'active', 'cancelled', 'completed'])->default('draft');
            $table->boolean('is_public')->default(true);
            $table->jsonb('metadata')->default('{}');
            $table->timestamps();

            $table->foreign('organizer_id')->references('id')->on('profiles')->onDelete('cascade');

            // Índices para performance
            $table->index(['organizer_id']);
            $table->index(['slug']);
            $table->index(['status', 'event_date']);
            $table->index(['is_public', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('events');
    }
};
