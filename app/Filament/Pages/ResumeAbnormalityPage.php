<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use App\Models\Maintenance;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;

class ResumeAbnormalityPage extends Page implements HasTable
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $title = 'Resume Laporan Abnormality';
    protected static ?string $navigationLabel = 'Resume Abnormality';
    protected static ?string $slug = 'resume-abnormality';
    protected static ?int $navigationSort = 12;

    protected static string $view = 'filament.pages.resume-abnormality-page';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'super_admin', 'leader', 'superadmin']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Maintenance::query()->with(['machine', 'reporter', 'approver', 'technician', 'verifier']))
            ->columns([
                Tables\Columns\TextColumn::make('work_order_number')
                    ->label('ID Laporan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('reporter.name')
                    ->label('Pelapor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular()
                    ->disk('cloudinary')
                    ->extraImgAttributes(['class' => 'zoomable-image']),
                Tables\Columns\TextColumn::make('machine.name')
                    ->label('Mesin / Asset')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'breakdown' => 'danger',
                        'preventive' => 'info',
                        'predictive' => 'warning',
                        'corrective' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn ($record) => $record->priority_badge_color ?? 'gray'),
                Tables\Columns\TextColumn::make('problem_description')
                    ->label('Masalah')
                    ->limit(40)
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record) => $record->status_badge_color ?? 'gray'),
                Tables\Columns\TextColumn::make('technician.name')
                    ->label('PIC Penanganan')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('target_due_date')
                    ->label('Target Selesai')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Lapor')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'in_progress' => 'Proses',
                        'completed' => 'Selesai',
                        'rejected' => 'Ditolak',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'urgent' => 'Urgent',
                        'high' => 'High',
                        'medium' => 'Medium',
                        'low' => 'Low',
                    ]),
                Tables\Filters\SelectFilter::make('machine_id')
                    ->relationship('machine', 'name')
                    ->label('Mesin')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->headerActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportAction::make()
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exports([
                        \pxlrbt\FilamentExcel\Exports\ExcelExport::make('table')->fromTable(),
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('assign_pic')
                        ->label('Assign PIC & Target')
                        ->icon('heroicon-o-user-plus')
                        ->color('info')
                        ->visible(fn (Maintenance $record) => in_array($record->status, ['pending', 'approved']))
                        ->form([
                            Forms\Components\Select::make('technician_id')
                                ->label('Pilih Teknisi / PIC')
                                ->relationship('technician', 'name')
                                ->searchable()
                                ->required(),
                            Forms\Components\DateTimePicker::make('target_due_date')
                                ->label('Target Selesai / ETA')
                                ->required(),
                        ])
                        ->action(function (array $data, Maintenance $record): void {
                            $record->update([
                                'technician_id' => $data['technician_id'],
                                'target_due_date' => $data['target_due_date'],
                                'status' => 'in_progress',
                                'started_at' => now(),
                            ]);
                            Notification::make()->title('PIC Assigned & In Progress')->success()->send();
                        }),

                    Tables\Actions\Action::make('countermeasure')
                        ->label('Isi Countermeasure')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->color('primary')
                        ->visible(fn (Maintenance $record) => $record->status === 'in_progress' && (auth()->user()->id === $record->technician_id || auth()->user()->hasAnyRole(['admin', 'super_admin', 'leader'])))
                        ->form([
                            Forms\Components\Section::make('Action Plan')
                                ->schema([
                                    Forms\Components\Textarea::make('temporary_action')
                                        ->label('Temporary Action (Tindakan Darurat)')
                                        ->required(),
                                    Forms\Components\Textarea::make('replaced_parts_note')
                                        ->label('Sparepart Diganti (Catatan)')
                                        ->nullable(),
                                ]),
                            Forms\Components\Section::make('Root Cause Analysis (RCA)')
                                ->schema([
                                    Forms\Components\TextInput::make('rca_man')->label('Man (Manusia)')->nullable(),
                                    Forms\Components\TextInput::make('rca_machine')->label('Machine (Mesin)')->nullable(),
                                    Forms\Components\TextInput::make('rca_material')->label('Material')->nullable(),
                                    Forms\Components\TextInput::make('rca_method')->label('Method (Metode)')->nullable(),
                                ])->columns(2),
                            Forms\Components\Section::make('Pencegahan')
                                ->schema([
                                    Forms\Components\Textarea::make('permanent_countermeasure')
                                        ->label('Permanent Countermeasure (Pencegahan Berulang)')
                                        ->required(),
                                    Forms\Components\FileUpload::make('photo_after')
                                        ->label('Foto Setelah Perbaikan (After)')
                                        ->image()
                                        ->disk('cloudinary')
                                        ->directory('abnormalities/after'),
                                ]),
                        ])
                        ->action(function (array $data, Maintenance $record): void {
                            $record->update(array_merge($data, [
                                'status' => 'need_verification',
                                'completed_at' => now(),
                            ]));
                            // Calculate downtime
                            if ($record->reported_at && $record->completed_at) {
                                $record->update([
                                    'downtime_hours' => $record->reported_at->diffInMinutes($record->completed_at) / 60
                                ]);
                            }
                            Notification::make()->title('Countermeasure Disimpan, Menunggu Verifikasi')->success()->send();
                        }),

                    Tables\Actions\Action::make('verify')
                        ->label('Verifikasi & Close')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn (Maintenance $record) => $record->status === 'need_verification' && auth()->user()->hasAnyRole(['admin', 'super_admin', 'leader']))
                        ->form([
                            Forms\Components\Textarea::make('supervisor_notes')
                                ->label('Catatan Pengawas (Opsional)')
                                ->nullable(),
                            Forms\Components\Select::make('verification_action')
                                ->label('Tindakan')
                                ->options([
                                    'approve' => 'Setujui & Close',
                                    'reject' => 'Tolak (Revisi ke In Progress)',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, Maintenance $record): void {
                            if ($data['verification_action'] === 'approve') {
                                $record->update([
                                    'status' => 'closed',
                                    'verified_by' => auth()->id(),
                                    'verified_at' => now(),
                                    'notes' => $data['supervisor_notes'],
                                ]);
                                Notification::make()->title('Laporan Closed')->success()->send();
                            } else {
                                $record->update([
                                    'status' => 'in_progress',
                                    'notes' => $data['supervisor_notes'],
                                    'completed_at' => null, // Reset completion
                                ]);
                                Notification::make()->title('Dikembalikan ke PIC')->warning()->send();
                            }
                        }),
                        
                    Tables\Actions\Action::make('tracking')
                        ->label('Detail / Tracking')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->modalHeading('Histori & Detail Penanganan')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup')
                        ->modalContent(fn (Maintenance $record) => view('filament.components.abnormality-tracking', ['record' => $record])),
                ])
            ])
            ->defaultSort('created_at', 'desc');
    }
}
