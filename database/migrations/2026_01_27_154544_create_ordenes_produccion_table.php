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
        Schema::create('ordenes_produccion', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('contrato_id')->nullable()->constrained('contratos');
            $table->date('fecha_recepcion'); 
            $table->date('fecha_entrega_estimada'); 
            $table->enum('estado', ['pendiente', 'en_proceso', 'parcial', 'finalizada', 'cerrada', 'entregada'])
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
        Schema::dropIfExists('ordenes_produccion');
    }
};
