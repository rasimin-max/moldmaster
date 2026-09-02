<?php

namespace App\Filament\Pages;

use App\Models\ToolLoan;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ToolLoanReportPage extends Page implements HasTable
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;
    use InteractsWithTable;
    use \Filament\Pages\Concerns\ExposesTableToWidgets;
    
    public ?string $activeTab = null;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static ?string $navigationLabel = 'Laporan Peminjaman Alat';
    protected static ?string $title = 'Laporan Peminjaman Alat';
    protected static ?string $navigationGroup = 'Laporan';

    protected static string $view = 'filament.pages.tool-loan-report-page';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ToolLoanChartWidget::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(ToolLoan::query()->with(['borrower', 'tool']))
            ->columns([
                TextColumn::make('created_at')->label('Tgl Pinjam')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('loan_number')->label('No. Pinjam')->searchable()->weight('bold'),
                TextColumn::make('borrower.name')->label('Nama Peminjam')->searchable()->sortable(),
                TextColumn::make('tool.name')->label('Alat Dipinjam')->searchable()->sortable(),
                TextColumn::make('quantity')->label('Jumlah')->alignCenter(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending' => 'Menunggu', 'approved' => 'Disetujui', 'borrowed' => 'Sedang Dipinjam',
                        'returned' => 'Dikembalikan', 'rejected' => 'Ditolak', 'overdue' => 'Terlambat', default => ucfirst($state),
                    })
                    ->colors([
                        'warning' => 'pending', 
                        'info' => 'approved', 
                        'primary' => 'borrowed', 
                        'success' => 'returned', 
                        'danger' => fn($state) => in_array($state, ['rejected', 'overdue'])
                    ]),
            ])
            ->filters([
                SelectFilter::make('borrower_id')
                    ->label('Filter Peminjam')
                    ->relationship('borrower', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'borrowed' => 'Sedang Dipinjam',
                        'returned' => 'Dikembalikan',
                        'overdue' => 'Terlambat'
                    ]),
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
