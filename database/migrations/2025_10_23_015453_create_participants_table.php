<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_id');
            $table->uuid('price_tier_id')->nullable();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('payment_status')->default('pending');
            $table->decimal('payment_amount', 10, 2)->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('pix_code')->nullable();
            $table->timestamp('pix_expires_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->jsonb('metadata')->default('{}');
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('price_tier_id')->references('id')->on('price_tiers')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('participants');
    }
};