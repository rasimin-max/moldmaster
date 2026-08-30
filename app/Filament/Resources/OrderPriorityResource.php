<?php

namespace App\Filament\Resources;

use App\Filament\Exports\OrderPriorityExporter;
use App\Filament\Resources\OrderPriorityResource\Pages;
use App\Models\OrderPriority;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderPriorityResource extends Resource
{
    protected static ?string $model = OrderPriority::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Prioritas Order';
    protected static ?string $modelLabel = 'Prioritas Order';
    protected static ?string $pluralModelLabel = 'Prioritas Order';
    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool { return false; }
    
    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_code')
                    ->label('Kode Item')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('item_name')
                    ->label('Nama Item')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'component' => 'Part Common',
                        'tool' => 'Tool',
                        'project_component' => 'Komponen Project',
                        default => $state,
                    })
                    ->color(fn (OrderPriority $record) => $record->badge_color)
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan Prioritas')
                    ->wrap(),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Stok Saat Ini')
                    ->numeric()
                    ->alignRight(),
                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Min Stok/Safety')
                    ->numeric()
                    ->alignRight(),
                Tables\Columns\TextColumn::make('order_qty')
                    ->label('Kekurangan (Qty Order)')
                    ->numeric()
                    ->weight('bold')
                    ->color('danger')
                    ->alignRight(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Filter Jenis')
                    ->options([
                        'component' => 'Part Common',
                        'tool' => 'Alat (Tool)',
                        'project_component' => 'Komponen Project',
                    ]),
            ])
            ->headerActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportAction::make()
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exports([
                        \pxlrbt\FilamentExcel\Exports\ExcelExport::make('table')->fromTable(),
                    ]),
            ])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('order_qty', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageOrderPriorities::route('/'),
        ];
    }
}
