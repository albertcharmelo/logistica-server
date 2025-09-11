<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duenos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('persona_id');
            $table->date('fecha_nacimiento')->nullable();
            $table->string('cuil')->nullable();
            $table->string('cuil_cobrador')->nullable();
            $table->string('cbu_alias')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('persona_id')->references('id')->on('personas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duenos');
    }
};
