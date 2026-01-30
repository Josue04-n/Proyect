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
        Schema::create('locales', function (Blueprint $table) {
            $table->id(); 
            $table->string('nombre'); 
            $table->string('direccion');
            $table->string('pasaje')->nullable(); 
            $table->string('telefono', 20)->nullable();
            $table->time('hora_apertura')->nullable(); 
            $table->time('hora_cierre')->nullable();  
            $table->boolean('es_principal')->default(false); 
            $table->boolean('is_active')->default(true); 
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
        Schema::dropIfExists('locales');
    }
};
