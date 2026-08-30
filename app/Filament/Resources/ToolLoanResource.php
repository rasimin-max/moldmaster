<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ToolLoanResource\Pages;
use App\Models\Tool;
use App\Models\ToolLoan;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ToolLoanResource extends Resource
{
    protected static ?string $model = ToolLoan::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Peminjaman Alat';
    protected static ?string $modelLabel = 'Peminjaman Alat';
    protected static ?string $pluralModelLabel = 'Peminjaman Alat';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Detail Peminjaman')->schema([
                Forms\Components\TextInput::make('scan_barcode')
                    ->label('Scan Barcode Alat')
                    ->suffixAction(
                        \CCK\FilamentQrcodeScannerHtml5\BarcodeScannerAction::make()
                    )
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if (blank($state)) return;
                        $tool = \App\Models\Tool::where('code', $state)->first();
                        if ($tool) {
                            $set('tool_id', $tool->id);
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
                    ->options(Tool::all()->mapWithKeys(fn($t) => [$t->id => "{$t->name} (Tersedia: {$t->available_quantity})"]))
                    ->required()
                    ->searchable()
                    ->reactive(),
                Forms\Components\Select::make('borrower_id')
                    ->label('Peminjam')
                    ->options(User::where('is_active', true)->pluck('name', 'id'))
                    ->default(auth()->id())
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->required()
                    ->default(1)
                    ->minValue(1),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu Approval',
                        'approved' => 'Disetujui',
                        'borrowed' => 'Dipinjam',
                        'returned' => 'Dikembalikan',
                        'rejected' => 'Ditolak',
                        'overdue' => 'Terlambat',
                    ])
                    ->required()
                    ->default('pending'),
                Forms\Components\TextInput::make('purpose')
                    ->label('Keperluan')
                    ->nullable()
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('planned_return_date')
                    ->label('Rencana Tanggal Kembali')
                    ->nullable(),
                Forms\Components\Select::make('condition_borrowed')
                    ->label('Kondisi Saat Pinjam')
                    ->options(['good' => 'Baik', 'fair' => 'Cukup', 'poor' => 'Kurang Baik'])
                    ->nullable(),
                Forms\Components\Select::make('condition_returned')
                    ->label('Kondisi Saat Kembali')
                    ->options(['good' => 'Baik', 'fair' => 'Cukup', 'poor' => 'Kurang Baik', 'damaged' => 'Rusak'])
                    ->nullable(),
                Forms\Components\Textarea::make('notes')->label('Catatan')->rows(2)->nullable()->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('loan_number')->label('No. Pinjam')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('tool.name')->label('Alat')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('borrower.name')->label('Peminjam')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('quantity')->label('Qty')->alignCenter(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending' => 'Menunggu', 'approved' => 'Disetujui', 'borrowed' => 'Dipinjam',
                        'returned' => 'Dikembalikan', 'rejected' => 'Ditolak', 'overdue' => 'Terlambat', default => ucfirst($state),
                    })
                    ->colors(['warning' => 'pending', 'info' => 'approved', 'primary' => 'borrowed', 'success' => 'returned', 'danger' => fn($state) => in_array($state, ['rejected', 'overdue'])]),
                Tables\Columns\TextColumn::make('planned_return_date')->label('Rencana Kembali')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('returned_at')->label('Tgl Kembali')->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Menunggu', 'borrowed' => 'Dipinjam', 'returned' => 'Dikembalikan', 'overdue' => 'Terlambat']),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(ToolLoan $r) => $r->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (ToolLoan $record) {
                        $record->update(['status' => 'approved', 'approved_by' => auth()->id()]);
                        Notification::make()->title('Peminjaman disetujui!')->success()->send();
                    }),
                Tables\Actions\Action::make('handover')
                    ->label('Serahkan')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('info')
                    ->visible(fn(ToolLoan $r) => $r->status === 'approved')
                    ->action(function (ToolLoan $record) {
                        $record->update(['status' => 'borrowed', 'borrowed_at' => now(), 'condition_borrowed' => 'good']);
                        Notification::make()->title('Alat diserahkan!')->info()->send();
                    }),
                Tables\Actions\Action::make('return')
                    ->label('Kembalikan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn(ToolLoan $r) => in_array($r->status, ['borrowed', 'overdue']))
                    ->form([
                        Forms\Components\Select::make('condition_returned')
                            ->label('Kondisi Alat Dikembalikan')
                            ->options(['good' => 'Baik', 'fair' => 'Cukup', 'poor' => 'Kurang Baik', 'damaged' => 'Rusak'])
                            ->required(),
                        Forms\Components\Textarea::make('notes')->label('Catatan')->rows(2),
                    ])
                    ->action(function (ToolLoan $record, array $data) {
                        $record->update([
                            'status' => 'returned',
                            'returned_at' => now(),
                            'condition_returned' => $data['condition_returned'],
                            'notes' => $data['notes'] ?? $record->notes,
                        ]);
                        Notification::make()->title('Alat berhasil dikembalikan!')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListToolLoans::route('/'),
            'create' => Pages\CreateToolLoan::route('/create'),
            'edit' => Pages\EditToolLoan::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string { return 'warning'; }
}
