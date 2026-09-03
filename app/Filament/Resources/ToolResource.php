<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ToolResource\Pages;
use App\Models\Tool;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ToolResource extends Resource
{
    protected static ?string $model = Tool::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Data Alat';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\FileUpload::make('photo')
                    ->disk('cloudinary')
                    ->label('Foto Alat')
                    ->image()
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('4:3')

                    ->directory('tools')
                    ->nullable()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('code')->label('Kode Alat')->required()->unique(ignoreRecord: true)->placeholder('TL-001'),
                Forms\Components\TextInput::make('name')->label('Nama Alat')->required(),
                Forms\Components\Select::make('type')->label('Tipe Alat')
                    ->options([
                        'Hand Tool' => 'Hand Tool',
                        'Power Tool' => 'Power Tool',
                        'Measuring Tool' => 'Measuring Tool',
                        'Cutting Tool' => 'Cutting Tool',
                        'Consumable' => 'Consumable',
                        'Other' => 'Lainnya',
                    ])
                    ->searchable()
                    ->nullable(),
                Forms\Components\TextInput::make('category')->label('Kategori')->placeholder('Hand Tools, Measuring, Power Tools')->nullable(),
                Forms\Components\TextInput::make('total_quantity')->label('Total Qty')->numeric()->required()->default(1),
                Forms\Components\TextInput::make('available_quantity')->label('Qty Tersedia')->numeric()->required()->default(1),
                Forms\Components\Select::make('condition')->label('Kondisi')
                    ->options(['good' => 'Baik', 'fair' => 'Cukup', 'poor' => 'Kurang Baik', 'damaged' => 'Rusak'])
                    ->default('good')->required(),
                Forms\Components\TextInput::make('location')->label('Lokasi Penyimpanan')->nullable(),
                Forms\Components\Textarea::make('description')->label('Deskripsi')->rows(2)->columnSpanFull()->nullable(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ViewColumn::make('photo')->label('Foto')->view('filament.tables.columns.hover-image'),
            Tables\Columns\TextColumn::make('code')->label('Kode')->searchable()->sortable()->weight('bold'),
            Tables\Columns\TextColumn::make('name')->label('Nama Alat')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('type')->label('Tipe Alat')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('category')->label('Kategori')->badge()->color('gray'),
            Tables\Columns\TextColumn::make('available_quantity')->label('Tersedia')->alignCenter()->weight('bold')
                ->color(fn($record) => $record->available_quantity === 0 ? 'danger' : 'success'),
            Tables\Columns\TextColumn::make('total_quantity')->label('Total')->alignCenter(),
            Tables\Columns\BadgeColumn::make('condition')
                ->formatStateUsing(fn($state) => match($state) { 'good' => 'Baik', 'fair' => 'Cukup', 'poor' => 'Kurang', 'damaged' => 'Rusak', default => $state })
                ->colors(['success' => 'good', 'warning' => 'fair', 'danger' => fn($state) => in_array($state, ['poor', 'damaged'])]),
            Tables\Columns\TextColumn::make('location')->label('Lokasi'),
        ])
        ->actions([
            Tables\Actions\Action::make('print_qr')
                ->label('QR')
                ->icon('heroicon-o-qr-code')
                ->color('success')
                ->url(fn (Tool $record): string => route('tools.qr', $record))
                ->openUrlInNewTab(),
            Tables\Actions\EditAction::make(), 
            Tables\Actions\DeleteAction::make()
        ])
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
            'index' => Pages\ListTools::route('/'),
            'create' => Pages\CreateTool::route('/create'),
            'edit' => Pages\EditTool::route('/{record}/edit'),
        ];
    }
}
