<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use App\Models\MachineOperationRecord;
use Illuminate\Database\Eloquent\Builder;

class ResumeMachineOperationRecordPage extends Page implements HasTable
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;
    use \Filament\Pages\Concerns\ExposesTableToWidgets;
    use InteractsWithTable;

    public ?string $activeTab = null;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $title = 'Resume Machine Operation Record';
    protected static ?string $navigationLabel = 'Resume Machine Operation Record';
    protected static ?string $slug = 'resume-machine-operation-record';
    protected static ?int $navigationSort = 11;

    protected static string $view = 'filament.pages.resume-machine-operation-record-page';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\FilteredMachineOperationTimeChart::class,
            \App\Filament\Widgets\FilteredMachineOperationCostChart::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MachineOperationRecord::query()->with(['machine', 'project', 'mold', 'component', 'machineProgram', 'user']))
            ->columns([
                Tables\Columns\TextColumn::make('machine.name')
                    ->label('Nama Mesin')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Nama Project')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('mold.name')
                    ->label('Nama Mold')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('component.name')
                    ->label('Nama Komponen')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('machineProgram.name')
                    ->label('Nama Program')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Operator')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('operation_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'production' => 'success',
                        'setup' => 'warning',
                        'trial' => 'info',
                        'maintenance' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('start_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('planned_duration_minutes')
                    ->label('Plan Waktu (Hrs)')
                    ->formatStateUsing(fn ($state) => $state ? number_format((float)$state, 2) . ' Hrs' : '-')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Aktual Proses')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 60, 2) . ' Hrs' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('machine.hourly_rate')
                    ->label('Rate Mesin/Jam')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('cost')
                    ->label('Total Biaya')
                    ->state(function (MachineOperationRecord $record) {
                        if (!$record->duration_minutes || !$record->machine || !$record->machine->hourly_rate) return 0;
                        return ($record->duration_minutes / 60) * $record->machine->hourly_rate;
                    })
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'plan_job' => 'info',
                        'running' => 'success',
                        'completed' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->relationship('project', 'name')
                    ->label('Project')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('mold_id')
                    ->relationship('mold', 'name')
                    ->label('Nama Mold')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('machine_id')
                    ->relationship('machine', 'name')
                    ->label('Mesin')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('end_date')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['start_date'] ?? null,
                                fn ($query, $date) => $query->whereDate('start_time', '>=', $date),
                            )
                            ->when(
                                $data['end_date'] ?? null,
                                fn ($query, $date) => $query->whereDate('start_time', '<=', $date),
                            );
                    }),
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
