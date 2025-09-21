<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Crear columna temporal con el ENUM deseado
        Schema::table('reclamos', function (Blueprint $table) {
            $table->enum('status_tmp', ['creado', 'en_proceso', 'solucionado'])
                ->default('creado')
                ->after('status');
        });

        // 2) Copiar datos actuales validando contra el set permitido
        DB::statement("
            UPDATE `reclamos`
            SET `status_tmp` = CASE
                WHEN `status` IN ('creado','en_proceso','solucionado') THEN `status`
                ELSE 'creado'
            END
        ");

        // 3) Eliminar la columna antigua
        Schema::table('reclamos', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // 4) Renombrar la temporal a definitiva (sin DBAL)
        // MySQL 8+ permite RENAME COLUMN sin especificar el tipo
        DB::statement("ALTER TABLE `reclamos` RENAME COLUMN `status_tmp` TO `status`");
    }

    public function down(): void
    {
        // Volver a VARCHAR(50) (permitiendo valores previos sin restricción)
        Schema::table('reclamos', function (Blueprint $table) {
            $table->string('status_old', 50)->nullable()->after('status');
        });

        DB::statement("
            UPDATE `reclamos`
            SET `status_old` = `status`
        ");

        Schema::table('reclamos', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        DB::statement("ALTER TABLE `reclamos` RENAME COLUMN `status_old` TO `status`");
    }
};
