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
        Schema::create('prendas_tienda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('local_id')->constrained('locales');
            $table->foreignId('tipo_prenda_id')->constrained('tipos_prenda');
            $table->string('talla', 10); 
            $table->string('color', 50)->nullable();
            $table->decimal('precio_venta', 10, 2); 
            $table->integer('stock_actual')->default(0); 
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
        Schema::dropIfExists('prendas_tienda');
    }
};
