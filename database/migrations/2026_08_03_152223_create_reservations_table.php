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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reference');
            $table->foreignId('user_id');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->foreignId('tool_id')->constrained()->cascadeOnDelete();
            $table->dateTime('date_start');
            $table->dateTime('date_end')->nullable();
            $table->string("state")->nullable();
            $table->string("payment_state")->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->string("comment")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
