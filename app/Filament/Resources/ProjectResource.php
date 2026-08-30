<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Project';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Kode Project')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Project')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('customer')
                    ->label('Customer')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Tanggal Mulai'),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Tanggal Selesai'),
                Forms\Components\TextInput::make('budget')
                    ->label('Budget (Rp)')
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'on_hold' => 'On Hold',
                    ])
                    ->default('active')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('budget')
                    ->label('Budget')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'gray',
                        'on_hold' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MoldsRelationManager::class,
            RelationManagers\PhasesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
