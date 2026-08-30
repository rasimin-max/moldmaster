<?php

namespace App\Filament\Resources\ComponentCategoryResource\Pages;

use App\Filament\Resources\ComponentCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComponentCategories extends ListRecords
{
    protected static string $resource = ComponentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
