<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('cpf', 14)->after('phone')->nullable();

            // Índice único para CPF por evento (evita duplicação no mesmo evento)
            $table->unique(['event_id', 'cpf'], 'participants_event_cpf_unique');

            // Índice único para telefone por evento
            $table->unique(['event_id', 'phone'], 'participants_event_phone_unique');
        });
    }

    public function down()
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropUnique('participants_event_cpf_unique');
            $table->dropUnique('participants_event_phone_unique');
            $table->dropColumn('cpf');
        });
    }
};
