<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PhasesRelationManager extends RelationManager
{
    protected static string $relationship = 'phases';

    protected static ?string $title = 'Fase Proyek';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('name')
                    ->label('Nama Fase')
                    ->options([
                        'Design' => 'Design',
                        'Machining' => 'Machining',
                        'Assembly' => 'Assembly',
                        'Trial' => 'Trial',
                        'QC' => 'QC',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->required(),
                Forms\Components\TextInput::make('estimated_hours')
                    ->label('Estimasi Jam')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Forms\Components\TextInput::make('progress')
                    ->label('Progress (%)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Aktif',
                        'completed' => 'Selesai',
                    ])
                    ->default('pending')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Fase')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estimated_hours')
                    ->label('Estimasi (Jam)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'active',
                        'success' => 'completed',
                    ]),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
