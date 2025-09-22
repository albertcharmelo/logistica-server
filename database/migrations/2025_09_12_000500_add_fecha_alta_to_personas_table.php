<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->date('fecha_alta')->nullable()->after('observaciones');
        });
        // Optionally backfill fecha_alta from created_at date for existing rows
        DB::statement("UPDATE personas SET fecha_alta = DATE(created_at) WHERE fecha_alta IS NULL");
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropColumn('fecha_alta');
        });
    }
};
