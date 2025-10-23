<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ai_suggestions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->uuid('event_id')->nullable();
            $table->string('suggestion_type');
            $table->jsonb('content');
            $table->decimal('confidence_score', 3, 2)->nullable();
            $table->boolean('is_applied')->default(false);
            $table->timestamp('applied_at')->nullable();
            $table->integer('feedback_score')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('profiles')->onDelete('cascade');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_suggestions');
    }
};
