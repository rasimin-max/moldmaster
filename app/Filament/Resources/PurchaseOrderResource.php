<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Purchase Order';
    protected static ?string $modelLabel = 'Purchase Order';
    protected static ?string $pluralModelLabel = 'Purchase Orders';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Header PO')->schema([
                Forms\Components\Select::make('vendor_id')
                    ->label('Supplier')
                    ->options(Vendor::where('status', 'active')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Terkirim ke Supplier',
                        'ordered' => 'Sedang Diproses',
                        'partial' => 'Sebagian Diterima',
                        'arrived' => 'Sudah Tiba',
                        'closed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->required()
                    ->default('draft'),
                Forms\Components\DatePicker::make('po_date')
                    ->label('Tanggal PO')
                    ->required()
                    ->default(now()),
                Forms\Components\DatePicker::make('expected_arrival_date')
                    ->label('Estimasi Tiba')
                    ->nullable(),
                Forms\Components\DatePicker::make('actual_arrival_date')
                    ->label('Tanggal Tiba Aktual')
                    ->nullable(),
                Forms\Components\TextInput::make('payment_terms')
                    ->label('Syarat Pembayaran')
                    ->placeholder('Net 30, COD, dll'),
                Forms\Components\TextInput::make('currency')
                    ->label('Mata Uang')
                    ->default('IDR'),
                Forms\Components\TextInput::make('invoice_number')
                    ->label('No. Invoice Supplier')
                    ->nullable(),
            ])->columns(2),

            Forms\Components\Section::make('Item PO')->schema([
                Forms\Components\Repeater::make('items')
                    ->relationship('items')
                    ->schema([
                        Forms\Components\Select::make('component_id')
                            ->label('Komponen')
                            ->options(\App\Models\Component::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->columnSpan(3),
                        Forms\Components\TextInput::make('qty_ordered')
                            ->label('Qty Order')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Forms\Components\TextInput::make('qty_received')
                            ->label('Qty Diterima')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('unit_price')
                            ->label('Harga Satuan')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        Forms\Components\TextInput::make('unit')
                            ->label('Satuan')
                            ->default('pcs'),
                    ])
                    ->columns(7)
                    ->addActionLabel('+ Tambah Item')
                    ->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Dokumen')->schema([
                Forms\Components\FileUpload::make('invoice_file')
                    ->label('File Invoice')

                    ->directory('purchase-orders/invoices')
                    ->nullable(),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3)
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('po_number')
                    ->label('No. PO')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => match($state) {
                        'draft' => 'Draft',
                        'sent' => 'Terkirim',
                        'ordered' => 'Diproses',
                        'partial' => 'Sebagian',
                        'arrived' => 'Tiba',
                        'closed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'sent',
                        'warning' => fn($state) => in_array($state, ['ordered', 'partial']),
                        'success' => fn($state) => in_array($state, ['arrived', 'closed']),
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('po_date')
                    ->label('Tanggal PO')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expected_arrival_date')
                    ->label('Estimasi Tiba')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Terkirim',
                        'ordered' => 'Diproses',
                        'arrived' => 'Tiba',
                        'closed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\SelectFilter::make('vendor_id')
                    ->label('Supplier')
                    ->options(Vendor::pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->label('Cetak PDF')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn(PurchaseOrder $record) => route('purchase-orders.pdf', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
