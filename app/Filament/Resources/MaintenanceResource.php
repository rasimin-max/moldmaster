<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceResource\Pages;
use App\Models\Machine;
use App\Models\Maintenance;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceResource extends Resource
{
    protected static ?string $model = Maintenance::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Maintenance Mesin';
    protected static ?string $modelLabel = 'Maintenance';
    protected static ?string $pluralModelLabel = 'Maintenance Mesin';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Laporan Kerusakan')->schema([
                Forms\Components\Select::make('machine_id')
                    ->label('Mesin')
                    ->options(Machine::pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('type')
                    ->label('Jenis Maintenance')
                    ->options([
                        'preventive' => 'Preventive (Rutin)',
                        'corrective' => 'Corrective',
                        'breakdown' => 'Breakdown',
                        'inspection' => 'Inspeksi',
                    ])
                    ->required()
                    ->default('breakdown'),
                Forms\Components\Select::make('priority')
                    ->label('Prioritas')
                    ->options([
                        'urgent' => 'Urgent 🔴',
                        'high' => 'High 🟠',
                        'medium' => 'Medium 🟡',
                        'low' => 'Low 🟢',
                    ])
                    ->required()
                    ->default('medium'),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu Approval',
                        'approved' => 'Disetujui',
                        'in_progress' => 'Sedang Dikerjakan',
                        'completed' => 'Selesai',
                        'rejected' => 'Ditolak',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->required()
                    ->default('pending'),
                Forms\Components\Textarea::make('problem_description')
                    ->label('Deskripsi Masalah / Kerusakan')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('photo')
                    ->disk('cloudinary')
                    ->label('Foto Kerusakan')
                    ->image()

                    ->directory('maintenances')
                    ->nullable(),
            ])->columns(2),

            Forms\Components\Section::make('Penanganan (diisi oleh Teknisi/Admin)')->schema([
                Forms\Components\Select::make('technician_id')
                    ->label('Teknisi')
                    ->options(User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'operator']))->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                Forms\Components\DateTimePicker::make('started_at')
                    ->label('Mulai Dikerjakan')
                    ->nullable(),
                Forms\Components\DateTimePicker::make('completed_at')
                    ->label('Selesai')
                    ->nullable(),
                Forms\Components\TextInput::make('downtime_hours')
                    ->label('Downtime (Jam)')
                    ->numeric()
                    ->nullable(),
                Forms\Components\TextInput::make('labor_cost')
                    ->label('Biaya Tenaga Kerja (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\Textarea::make('action_taken')
                    ->label('Tindakan yang Dilakukan')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2)
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Sparepart Digunakan')->schema([
                Forms\Components\Repeater::make('spareParts')
                    ->relationship('spareParts')
                    ->schema([
                        Forms\Components\TextInput::make('part_name')->label('Nama Part')->required()->columnSpan(2),
                        Forms\Components\TextInput::make('part_number')->label('Part Number')->nullable(),
                        Forms\Components\TextInput::make('quantity')->label('Qty')->numeric()->required()->default(1),
                        Forms\Components\TextInput::make('unit')->label('Satuan')->default('pcs'),
                        Forms\Components\TextInput::make('unit_price')->label('Harga/unit')->numeric()->prefix('Rp')->default(0),
                        Forms\Components\TextInput::make('vendor')->label('Vendor')->nullable(),
                    ])
                    ->columns(7)
                    ->addActionLabel('+ Tambah Sparepart')
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('work_order_number')
                    ->label('No. WO')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('machine.name')
                    ->label('Mesin')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Jenis')
                    ->formatStateUsing(fn($state) => match($state) {
                        'preventive' => 'Preventive',
                        'corrective' => 'Corrective',
                        'breakdown' => 'Breakdown',
                        'inspection' => 'Inspeksi',
                        default => ucfirst($state),
                    })
                    ->colors(['success' => 'preventive', 'info' => 'corrective', 'danger' => 'breakdown', 'warning' => 'inspection']),
                Tables\Columns\BadgeColumn::make('priority')
                    ->label('Prioritas')
                    ->formatStateUsing(fn($state) => strtoupper($state))
                    ->colors(['danger' => 'urgent', 'warning' => 'high', 'info' => 'medium', 'gray' => 'low']),
                Tables\Columns\TextColumn::make('problem_description')
                    ->label('Masalah')
                    ->limit(40)
                    ->wrap(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'in_progress' => 'Proses',
                        'completed' => 'Selesai',
                        'rejected' => 'Ditolak',
                        default => ucfirst($state),
                    })
                    ->colors(['warning' => 'pending', 'info' => 'approved', 'primary' => 'in_progress', 'success' => 'completed', 'danger' => 'rejected']),
                Tables\Columns\TextColumn::make('downtime_hours')
                    ->label('Downtime (jam)')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('reported_at')
                    ->label('Dilaporkan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'in_progress' => 'Proses', 'completed' => 'Selesai']),
                Tables\Filters\SelectFilter::make('priority')
                    ->options(['urgent' => 'Urgent', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low']),
                Tables\Filters\SelectFilter::make('machine_id')
                    ->label('Mesin')
                    ->options(Machine::pluck('name', 'id')),
                Tables\Filters\Filter::make('breakdown_active')
                    ->label('Breakdown Aktif')
                    ->query(fn(Builder $q) => $q->where('type', 'breakdown')->whereIn('status', ['pending', 'approved', 'in_progress'])),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn(Maintenance $r) => $r->status === 'pending')
                    ->form([
                        Forms\Components\Select::make('priority')
                            ->label('Set Prioritas')
                            ->options(['urgent' => 'Urgent', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low'])
                            ->required(),
                    ])
                    ->action(function (Maintenance $record, array $data) {
                        $record->update([
                            'status' => 'approved',
                            'priority' => $data['priority'],
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                        Notification::make()->title('Maintenance disetujui!')->success()->send();
                    }),
                Tables\Actions\Action::make('start')
                    ->label('Mulai')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->visible(fn(Maintenance $r) => $r->status === 'approved')
                    ->action(function (Maintenance $record) {
                        $record->update(['status' => 'in_progress', 'started_at' => now()]);
                        Notification::make()->title('Maintenance dimulai!')->info()->send();
                    }),
                Tables\Actions\Action::make('complete')
                    ->label('Selesai')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn(Maintenance $r) => $r->status === 'in_progress')
                    ->form([
                        Forms\Components\Textarea::make('action_taken')->label('Tindakan yang Dilakukan')->required()->rows(3),
                        Forms\Components\TextInput::make('downtime_hours')->label('Total Downtime (Jam)')->numeric()->required(),
                    ])
                    ->action(function (Maintenance $record, array $data) {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                            'action_taken' => $data['action_taken'],
                            'downtime_hours' => $data['downtime_hours'],
                        ]);
                        Notification::make()->title('Maintenance selesai!')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('reported_at', 'desc');
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenances::route('/'),
            'create' => Pages\CreateMaintenance::route('/create'),
            'edit' => Pages\EditMaintenance::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string { return 'danger'; }
}
