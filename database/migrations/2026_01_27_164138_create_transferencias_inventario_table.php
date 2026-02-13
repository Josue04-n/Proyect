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
        Schema::create('transferencias_inventario', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('origen_local_id')->constrained('locales');
            $table->foreignId('destino_local_id')->constrained('locales');
            $table->foreignId('prenda_tienda_id')->constrained('prendas_tienda');
            $table->integer('cantidad');
            $table->dateTime('fecha_transferencia');
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
        Schema::dropIfExists('transferencias_inventario');
    }
};
