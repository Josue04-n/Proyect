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
        Schema::create('entregas_produccion', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('asignacion_trabajo_id')->constrained('asignaciones_trabajo');
            $table->integer('cantidad_entregada'); 
            $table->dateTime('fecha_recibo_real');
            $table->decimal('tarifa_aplicada', 10, 2); 
            $table->decimal('monto_generado', 10, 2); 
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
        Schema::dropIfExists('entregas_produccion');
    }
};
