<?php

namespace App\Filament\Pages;

use App\Models\Improvement;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ImprovementReportPage extends Page implements HasTable
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;
    use InteractsWithTable;
    use \Filament\Pages\Concerns\ExposesTableToWidgets;
    
    public ?string $activeTab = null;
    
    // use \BezhanSalleh\FilamentShield\Traits\HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Improvement';
    protected static ?string $title = 'Laporan Improvement & Cost Effect';
    protected static ?string $navigationGroup = 'Laporan';

    protected static string $view = 'filament.pages.improvement-report-page';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ImprovementChartWidget::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Improvement::query())
            ->columns([
                TextColumn::make('created_at')->label('Tgl Info')->dateTime()->sortable(),
                TextColumn::make('title')->label('Judul Improvement')->searchable(),
                TextColumn::make('reporter_name')->label('Nama Member')->searchable(),
                TextColumn::make('cost_effect')->label('Cost Effect (Rp)')->money('idr')->sortable(),
                TextColumn::make('implementation_date')->label('Tgl Pelaksanaan')->date()->sortable(),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('reporter_name')
                    ->label('Nama Member')
                    ->options(fn () => Improvement::pluck('reporter_name', 'reporter_name')->unique()->toArray()),
                Filter::make('implemented')
                    ->label('Sudah Diimplementasi')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('implementation_date')),
            ])
            ->defaultSort('cost_effect', 'desc')
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
