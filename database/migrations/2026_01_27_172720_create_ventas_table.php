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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->dateTime('fecha_emision')->default(now());
            $table->decimal('subtotal', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0); 
            $table->decimal('impuestos', 10, 2)->default(0); 
            $table->decimal('total', 10, 2);
            $table->enum('estado_pago', ['pagado', 'pendiente', 'anulado'])->default('pagado');
            $table->string('metodo_pago')->default('efectivo');
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
        Schema::dropIfExists('ventas');
    }
};
