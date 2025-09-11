<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transporte_temporals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('persona_id');
            $table->string('guia_remito')->nullable();
            $table->decimal('valor_viaje', 12, 2)->nullable();
            $table->string('origen')->nullable();
            $table->string('destino')->nullable();
            $table->unsignedBigInteger('estado_servicio_id')->nullable();
            $table->timestamps();

            $table->foreign('persona_id')->references('id')->on('personas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transporte_temporals');
    }
};
