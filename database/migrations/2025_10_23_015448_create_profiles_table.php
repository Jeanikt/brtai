<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Este ID deve corresponder ao ID do usuário no Supabase Auth
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->text('avatar_url')->nullable();
            $table->enum('plan_type', ['freemium', 'pro', 'enterprise'])->default('freemium');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->jsonb('metadata')->default('{}');
            $table->string('subscription_id')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();

            // REMOVA esta linha se estiver usando Supabase Auth
            // $table->foreign('id')->references('id')->on('auth.users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('profiles');
    }
};
