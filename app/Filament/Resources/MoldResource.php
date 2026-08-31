<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MoldResource\Pages;
use App\Models\Mold;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MoldResource extends Resource
{
    protected static ?string $model = Mold::class;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Data Mold';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                // MAIN COLUMN
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Informasi Utama')->schema([
                        Forms\Components\FileUpload::make('photo')
                            ->label('Foto Mold')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('4:3')

                            ->directory('molds')
                            ->nullable()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Mold')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('project_id')
                            ->label('Project Terkait')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->nullable(),
                        Forms\Components\TextInput::make('project_name')
                            ->label('Nama Proyek (Opsional/Manual)')
                            ->nullable(),
                        Forms\Components\TextInput::make('customer')
                            ->label('Customer')
                            ->nullable(),
                        Forms\Components\TextInput::make('product_type')
                            ->label('Jenis Produk')
                            ->placeholder('Bumper, Grille, dll')
                            ->nullable(),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull()
                            ->nullable(),
                    ])->columns(2),
                ])->columnSpan(['lg' => 2]),

                // SIDEBAR COLUMN
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Identifikasi & Status')->schema([
                        Forms\Components\TextInput::make('mold_number')
                            ->label('Nomor Mold')
                            ->placeholder('425')
                            ->nullable(),
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Mold')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('MOL-2024-001'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif', 
                                'maintenance' => 'Maintenance', 
                                'inactive' => 'Nonaktif', 
                                'retired' => 'Pensiunkan'
                            ])
                            ->default('active')
                            ->required(),
                    ]),
                    
                    Forms\Components\Section::make('Spesifikasi & Shot')->schema([
                        Forms\Components\TextInput::make('cavity')
                            ->label('Cavity')
                            ->numeric()
                            ->default(1)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('shot_life')
                            ->label('Target Shot Life')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('current_shot')
                            ->label('Shot Saat Ini')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
                ])->columnSpan(['lg' => 1]),
            ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ViewColumn::make('photo')->label('Foto')->view('filament.tables.columns.hover-image'),
            Tables\Columns\TextColumn::make('mold_number')->label('Nomor Mold')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('code')->label('Kode')->searchable()->sortable()->weight('bold'),
            Tables\Columns\TextColumn::make('name')->label('Nama Mold')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('customer')->label('Customer')->searchable(),
            Tables\Columns\TextColumn::make('product_type')->label('Produk')->badge()->color('indigo'),
            Tables\Columns\TextColumn::make('current_shot')->label('Shot')->formatStateUsing(fn($state) => number_format($state))->alignCenter(),
            Tables\Columns\TextColumn::make('shot_life')->label('Target')->formatStateUsing(fn($state) => $state ? number_format($state) : '-')->alignCenter(),
            Tables\Columns\BadgeColumn::make('status')
                ->formatStateUsing(fn($state) => match($state) { 'active' => 'Aktif', 'maintenance' => 'Maintenance', 'inactive' => 'Nonaktif', 'retired' => 'Pensiunkan', default => $state })
                ->colors(['success' => 'active', 'warning' => 'maintenance', 'gray' => fn($state) => in_array($state, ['inactive', 'retired'])]),
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
        ->defaultSort('code');
    }

    public static function getRelations(): array { return []; }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMolds::route('/'),
            'create' => Pages\CreateMold::route('/create'),
            'edit' => Pages\EditMold::route('/{record}/edit'),
        ];
    }
}
