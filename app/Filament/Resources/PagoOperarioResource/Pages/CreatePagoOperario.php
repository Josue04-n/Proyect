<?php

namespace App\Filament\Resources\PagoOperarioResource\Pages;

use App\Filament\Resources\PagoOperarioResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreatePagoOperario extends CreateRecord
{
    protected static string $resource = PagoOperarioResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        DB::statement('CALL SP_PAGAR_OPERARIO(?, ?, ?, ?, ?)', [
            $data['operario_id'],       
            $data['monto'],             
            $data['forma_pago'],      
            $data['observacion'] ?? null, 
            auth()->id(),               
        ]);

        return static::getModel()::latest()->first();
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}