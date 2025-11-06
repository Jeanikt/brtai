<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('plan_type'); // pro, enterprise, etc
            $table->string('status'); // pending, active, cancelled, expired
            $table->decimal('amount', 10, 2);
            $table->string('gateway'); // woovi, etc
            $table->string('gateway_transaction_id')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'status']);
            $table->index('ends_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
};
