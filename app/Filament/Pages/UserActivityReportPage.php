<?php

namespace App\Filament\Pages;

use App\Models\AuditLog;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserActivityReportPage extends Page implements HasForms, HasTable
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;

    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Aktivitas User';
    protected static ?string $title = 'Laporan Aktivitas User';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.user-activity-report-page';

    public function table(Table $table): Table
    {
        return $table
            ->query(AuditLog::query())
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('activity_type')
                    ->label('Jenis Aktivitas')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'Pengambilan Barang' => 'danger',
                        'Pengembalian Barang' => 'success',
                        'Peminjaman Alat' => 'warning',
                        'Pengembalian Alat' => 'success',
                        'Laporan Improvement' => 'info',
                        'Laporan Abnormality' => 'danger',
                        'Permintaan Tool/Part' => 'primary',
                        default => 'gray',
                    })
                    ->state(function (AuditLog $record) {
                        $model = class_basename($record->model_type);
                        $action = $record->action;
                        
                        $new = is_array($record->new_values) ? $record->new_values : json_decode($record->new_values ?? '{}', true);

                        if ($model === 'StockMovement' && $action === 'created') {
                            return ($new['type'] ?? '') === 'in' ? 'Pengembalian Barang' : 'Pengambilan Barang';
                        }
                        if ($model === 'ToolLoan') {
                            if ($action === 'created') return 'Peminjaman Alat';
                            if ($action === 'updated' && ($new['status'] ?? '') === 'returned') return 'Pengembalian Alat';
                        }
                        if ($model === 'Improvement' && $action === 'created') return 'Laporan Improvement';
                        if ($model === 'Maintenance' && $action === 'created') {
                            $type = $new['type'] ?? '';
                            if ($type === 'abnormality') return 'Laporan Abnormality';
                            if ($type === 'improvement') return 'Laporan Improvement';
                        }
                        if ($model === 'PurchaseOrder' && $action === 'created') return 'Permintaan Tool/Part';
                        
                        return $record->description;
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Detail')
                    ->wrap()
                    ->state(function (AuditLog $record) {
                        $model = class_basename($record->model_type);
                        $action = $record->action;
                        $new = is_array($record->new_values) ? $record->new_values : json_decode($record->new_values ?? '{}', true);

                        if ($model === 'ToolLoan') {
                            $tool = \App\Models\Tool::find($new['tool_id'] ?? 0);
                            $toolName = $tool ? $tool->name : 'Alat (ID: ' . ($new['tool_id'] ?? '?') . ')';
                            $qty = $new['quantity'] ?? '-';
                            if ($action === 'created') return "Meminjam alat: {$toolName} (Qty: {$qty})";
                            if ($action === 'updated' && ($new['status'] ?? '') === 'returned') return "Mengembalikan alat: {$toolName}";
                        }
                        
                        if ($model === 'StockMovement' && $action === 'created') {
                            $comp = \App\Models\Component::find($new['component_id'] ?? 0);
                            $compName = $comp ? $comp->name : 'Komponen (ID: ' . ($new['component_id'] ?? '?') . ')';
                            $qty = $new['quantity'] ?? '-';
                            $type = ($new['type'] ?? '') === 'in' ? 'Mengembalikan' : 'Mengambil';
                            return "{$type} barang: {$compName} (Qty: {$qty})";
                        }

                        if ($model === 'PurchaseOrder' && $action === 'created') {
                            $po = \App\Models\PurchaseOrder::find($record->model_id);
                            if ($po) {
                                $items = \App\Models\PoItem::where('purchase_order_id', $po->id)->get();
                                if ($items->count() > 0) {
                                    $itemDetails = [];
                                    foreach ($items as $item) {
                                        $comp = \App\Models\Component::find($item->component_id);
                                        $name = $comp ? $comp->name : 'Part';
                                        $itemDetails[] = "{$name} (Qty: {$item->qty_ordered})";
                                    }
                                    return "Request part/tool: " . implode(', ', $itemDetails);
                                }
                            }
                            return "Request PO: " . ($new['po_number'] ?? '-');
                        }

                        if ($model === 'Maintenance' && $action === 'created') {
                            $machine = \App\Models\Machine::find($new['machine_id'] ?? 0);
                            $machineName = $machine ? $machine->name : 'Mesin (ID: ' . ($new['machine_id'] ?? '?') . ')';
                            $issue = $new['issue_description'] ?? '-';
                            $type = ($new['type'] ?? '') === 'abnormality' ? 'Abnormality' : 'Improvement';
                            return "Laporan {$type} di mesin {$machineName}: {$issue}";
                        }

                        if ($model === 'Improvement' && $action === 'created') {
                            $title = $new['title'] ?? '-';
                            return "Laporan Improvement: {$title}";
                        }

                        return $record->description;
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('activity_type')
                    ->label('Jenis Aktivitas')
                    ->options([
                        'Pengambilan Barang' => 'Pengambilan Barang',
                        'Pengembalian Barang' => 'Pengembalian Barang',
                        'Peminjaman Alat' => 'Peminjaman Alat',
                        'Pengembalian Alat' => 'Pengembalian Alat',
                        'Permintaan Tool/Part' => 'Permintaan Tool/Part',
                        'Laporan Improvement' => 'Laporan Improvement',
                        'Laporan Abnormality' => 'Laporan Abnormality',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        $value = $data['value'];
                        return $query->where(function ($q) use ($value) {
                            if ($value === 'Pengambilan Barang') {
                                $q->where('model_type', 'like', '%StockMovement')
                                  ->where('action', 'created')
                                  ->where('new_values', 'like', '%"type":"out"%');
                            } elseif ($value === 'Pengembalian Barang') {
                                $q->where('model_type', 'like', '%StockMovement')
                                  ->where('action', 'created')
                                  ->where('new_values', 'like', '%"type":"in"%');
                            } elseif ($value === 'Peminjaman Alat') {
                                $q->where('model_type', 'like', '%ToolLoan')
                                  ->where('action', 'created');
                            } elseif ($value === 'Pengembalian Alat') {
                                $q->where('model_type', 'like', '%ToolLoan')
                                  ->where('action', 'updated')
                                  ->where('new_values', 'like', '%"status":"returned"%');
                            } elseif ($value === 'Permintaan Tool/Part') {
                                $q->where('model_type', 'like', '%PurchaseOrder')
                                  ->where('action', 'created');
                            } elseif ($value === 'Laporan Improvement') {
                                $q->where(function ($sub) {
                                    $sub->where('model_type', 'like', '%Improvement')
                                        ->where('action', 'created');
                                })->orWhere(function ($sub) {
                                    $sub->where('model_type', 'like', '%Maintenance')
                                        ->where('action', 'created')
                                        ->where('new_values', 'like', '%"type":"improvement"%');
                                });
                            } elseif ($value === 'Laporan Abnormality') {
                                $q->where('model_type', 'like', '%Maintenance')
                                  ->where('action', 'created')
                                  ->where('new_values', 'like', '%"type":"abnormality"%');
                            }
                        });
                    }),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Filter User')
                    ->options(User::pluck('name', 'id'))
                    ->searchable(),
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
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada data aktivitas')
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
