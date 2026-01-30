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
        Schema::create('inventario_sucursales', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('prenda_tienda_id')->constrained('prendas_tienda');
            $table->foreignId('local_id')->constrained('locales');
            $table->integer('cantidad')->default(0);
            $table->integer('stock_minimo')->default(5); 
            $table->unique(['prenda_tienda_id', 'local_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_sucursales');
    }
};
