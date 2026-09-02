<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

class SagyoNippoUnfilledTable extends BaseWidget
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

    protected static ?string $heading = 'Daftar Member Belum Isi (Hari Ini)';
    protected static ?int $sort = 4;
    
    public function table(Table $table): Table
    {
        $today = Carbon::today();
        
        return $table
            ->query(
                User::query()->whereDoesntHave('sagyoNippos', function (Builder $query) use ($today) {
                    $query->whereDate('date', $today);
                })->where('is_active', true) // assuming we only care about active users
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Member')
                    ->searchable(),
                Tables\Columns\TextColumn::make('area')
                    ->label('Area')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color('danger')
                    ->default('Belum Mengisi'),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}
