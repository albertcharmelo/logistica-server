<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reclamos', function (Blueprint $table) {
            $table->id();

            // Relaciones mínimas (según tu BD)
            $table->unsignedBigInteger('persona_id');       // -> personas.id (transportista)
            $table->unsignedBigInteger('agente_id')->nullable(); // -> users.id (agente)
            $table->unsignedBigInteger('reclamo_type_id');  // -> reclamo_types.id

            // Campos propios mínimos
            $table->text('detalle')->nullable();
            $table->string('status', 40)->default('creado');
            // creado | asignado_al_area | en_proceso | pendiente_de_resolucion | solucionado

            $table->timestamps();
            $table->softDeletes();

            // FKs
            $table->foreign('persona_id')->references('id')->on('personas')->cascadeOnDelete();
            $table->foreign('agente_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reclamo_type_id')->references('id')->on('reclamo_types')->cascadeOnDelete();

            $table->index(['persona_id', 'reclamo_type_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reclamos');
    }
};
