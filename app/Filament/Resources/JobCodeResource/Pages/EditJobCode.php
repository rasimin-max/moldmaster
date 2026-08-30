<?php

namespace App\Filament\Resources\JobCodeResource\Pages;

use App\Filament\Resources\JobCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJobCode extends EditRecord
{
    protected static string $resource = JobCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
