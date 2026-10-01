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
            ->query(Maintenance::query()->with(['machine', 'reporter', 'approver']))
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
            ->defaultSort('created_at', 'desc');
    }
}
