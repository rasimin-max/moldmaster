<?php

namespace App\Filament\Resources\MoldResource\Pages;

use App\Filament\Resources\MoldResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMold extends CreateRecord
{
    protected static string $resource = MoldResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
