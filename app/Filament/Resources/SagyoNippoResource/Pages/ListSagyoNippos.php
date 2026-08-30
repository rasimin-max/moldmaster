<?php

namespace App\Filament\Resources\SagyoNippoResource\Pages;

use App\Filament\Resources\SagyoNippoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSagyoNippos extends ListRecords
{
    protected static string $resource = SagyoNippoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->color('primary'),
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Resources\Components\Tab::make('Semua Laporan'),
            'project' => \Filament\Resources\Components\Tab::make('Project')
                ->modifyQueryUsing(fn ($query) => $query->whereHas('items', fn ($q) => $q->whereNotNull('project_id'))),
            'non_project' => \Filament\Resources\Components\Tab::make('Non-Project')
                ->modifyQueryUsing(fn ($query) => $query->whereHas('items', fn ($q) => $q->whereNull('project_id'))),
        ];
    }
}
