<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('apellidos')->nullable();
            $table->string('nombres')->nullable();
            $table->string('cuil')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->decimal('pago', 12, 2)->nullable();
            $table->string('cbu_alias')->nullable();
            $table->boolean('combustible')->default(false);
            $table->unsignedBigInteger('unidad_id')->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->unsignedBigInteger('estado_id')->nullable();
            $table->unsignedTinyInteger('tipo')->nullable();
            $table->string('observaciontarifa')->nullable();
            $table->unsignedTinyInteger('tarifaespecial')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('unidad_id')->references('id')->on('unidades')->nullOnDelete();
            $table->foreign('cliente_id')->references('id')->on('clientes')->nullOnDelete();
            $table->foreign('sucursal_id')->references('id')->on('sucursals')->nullOnDelete();
            $table->foreign('estado_id')->references('id')->on('estados')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
