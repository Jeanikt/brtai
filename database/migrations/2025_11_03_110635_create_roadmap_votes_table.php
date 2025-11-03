<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roadmap_votes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('roadmap_item_id');
            $table->uuid('user_id');
            $table->enum('vote_type', ['like', 'dislike']);
            $table->timestamps();

            $table->foreign('roadmap_item_id')->references('id')->on('roadmap_items')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('profiles')->onDelete('cascade');

            $table->unique(['roadmap_item_id', 'user_id']); // um voto por sugestão por usuário
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roadmap_votes');
    }
};
