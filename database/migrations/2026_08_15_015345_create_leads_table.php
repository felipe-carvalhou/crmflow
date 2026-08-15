<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('telefone');
            $table->string('segmento');
            $table->string('origem')->nullable();
            $table->enum('status', ['novo', 'contatado', 'negociando', 'fechado', 'perdido'])
                ->default('novo');
            $table->decimal('valor_estimado', 10, 2)->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamp('ultimo_contato_em')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
