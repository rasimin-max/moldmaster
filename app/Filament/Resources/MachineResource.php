<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MachineResource\Pages;
use App\Models\Machine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MachineResource extends Resource
{
    protected static ?string $model = Machine::class;
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Data Mesin';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\FileUpload::make('photo')
                    ->label('Foto Mesin')
                    ->image()
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('4:3')
                    ->disk('public')
                    ->directory('machines')
                    ->nullable()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('code')->label('Kode Mesin')->required()->unique(ignoreRecord: true)->placeholder('CNC-001'),
                Forms\Components\TextInput::make('name')->label('Nama Mesin')->required(),
                Forms\Components\Select::make('type')->label('Jenis Mesin')
                    ->options(['CNC' => 'CNC', 'EDM' => 'EDM', 'Wirecut' => 'Wire Cut', 'Grinding' => 'Grinding', 'Milling' => 'Milling', 'Lathe' => 'Lathe', 'Drilling' => 'Drilling', 'Polishing' => 'Polishing', 'Assembly' => 'Assembly', 'Laser' => 'Laser'])
                    ->required(),
                Forms\Components\TextInput::make('brand')->label('Merk/Brand')->nullable(),
                Forms\Components\TextInput::make('model_number')->label('Model Number')->nullable(),
                Forms\Components\TextInput::make('serial_number')->label('Serial Number')->nullable(),
                Forms\Components\TextInput::make('area')->label('Area/Lokasi')->nullable(),
                Forms\Components\TextInput::make('year_purchased')->label('Tahun Beli')->numeric()->nullable(),
                Forms\Components\Select::make('status')->label('Status')
                    ->options(['operational' => 'Operasional', 'maintenance' => 'Maintenance', 'breakdown' => 'Breakdown', 'idle' => 'Idle', 'retired' => 'Pensiun'])
                    ->default('operational')->required(),
                Forms\Components\TextInput::make('hourly_rate')->label('Biaya/Jam (Rp)')->numeric()->prefix('Rp')->default(0),
                Forms\Components\Textarea::make('notes')->label('Catatan')->rows(2)->columnSpanFull()->nullable(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ViewColumn::make('photo')->label('Foto')->view('filament.tables.columns.hover-image'),
            Tables\Columns\TextColumn::make('code')->label('Kode')->searchable()->sortable()->weight('bold'),
            Tables\Columns\TextColumn::make('name')->label('Nama Mesin')->searchable()->sortable(),
            Tables\Columns\BadgeColumn::make('type')->colors(['primary' => 'CNC', 'warning' => 'EDM', 'info' => 'Wirecut', 'success' => 'Grinding']),
            Tables\Columns\TextColumn::make('brand')->label('Merk'),
            Tables\Columns\TextColumn::make('area')->label('Area')->badge()->color('indigo'),
            Tables\Columns\BadgeColumn::make('status')
                ->formatStateUsing(fn($state) => match($state) { 'operational' => 'Operasional', 'maintenance' => 'Maintenance', 'breakdown' => 'Breakdown', 'idle' => 'Idle', 'retired' => 'Pensiun', default => $state })
                ->colors(['success' => 'operational', 'warning' => 'maintenance', 'danger' => 'breakdown', 'info' => 'idle', 'gray' => 'retired']),
            Tables\Columns\TextColumn::make('total_operation_hours')
                ->label('Total Hrs')
                ->numeric(),
            Tables\Columns\TextColumn::make('total_operation_cycles')
                ->label('Total Cycles')
                ->numeric()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction::make()
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success'),
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('type')->options(['CNC' => 'CNC', 'EDM' => 'EDM', 'Wirecut' => 'Wire Cut', 'Grinding' => 'Grinding']),
            Tables\Filters\SelectFilter::make('status')->options(['operational' => 'Operasional', 'maintenance' => 'Maintenance', 'breakdown' => 'Breakdown']),
        ])
        ->defaultSort('code');
    }

    public static function getRelations(): array { return []; }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMachines::route('/'),
            'create' => Pages\CreateMachine::route('/create'),
            'edit' => Pages\EditMachine::route('/{record}/edit'),
        ];
    }
}
