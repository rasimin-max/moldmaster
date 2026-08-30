<?php

namespace App\Filament\Widgets;

use App\Models\Maintenance;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestAbnormalitiesTable extends BaseWidget
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

    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Maintenance::query()
                    ->whereNotNull('reported_by') // Only reports with a reporter
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('reported_at')
                    ->label('Tgl Lapor')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('machine.name')
                    ->label('Mesin/Asset'),
                Tables\Columns\TextColumn::make('problem_description')
                    ->label('Masalah')
                    ->limit(50),
                Tables\Columns\TextColumn::make('reporter.name')
                    ->label('Pelapor'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('process')
                    ->label('Selesai / Close')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Maintenance $record) => $record->update(['status' => 'completed', 'approved_by' => auth()->id()]))
                    ->visible(fn (Maintenance $record) => in_array($record->status, ['pending', 'reported', 'in_progress'])),
            ])
            ->heading('Laporan Abnormality Terbaru');
    }
}
