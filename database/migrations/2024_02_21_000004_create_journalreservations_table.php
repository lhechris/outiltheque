<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journal_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reference');
            $table->string('nom');
            $table->string('email');
            $table->string('telephone');
            $table->dateTime('debut');
            $table->unsignedBigInteger('outil_id');
            $table->dateTime('fin')->nullable();
            $table->string("commentaire")->nullable();
            $table->unsignedBigInteger('paiement_id')->nullable();
            $table->string("state")->nullable();
            $table->string("paiement_state")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_reservations');
    }
};
