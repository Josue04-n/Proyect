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
        Schema::create('asignaciones_trabajo', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('orden_item_id')->constrained('orden_items')->cascadeOnDelete();
            $table->foreignId('operario_id')->constrained('operarios');
            $table->integer('cantidad_asignada'); 
            $table->date('fecha_asignacion');
            $table->date('fecha_estimada_entrega')->nullable(); 
            $table->enum('estado', ['pendiente', 'en_proceso', 'completada', 'cancelada'])
                  ->default('pendiente');
            $table->text('observacion')->nullable(); 
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones_trabajo');
    }
};
