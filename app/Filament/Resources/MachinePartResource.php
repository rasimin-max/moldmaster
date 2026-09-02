<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MachinePartResource\Pages;
use App\Models\MachinePart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MachinePartResource extends Resource
{
    protected static ?string $model = MachinePart::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationGroup = 'Master Data'; // Putting it in Master Data instead of Operator Menu, or maybe under Machine settings
    protected static ?string $modelLabel = 'Machine Part (Spare Part)';
    protected static ?string $pluralModelLabel = 'Machine Parts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('machine_id')
                    ->relationship('machine', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('part_number')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('machine-parts')
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('1920')
                    ->imageResizeTargetHeight('1920')
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('installed_at')
                    ->default(now()),
                Forms\Components\TextInput::make('expected_life_hours')
                    ->numeric()
                    ->label('Expected Life (Hours)'),
                Forms\Components\TextInput::make('expected_life_cycles')
                    ->numeric()
                    ->label('Expected Life (Cycles)'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Currently Installed')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('machine.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('part_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('installed_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expected_life_hours')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expected_life_cycles')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_hours')
                    ->label('Usage (Hrs)')
                    ->getStateUsing(function (MachinePart $record) {
                        if (!$record->machine) return 0;
                        // Calculate usage based on operations since installed_at
                        $start = $record->installed_at ? $record->installed_at->startOfDay() : null;
                        
                        $query = $record->machine->operationRecords()
                            ->where('status', 'completed');
                            
                        if ($start) {
                            $query->where('start_time', '>=', $start);
                        }
                        
                        $minutes = $query->sum('duration_minutes');
                        return round($minutes / 60, 2);
                    }),
                Tables\Columns\TextColumn::make('life_status_hours')
                    ->label('Life % (Hrs)')
                    ->getStateUsing(function (MachinePart $record) {
                        if (!$record->expected_life_hours || !$record->machine) return 'N/A';
                        
                        $start = $record->installed_at ? $record->installed_at->startOfDay() : null;
                        $query = $record->machine->operationRecords()->where('status', 'completed');
                        if ($start) $query->where('start_time', '>=', $start);
                        
                        $hoursUsed = $query->sum('duration_minutes') / 60;
                        $percent = ($hoursUsed / $record->expected_life_hours) * 100;
                        
                        return round($percent, 1) . '%';
                    })
                    ->badge()
                    ->color(function ($state) {
                        if ($state === 'N/A') return 'gray';
                        $val = (float) str_replace('%', '', $state);
                        if ($val > 90) return 'danger';
                        if ($val > 75) return 'warning';
                        return 'success';
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('machine_id')
                    ->relationship('machine', 'name')
                    ->label('Machine'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Is Installed'),
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
            'index' => Pages\ListMachineParts::route('/'),
            'create' => Pages\CreateMachinePart::route('/create'),
            'edit' => Pages\EditMachinePart::route('/{record}/edit'),
        ];
    }
}
