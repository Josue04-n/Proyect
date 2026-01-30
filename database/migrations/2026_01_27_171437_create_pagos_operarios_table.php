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
        Schema::create('pagos_operarios', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('operario_id')->constrained('operarios');
            $table->date('fecha_pago');
            $table->decimal('monto', 10, 2);
            $table->string('forma_pago');
            $table->text('observacion')->nullable();
            $table->foreignId('usuario_paga_id')->constrained('users');
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
        Schema::dropIfExists('pagos_operarios');
    }
};
