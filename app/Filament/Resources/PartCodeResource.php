<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartCodeResource\Pages;
use App\Models\PartCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PartCodeResource extends Resource
{
    protected static ?string $model = PartCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Part Code';
    protected static ?string $modelLabel = 'Part Code';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('item')
                    ->label('Nama Part (Misal: ANGULAR BLOCK)')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->label('Kode Part (Misal: 01-P)')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item')
                    ->label('Nama Part')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartCodes::route('/'),
        ];
    }
}
