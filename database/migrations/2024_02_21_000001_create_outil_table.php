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
        Schema::create('outils', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('description');
            $table->integer('prix');
            $table->integer('duree');
            $table->integer('nombre');
            $table->integer('categorie_id');
            $table->integer('file_id')->nullable()->references('id')->on('files');
            $table->integer('file2_id')->nullable()->references('id')->on('files');
            $table->string("conseil")->nullable();
            $table->string("precaution")->nullable();
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
        Schema::dropIfExists('table_outils');
    }
};
