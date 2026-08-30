<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutboundToolResource\Pages;
use App\Models\Tool;
use App\Models\ToolMovement;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OutboundToolResource extends Resource
{
    protected static ?string $model = \App\Models\ToolMovement::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Barang Keluar Alat';
    protected static ?string $modelLabel = 'Barang Keluar Alat';
    protected static ?string $pluralModelLabel = 'Barang Keluar Alat';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Detail Transaksi')->schema([
                Forms\Components\Select::make('type')
                    ->label('Jenis Transaksi')
                    ->options([
                        'in' => 'Barang Masuk',
                        'out' => 'Barang Keluar',
                        'return' => 'Return',
                        'adjustment' => 'Penyesuaian Stok',
                    ])
                    ->required()
                    ->live(),
                Forms\Components\TextInput::make('scan_barcode')
                    ->label('Scan Barcode Alat')
                    ->suffixAction(
                        \CCK\FilamentQrcodeScannerHtml5\BarcodeScannerAction::make()
                    )
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if (blank($state)) return;
                        $component = \App\Models\Tool::where('code', $state)->orWhere('qr_code', $state)->first();
                        if ($component) {
                            $set('tool_id', $component->id);
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Alat tidak ditemukan')
                                ->danger()
                                ->send();
                        }
                    })
                    ->helperText('Gunakan scanner fisik atau klik icon kamera'),
                Forms\Components\Select::make('tool_id')
                    ->label('Alat')
                    ->options(\App\Models\Tool::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->reactive(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->rule(function (Forms\Get $get) {
                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                            if ($get('type') !== 'out') return;
                            
                            $toolId = $get('tool_id');
                            if (!$toolId) return;
                            
                            $tool = \App\Models\Tool::find($toolId);
                            if ($tool && $value > $tool->stock) {
                                $fail("Stok alat habis atau tidak mencukupi (Sisa stok: {$tool->stock}).");
                            }
                        };
                    }),
                Forms\Components\TextInput::make('operator_name')
                    ->label('Nama Operator')
                    ->default(fn () => auth()->user()?->name)
                    ->nullable(),
                Forms\Components\TextInput::make('purpose')
                    ->label('Keperluan / Keterangan')
                    ->nullable()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2)
                    ->nullable()
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('No. Referensi')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Jenis')
                    ->formatStateUsing(fn($state) => match($state) {
                        'in' => 'Masuk',
                        'out' => 'Keluar',
                        'return' => 'Return',
                        'adjustment' => 'Penyesuaian',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'success' => 'in',
                        'danger' => 'out',
                        'warning' => 'return',
                        'info' => 'adjustment',
                    ]),
                Tables\Columns\TextColumn::make('tool.name')
                    ->label('Alat')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('tool.category')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Diminta Oleh')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'gray' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'in' => 'Barang Masuk',
                        'out' => 'Barang Keluar',
                        'return' => 'Return',
                        'adjustment' => 'Penyesuaian',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu Approval',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
                Tables\Filters\Filter::make('pending_approval')
                    ->label('Menunggu Approval')
                    ->query(fn(Builder $query) => $query->where('status', 'pending')),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn(ToolMovement $r) => $r->status === 'pending')
                    ->action(function (ToolMovement $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                        Notification::make()->title('Transaksi disetujui!')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn(ToolMovement $r) => $r->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (ToolMovement $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'approved_by' => auth()->id(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        Notification::make()->title('Transaksi ditolak!')->danger()->send();
                    }),
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array { return []; }

    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'out');
    }
    


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOutboundTools::route('/'),
            'create' => Pages\CreateOutboundTool::route('/create'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string { return 'warning'; }
}
