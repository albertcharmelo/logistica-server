<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'unidades',
            'sucursals',
            'fyle_types',
            'clientes',
            'personas',
            'duenos',
            'transporte_temporals',
            'estados',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'unidades',
            'sucursals',
            'fyle_types',
            'clientes',
            'personas',
            'duenos',
            'transporte_temporals',
            'estados',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }
};
