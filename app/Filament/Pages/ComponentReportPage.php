<?php

namespace App\Filament\Pages;

use App\Models\Component;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ComponentReportPage extends Page implements HasTable
{
    use InteractsWithTable;
    use \Filament\Pages\Concerns\ExposesTableToWidgets;
    
    public ?string $activeTab = null;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static ?string $navigationLabel = 'Laporan Komponen';
    protected static ?string $title = 'Laporan Komponen';
    protected static ?string $navigationGroup = 'Laporan';

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Resources\Components\Tab::make('Semua Komponen'),
            'project' => \Filament\Resources\Components\Tab::make('Komponen Project')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('mold_id')),
            'common' => \Filament\Resources\Components\Tab::make('Komponen Common')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('mold_id')),
        ];
    }

    protected static string $view = 'filament.pages.component-report-page';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ComponentChartWidget::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Component::query()->with(['category', 'mold.project']))
            ->columns([
                TextColumn::make('code')->label('Kode Komponen')->searchable()->weight('bold'),
                TextColumn::make('name')->label('Nama Komponen')->searchable()->sortable(),
                TextColumn::make('category.name')->label('Bagian (Kategori)')->searchable()->sortable(),
                TextColumn::make('mold.mold_number')->label('Nomor Mold')->searchable()->sortable(),
                TextColumn::make('mold.project.name')->label('Project')->searchable()->sortable(),
                TextColumn::make('required_qty')
                    ->label('Kebutuhan')
                    ->alignCenter()
                    ->formatStateUsing(fn($record) => $record->mold_id ? $record->required_qty : '-')
                    ->color('warning')
                    ->weight('bold'),
                TextColumn::make('total_received')
                    ->label('Barang Masuk')
                    ->getStateUsing(fn($record) => $record->stock + $record->taken_qty)
                    ->alignCenter()
                    ->color('success')
                    ->weight('bold'),
                TextColumn::make('taken_qty')
                    ->label('Barang Dipakai')
                    ->getStateUsing(fn($record) => $record->taken_qty)
                    ->alignCenter()
                    ->color('info')
                    ->weight('bold'),
                TextColumn::make('stock')
                    ->label('Stok Sekarang')
                    ->alignCenter()
                    ->color('primary')
                    ->weight('bold'),
                TextColumn::make('not_ready')
                    ->label('Belum Datang')
                    ->getStateUsing(fn($record) => $record->mold_id ? max(0, $record->required_qty - ($record->stock + $record->taken_qty)) : '-')
                    ->alignCenter()
                    ->color(fn($state) => $state !== '-' && $state > 0 ? 'danger' : 'success')
                    ->weight('bold'),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function($record) {
                        if (!$record->mold_id || !$record->required_qty) return $record->status;
                        
                        $req = $record->required_qty;
                        $used = $record->taken_qty;
                        $currentStock = $record->stock;
                        $totalReceived = $currentStock + $used;

                        if ($used >= $req) return 'complete';
                        if ($used > 0) return 'proses_dipakai';
                        if ($totalReceived >= $req) return 'ready';
                        if ($totalReceived > 0) return 'on_progress';
                        return 'waiting';
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'complete' => 'Complete',
                        'proses_dipakai' => 'Proses Di Pakai',
                        'ready' => 'Ready',
                        'on_progress' => 'On Progress',
                        'waiting' => 'Waiting',
                        'in_use' => 'Sedang Dipakai',
                        'pending_arrival' => 'Belum Datang',
                        'maintenance' => 'Maintenance',
                        'retired' => 'Pensiun',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'success' => ['complete', 'ready'], 
                        'warning' => ['on_progress', 'in_use'], 
                        'info' => ['proses_dipakai', 'pending_arrival'], 
                        'danger' => ['waiting', 'maintenance'],
                        'gray' => ['retired'],
                    ]),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Filter Bagian')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('mold_id')
                    ->label('Filter Nomor Mold')
                    ->relationship('mold', 'mold_number')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('project_id')
                    ->label('Filter Project')
                    ->options(\App\Models\Project::pluck('name', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('mold', fn($q) => $q->where('project_id', $data['value']));
                        }
                    })
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'complete' => 'Complete',
                        'proses_dipakai' => 'Proses Dipakai',
                        'ready' => 'Ready',
                        'on_progress' => 'On Progress',
                        'waiting' => 'Waiting',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) return;
                        
                        $val = $data['value'];
                        $takenQty = '(SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE stock_movements.component_id = components.id AND stock_movements.type = "out" AND stock_movements.status = "approved")';
                        
                        if ($val === 'complete') {
                            $query->whereNotNull('mold_id')->whereRaw("($takenQty) >= required_qty");
                        } elseif ($val === 'proses_dipakai') {
                            $query->whereNotNull('mold_id')->whereRaw("($takenQty) > 0")->whereRaw("($takenQty) < required_qty");
                        } elseif ($val === 'ready') {
                            $query->whereNotNull('mold_id')->whereRaw("(stock + ($takenQty)) >= required_qty")->whereRaw("($takenQty) = 0");
                        } elseif ($val === 'on_progress') {
                            $query->whereNotNull('mold_id')->whereRaw("(stock + ($takenQty)) > 0")->whereRaw("(stock + ($takenQty)) < required_qty")->whereRaw("($takenQty) = 0");
                        } elseif ($val === 'waiting') {
                            $query->whereNotNull('mold_id')->whereRaw("(stock + ($takenQty)) = 0")->where('required_qty', '>', 0);
                        } else {
                            $query->where('status', $val)->where(function($q) {
                                $q->whereNull('mold_id')->orWhere('required_qty', 0);
                            });
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportAction::make()
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exports([
                        \pxlrbt\FilamentExcel\Exports\ExcelExport::make('table')->fromTable(),
                    ]),
            ]);
    }
}
