<?php

namespace App\Filament\Widgets;

use App\Models\Component;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class CommonPartSafetyStockTable extends BaseWidget
{
    public static function canView(): bool
    {
        try {
            $activeWidgets = app(\App\Settings\GeneralSettings::class)->getActiveWidgetsForUser();
            return empty($activeWidgets) || in_array(class_basename(static::class), $activeWidgets);
        } catch (\Throwable $e) {
            return true;
        }
    }

    protected static ?string $heading = 'Daftar Safety Stock - Common Parts';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Component::query()
                    ->whereHas('category', function (Builder $query) {
                        $query->where('name', 'COMMON PART');
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Komponen')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('stock_minimum')
                    ->label('Min Stok')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\BadgeColumn::make('status_stock')
                    ->label('Status')
                    ->getStateUsing(fn ($record) => $record->stock <= $record->stock_minimum ? 'Kritis' : 'Aman')
                    ->colors([
                        'danger' => 'Kritis',
                        'success' => 'Aman',
                    ]),
            ])
            ->defaultSort('stock', 'asc')
            ->paginated([5, 10, 25]);
    }
}
