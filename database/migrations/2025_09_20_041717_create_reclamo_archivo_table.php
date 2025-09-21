<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reclamo_archivo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reclamo_id');
            $table->unsignedBigInteger('archivo_id'); // -> archivos.id
            $table->timestamps();

            $table->foreign('reclamo_id')->references('id')->on('reclamos')->cascadeOnDelete();
            $table->foreign('archivo_id')->references('id')->on('archivos')->cascadeOnDelete();

            $table->unique(['reclamo_id', 'archivo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reclamo_archivo');
    }
};
