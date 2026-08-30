<?php

namespace App\Filament\Widgets;

use App\Models\StockMovement;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentMovementsWidget extends BaseWidget
{
    public static function canView(): bool
    {
        try {
            $activeWidgets = app(\App\Settings\GeneralSettings::class)->active_widgets ?? [];
            return empty($activeWidgets) || in_array(class_basename(static::class), $activeWidgets);
        } catch (\Throwable $e) {
            return true;
        }
    }

    protected static ?int $sort = 3;
    protected static ?string $heading = 'Pergerakan Stok Terbaru';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(StockMovement::with(['component', 'requester', 'mold'])->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')->label('No. Ref')->weight('bold'),
                Tables\Columns\BadgeColumn::make('type')
                    ->formatStateUsing(fn($state) => match($state) { 'in' => 'Masuk', 'out' => 'Keluar', 'return' => 'Return', default => $state })
                    ->colors(['success' => 'in', 'danger' => 'out', 'warning' => 'return']),
                Tables\Columns\TextColumn::make('component.name')->label('Komponen')->limit(30),
                Tables\Columns\TextColumn::make('quantity')->label('Qty')->alignCenter(),
                Tables\Columns\TextColumn::make('requester.name')->label('Operator'),
                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn($state) => match($state) { 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', default => $state })
                    ->colors(['warning' => 'pending', 'success' => 'approved', 'danger' => 'rejected']),
                Tables\Columns\TextColumn::make('created_at')->label('Waktu')->dateTime('d/m H:i')->sortable(),
            ]);
    }
}
