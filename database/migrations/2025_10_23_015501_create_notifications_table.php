<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->uuid('participant_id')->nullable();
            $table->uuid('event_id')->nullable();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->string('channel')->default('push');
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->jsonb('metadata')->default('{}');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('profiles')->onDelete('cascade');
            $table->foreign('participant_id')->references('id')->on('participants')->onDelete('cascade');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
