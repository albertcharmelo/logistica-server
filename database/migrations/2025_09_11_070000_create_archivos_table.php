<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('persona_id');
            $table->unsignedBigInteger('tipo_archivo_id'); // references fyle_types.id
            $table->string('carpeta');
            $table->string('ruta'); // relative path within disk
            $table->string('disk')->default('public');
            $table->string('nombre_original')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('persona_id')->references('id')->on('personas')->onDelete('cascade');
            $table->foreign('tipo_archivo_id')->references('id')->on('fyle_types')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archivos');
    }
};
