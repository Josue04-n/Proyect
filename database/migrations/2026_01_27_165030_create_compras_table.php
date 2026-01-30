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
        Schema::create('compras', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->string('numero_comprobante'); 
            $table->date('fecha_compra');
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('estado', ['pendiente', 'recibida', 'anulada'])->default('pendiente');
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
        Schema::dropIfExists('compras');
    }
};
