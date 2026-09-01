<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorResource\Pages;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Supplier';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\FileUpload::make('photo')
                    ->disk('cloudinary')
                    ->label('Foto / Logo')
                    ->image()
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('4:3')

                    ->directory('vendors')
                    ->nullable()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('code')->label('Kode')->required()->unique(ignoreRecord: true)->maxLength(20),
                Forms\Components\TextInput::make('name')->label('Nama Supplier')->required()->maxLength(255),
                Forms\Components\TextInput::make('pic_name')->label('Nama PIC')->nullable(),
                Forms\Components\TextInput::make('phone')->label('No. Telepon')->nullable(),
                Forms\Components\TextInput::make('email')->label('Email')->email()->nullable(),
                Forms\Components\TextInput::make('lead_time_days')->label('Lead Time (hari)')->numeric()->default(7),
                Forms\Components\Select::make('status')->label('Status')->options(['active' => 'Aktif', 'inactive' => 'Nonaktif'])->default('active')->required(),
                Forms\Components\TextInput::make('bank_name')->label('Bank')->nullable(),
                Forms\Components\TextInput::make('bank_account')->label('No. Rekening')->nullable(),
                Forms\Components\Textarea::make('address')->label('Alamat')->rows(2)->columnSpanFull(),
                Forms\Components\Textarea::make('notes')->label('Catatan')->rows(2)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ViewColumn::make('photo')->label('Foto')->view('filament.tables.columns.hover-image'),
            Tables\Columns\TextColumn::make('code')->label('Kode')->searchable()->sortable()->weight('bold'),
            Tables\Columns\TextColumn::make('name')->label('Nama Supplier')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('pic_name')->label('PIC')->searchable(),
            Tables\Columns\TextColumn::make('phone')->label('Telepon'),
            Tables\Columns\TextColumn::make('lead_time_days')->label('Lead Time')->suffix(' hari')->alignCenter(),
            Tables\Columns\BadgeColumn::make('status')->colors(['success' => 'active', 'danger' => 'inactive'])
                ->formatStateUsing(fn($state) => $state === 'active' ? 'Aktif' : 'Nonaktif'),
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
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
}
