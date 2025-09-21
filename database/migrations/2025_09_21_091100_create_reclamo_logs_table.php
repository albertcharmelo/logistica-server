<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reclamo_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reclamo_id');
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable(); // user id
            $table->timestamps();

            $table->foreign('reclamo_id')->references('id')->on('reclamos')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reclamo_logs');
    }
};
