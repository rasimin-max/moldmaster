<?php

namespace App\Filament\Resources\StockMovementResource\Pages;

use App\Filament\Resources\StockMovementResource;
use App\Filament\Traits\HasExcelImport;
use App\Imports\StockMovementsImport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListStockMovements extends ListRecords
{
    use HasExcelImport;

    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(\App\Filament\Exports\StockMovementExporter::class)
                ->label('Export'),
            $this->makeImportAction('Import', StockMovementsImport::class, 'Import Transaksi Stok'),
            Actions\CreateAction::make()->label('+ Input Transaksi'),
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
