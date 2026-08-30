<?php

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Resources\StockMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListStockMovements extends ListRecords
{
    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(\App\Filament\Exports\StockMovementExporter::class)
                ->label('Export'),
            Actions\ImportAction::make()
                ->importer(\App\Filament\Imports\StockMovementImporter::class)
                ->label('Import'),
            Actions\CreateAction::make()->label('+ Input Transaksi')
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua'),
            'pending' => Tab::make('Menunggu Approval')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending'))
                ->badge(fn() => \App\Models\StockMovement::where('status', 'pending')->count()),
            'in' => Tab::make('Barang Masuk')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'in')),
            'out' => Tab::make('Barang Keluar')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'out')),
            'return' => Tab::make('Return')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'return')),
        ];
    }
}
