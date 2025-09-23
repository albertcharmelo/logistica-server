<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->string('patente', 100)->nullable()->after('apellidos');
        });
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropColumn('patente');
        });
    }
};
