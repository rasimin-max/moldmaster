<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MachineProgramResource\Pages;
use App\Filament\Resources\MachineProgramResource\RelationManagers;
use App\Models\MachineProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MachineProgramResource extends Resource
{
    protected static ?string $model = MachineProgram::class;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $modelLabel = 'Nama Program';
    protected static ?string $pluralModelLabel = 'Nama Program';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Header Info')
                    ->schema([
                        Forms\Components\Select::make('project_id')
                            ->relationship('project', 'name')
                            ->label('Project')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('mold_id')
                            ->relationship('mold', 'name')
                            ->label('Mold Name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('component_id')
                            ->relationship('component', 'name')
                            ->label('Comp. Name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('machine_id')
                            ->relationship('machine', 'name')
                            ->label('Machine')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('programmer')
                            ->label('Programmer')
                            ->maxLength(255),
                    ])->columns(3),
                Forms\Components\Section::make('Program Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Program Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('r_f')
                            ->label('R/F')
                            ->maxLength(10),
                        Forms\Components\TextInput::make('b')
                            ->label('B')
                            ->maxLength(10),
                        Forms\Components\TextInput::make('barcode')
                            ->label('BARCODE')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('estimated_time')
                            ->label('Process Time Plan')
                            ->helperText('Jam/Menit, contoh: 0,06 atau 3.25'),
                        Forms\Components\TextInput::make('actual_time')
                            ->label('Process Time Actual')
                            ->helperText('Waktu aktual'),
                    ])->columns(3),
                Forms\Components\Section::make('Tool Information')
                    ->schema([
                        Forms\Components\TextInput::make('tool_no')
                            ->label('TOOL NO')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('tool_name')
                            ->label('Tool Name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tool_dia')
                            ->label('Dia.'),
                        Forms\Components\TextInput::make('tool_r')
                            ->label('R.'),
                        Forms\Components\TextInput::make('tool_length_total')
                            ->label('Length Total'),
                        Forms\Components\TextInput::make('tool_length_eff')
                            ->label('Length Eff.'),
                        Forms\Components\TextInput::make('tool_num')
                            ->label('Num')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('holder')
                            ->label('Holder')
                            ->maxLength(255),
                    ])->columns(4),
                Forms\Components\Section::make('Cutting Condition')
                    ->schema([
                        Forms\Components\TextInput::make('ps_thick')
                            ->label('PS Thick'),
                        Forms\Components\TextInput::make('rpm')
                            ->label('Rpm'),
                        Forms\Components\TextInput::make('feed')
                            ->label('Feed'),
                        Forms\Components\TextInput::make('doc')
                            ->label('DoC'),
                        Forms\Components\TextInput::make('setting')
                            ->label('Setting')
                            ->maxLength(255),
                    ])->columns(3),
                Forms\Components\Section::make('Other')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi Tambahan')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Program Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('mold.name')
                    ->label('Mold Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('component.name')
                    ->label('Comp. Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('machine.name')
                    ->label('Machine')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('programmer')
                    ->label('Programmer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('r_f')
                    ->label('R/F')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('b')
                    ->label('B')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tool_no')
                    ->label('Tool No')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tool_name')
                    ->label('Tool Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tool_dia')
                    ->label('Dia.')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tool_r')
                    ->label('R.')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tool_length_total')
                    ->label('Len. Total')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tool_length_eff')
                    ->label('Len. Eff')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tool_num')
                    ->label('Num')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('holder')
                    ->label('Holder')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ps_thick')
                    ->label('PS Thick')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('rpm')
                    ->label('Rpm')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('feed')
                    ->label('Feed')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('doc')
                    ->label('DoC')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('setting')
                    ->label('Setting')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('estimated_time')
                    ->label('Plan')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('actual_time')
                    ->label('Actual')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('barcode')
                    ->label('Barcode')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->relationship('project', 'name')
                    ->label('Project')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('mold_id')
                    ->relationship('mold', 'name')
                    ->label('Mold Name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('machine_id')
                    ->relationship('machine', 'name')
                    ->label('Machine')
                    ->searchable()
                    ->preload(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMachinePrograms::route('/'),
            'create' => Pages\CreateMachineProgram::route('/create'),
            'edit' => Pages\EditMachineProgram::route('/{record}/edit'),
        ];
    }
}
