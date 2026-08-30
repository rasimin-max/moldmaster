<?php

namespace App\Filament\Resources\MoldResource\Pages;

use App\Filament\Resources\MoldResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMold extends EditRecord
{
    protected static string $resource = MoldResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
