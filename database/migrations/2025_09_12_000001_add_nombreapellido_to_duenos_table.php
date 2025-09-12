<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('duenos', function (Blueprint $table) {
            // allow nullable to avoid issues with existing records, set default for new inserts
            $table->string('nombreapellido')->nullable()->default('Sin nombre')->after('persona_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('duenos', function (Blueprint $table) {
            $table->dropColumn('nombreapellido');
        });
    }
};
