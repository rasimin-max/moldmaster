<?php

namespace App\Filament\Widgets;

use App\Models\Improvement;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestImprovementsTable extends BaseWidget
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

    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Improvement::query()
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Lapor')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Improvement'),
                Tables\Columns\TextColumn::make('reporter.name')
                    ->label('Oleh'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('process')
                    ->label('Selesai / Close')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Improvement $record) => $record->update(['status' => 'implemented']))
                    ->visible(fn (Improvement $record) => in_array($record->status, ['submitted', 'in_progress', 'pending'])),
            ])
            ->heading('Ide Improvement Terbaru');
    }
}
