<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Crear columna temporal con el NUEVO ENUM (incluye 'creador')
        Schema::table('reclamo_comments', function (Blueprint $table) {
            $table->enum('sender_type_tmp', ['persona', 'agente', 'sistema', 'creador'])
                ->default('sistema')
                ->after('sender_type');
        });

        // 2) Copiar datos existentes al nuevo set (si algo no coincide, lo normalizamos a 'sistema')
        DB::statement("
            UPDATE `reclamo_comments`
            SET `sender_type_tmp` = CASE
                WHEN `sender_type` IN ('persona','agente','sistema','creador') THEN `sender_type`
                ELSE 'sistema'
            END
        ");

        // 3) Borrar la columna vieja
        Schema::table('reclamo_comments', function (Blueprint $table) {
            $table->dropColumn('sender_type');
        });

        // 4) Renombrar la temporal a definitiva
        DB::statement("ALTER TABLE `reclamo_comments` RENAME COLUMN `sender_type_tmp` TO `sender_type`");
    }

    public function down(): void
    {
        // Volver al ENUM anterior (sin 'creador')
        Schema::table('reclamo_comments', function (Blueprint $table) {
            $table->enum('sender_type_old', ['persona', 'agente', 'sistema'])
                ->default('sistema')
                ->after('sender_type');
        });

        DB::statement("
            UPDATE `reclamo_comments`
            SET `sender_type_old` = CASE
                WHEN `sender_type` IN ('persona','agente','sistema') THEN `sender_type`
                ELSE 'sistema'
            END
        ");

        Schema::table('reclamo_comments', function (Blueprint $table) {
            $table->dropColumn('sender_type');
        });

        DB::statement("ALTER TABLE `reclamo_comments` RENAME COLUMN `sender_type_old` TO `sender_type`");
    }
};
