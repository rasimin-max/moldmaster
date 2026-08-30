<?php

namespace App\Filament\Resources\MachineProgramResource\Pages;

use App\Filament\Resources\MachineProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMachineProgram extends EditRecord
{
    protected static string $resource = MachineProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
