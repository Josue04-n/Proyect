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
        //
        Schema::create('orden_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('orden_produccion_id')->constrained('ordenes_produccion')->cascadeOnDelete();
        
        $table->foreignId('tipo_prenda_id')->constrained('tipos_prenda');
        $table->string('talla', 50);
        $table->string('color', 50)->nullable();
        $table->integer('cantidad');
        
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
