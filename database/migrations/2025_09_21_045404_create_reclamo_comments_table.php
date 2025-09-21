<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reclamo_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reclamo_id');
            // quién envía: 'persona', 'agente', 'sistema'
            $table->enum('sender_type', ['persona', 'agente', 'sistema'])->index();

            // si es persona -> persona_id; si es agente -> user_id; si es sistema -> ambos null
            $table->unsignedBigInteger('sender_persona_id')->nullable()->index();
            $table->unsignedBigInteger('sender_user_id')->nullable()->index();

            $table->text('message');           // cuerpo del mensaje
            $table->json('meta')->nullable();  // extras (p.ej. { "old_status": "creado", "new_status": "en_proceso" })

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('reclamo_id')->references('id')->on('reclamos')->onDelete('cascade');
            $table->foreign('sender_persona_id')->references('id')->on('personas')->onDelete('set null');
            $table->foreign('sender_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reclamo_comments');
    }
};
