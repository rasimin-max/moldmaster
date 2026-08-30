<?php

namespace App\Filament\Resources\MaintenanceResource\Pages;

use App\Filament\Resources\MaintenanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMaintenances extends ListRecords
{
    protected static string $resource = MaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('+ Lapor Kerusakan')];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua'),
            'pending' => Tab::make('Menunggu')
                ->modifyQueryUsing(fn(Builder $q) => $q->where('status', 'pending'))
                ->badge(fn() => \App\Models\Maintenance::where('status', 'pending')->count()),
            'in_progress' => Tab::make('Proses')
                ->modifyQueryUsing(fn(Builder $q) => $q->where('status', 'in_progress')),
            'breakdown' => Tab::make('Breakdown')
                ->modifyQueryUsing(fn(Builder $q) => $q->where('type', 'breakdown')->whereNotIn('status', ['completed', 'cancelled']))
                ->badge(fn() => \App\Models\Maintenance::where('type', 'breakdown')->whereNotIn('status', ['completed', 'cancelled'])->count()),
            'completed' => Tab::make('Selesai')
                ->modifyQueryUsing(fn(Builder $q) => $q->where('status', 'completed')),
        ];
    }
}
