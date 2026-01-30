<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id(); 
            $table->boolean('is_active')->default(true);
            $table->string('razon_social'); 
            $table->string('ruc', 20)->unique(); 
            $table->string('contacto')->nullable(); 
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps(); 
        });

        // ---------------------------------------------------------
        // REQUERIMIENTO: Insertar registro "Proveedor Ocasional"
        // ---------------------------------------------------------
        DB::table('proveedores')->insert([
            'razon_social' => 'Proveedor Ocasional',
            'ruc' => '9999999999001', 
            'contacto' => 'Ventas Varias',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes_');
    }
};
