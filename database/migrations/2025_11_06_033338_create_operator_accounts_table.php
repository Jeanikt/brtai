<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('operator_accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id'); // Alterado para uuid
            $table->enum('account_type', ['CPF', 'CNPJ'])->default('CPF');
            $table->string('document')->unique();
            $table->string('pix_key');
            $table->string('bank_code')->nullable();
            $table->string('agency')->nullable();
            $table->string('account')->nullable();
            $table->boolean('verified')->default(false);
            $table->json('verification_data')->nullable();
            $table->timestamps();

            // Chave estrangeira corrigida
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_account_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('gateway_transaction_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('operator_accounts');
    }
};
