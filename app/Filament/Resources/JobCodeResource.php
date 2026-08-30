<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobCodeResource\Pages;
use App\Models\JobCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JobCodeResource extends Resource
{
    protected static ?string $model = JobCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Job Code';
    protected static ?string $modelLabel = 'Job Code';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('type')
                    ->label('Tipe (Misal: A. CAD / DESIGN)')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('item')
                    ->label('Item Pekerjaan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->label('Kode Job (Misal: A-1)')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('rate')
                    ->label('Rate (Cost per Jam)')
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('item')
                    ->label('Item Pekerjaan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('rate')
                    ->label('Rate/Jam')
                    ->money('IDR')
                    ->sortable(),
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
            'index' => Pages\ListJobCodes::route('/'),
        ];
    }
}
