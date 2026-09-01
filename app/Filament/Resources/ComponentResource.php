<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComponentResource\Pages;
use App\Models\Component;
use App\Models\ComponentCategory;
use App\Models\MachiningType;
use App\Models\MaterialType;
use App\Models\Mold;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ComponentResource extends Resource
{
    protected static ?string $model = Component::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Komponen';
    protected static ?string $modelLabel = 'Komponen';
    protected static ?string $pluralModelLabel = 'Komponen';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                // MAIN COLUMN
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Informasi Utama')->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Komponen')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Komponen')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(30)
                            ->placeholder('COMP-INS-001'),
                        Forms\Components\Select::make('category_id')
                            ->label('Bagian')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                            ]),
                    ])->columns(2),

                    Forms\Components\Section::make('Spesifikasi & Detail')->schema([
                        Forms\Components\Select::make('material_type_id')
                            ->label('Material')
                            ->relationship('materialType', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                            ]),
                        Forms\Components\Select::make('machining_type_id')
                            ->label('Machining')
                            ->relationship('machiningType', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                            ]),
                        Forms\Components\TextInput::make('material')
                            ->label('Detail Material')
                            ->placeholder('NAK80, SKD61, S136...'),
                        Forms\Components\TextInput::make('size_spec')
                            ->label('Spesifikasi Ukuran')
                            ->placeholder('250x180x120mm'),
                    ])->columns(2),

                    Forms\Components\Section::make('Media & Catatan')->schema([
                        Forms\Components\FileUpload::make('photo')
                    ->disk('cloudinary')
                            ->label('Foto Komponen')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('4:3')

                            ->directory('components')
                            ->nullable(),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi / Catatan')
                            ->rows(4),
                    ])->columns(2),
                ])->columnSpan(['lg' => 2]),

                // SIDEBAR COLUMN
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Status & Lokasi')->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'ready' => 'Ready',
                                'in_use' => 'Dipakai',
                                'pending_arrival' => 'Belum Datang',
                                'maintenance' => 'Maintenance',
                                'retired' => 'Pensiunkan',
                            ])
                            ->required()
                            ->default('ready'),
                        Forms\Components\TextInput::make('rack_location')
                            ->label('Lokasi Rak')
                            ->placeholder('R01-A-01'),
                    ]),
                    
                    Forms\Components\Section::make('Relasi')->schema([
                        Forms\Components\Select::make('project_id')
                            ->label('Project Terkait')
                            ->options(\App\Models\Project::pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($record) => $record?->mold?->project_id),
                        Forms\Components\Select::make('mold_id')
                            ->label('Nomor Mold')
                            ->options(function (Forms\Get $get) {
                                $projectId = $get('project_id');
                                $query = \App\Models\Mold::query();
                                
                                if ($projectId) {
                                    $query->where('project_id', $projectId);
                                }
                                
                                return $query->with('project')->get()->mapWithKeys(function ($mold) {
                                    $identifier = $mold->mold_number ? $mold->mold_number : $mold->code;
                                    $projectName = $mold->project ? $mold->project->name : '-';
                                    return [$mold->id => "{$projectName} | {$identifier}"];
                                });
                            })
                            ->searchable()
                            ->nullable(),
                        Forms\Components\Select::make('vendor_id')
                            ->label('Supplier')
                            ->options(Vendor::where('status', 'active')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                    ]),

                    Forms\Components\Section::make('Stok & Harga')->schema([
                        Forms\Components\TextInput::make('required_qty')
                            ->label('Quantity Yang Dibutuhkan')
                            ->numeric()
                            ->default(0)
                            ->visible(fn (Forms\Get $get) => $get('mold_id') !== null),
                        Forms\Components\TextInput::make('stock')
                            ->label(fn (Forms\Get $get) => $get('mold_id') !== null ? 'Quantity Sekarang' : 'Stok Saat Ini')
                            ->numeric()
                            ->required()
                            ->default(0),
                        Forms\Components\Placeholder::make('taken_qty_display')
                            ->label('Quantity Yang Sudah Dipakai')
                            ->content(fn (?Component $record) => $record ? $record->taken_qty : 0)
                            ->visible(fn (Forms\Get $get) => $get('mold_id') !== null),
                        Forms\Components\TextInput::make('stock_minimum')
                            ->label('Stok Minimum')
                            ->numeric()
                            ->required()
                            ->default(5)
                            ->hidden(fn (Forms\Get $get) => $get('mold_id') !== null),
                        Forms\Components\TextInput::make('unit')
                            ->label('Satuan')
                            ->default('pcs')
                            ->required(),
                        Forms\Components\TextInput::make('unit_price')
                            ->label('Harga Satuan (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                    ])->columns(2),

                    Forms\Components\Section::make('Tracking Shot')->schema([
                        Forms\Components\TextInput::make('shot_count')
                            ->label('Jumlah Shot')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('shot_life')
                            ->label('Maks Life')
                            ->numeric()
                            ->nullable(),
                    ])->columns(2)->collapsed(),
                ])->columnSpan(['lg' => 1]),
            ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['stockMovements' => function($q) {
                $q->where('type', 'out')->where('status', 'approved');
            }]))
            ->columns([
                Tables\Columns\ViewColumn::make('photo')
                    ->label('Foto')
                    ->view('filament.tables.columns.hover-image'),
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode / QR')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Komponen')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('size_spec')
                    ->label('Spesifikasi / Ukuran')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('category.name')
                    ->label('Bagian')
                    ->sortable(),
                Tables\Columns\TextColumn::make('materialType.name')
                    ->label('Material')
                    ->sortable(),
                Tables\Columns\TextColumn::make('machiningType.name')
                    ->label('Machining')
                    ->sortable(),
                Tables\Columns\TextColumn::make('mold_project')
                    ->label('Nama Project')
                    ->getStateUsing(fn($record) => $record->mold?->project?->name ?? $record->mold?->project_name ?? '-')
                    ->searchable(query: fn (Builder $query, string $search): Builder => 
                        $query->whereHas('mold', fn ($q) => $q->where('project_name', 'like', "%{$search}%")->orWhereHas('project', fn ($q2) => $q2->where('name', 'like', "%{$search}%")))
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('mold_identifier')
                    ->label('Nomor Mold')
                    ->getStateUsing(fn($record) => $record->mold?->mold_number ?? $record->mold?->code ?? '-')
                    ->searchable(query: fn (Builder $query, string $search): Builder => 
                        $query->whereHas('mold', fn ($q) => $q->where('mold_number', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                    )
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('mold.name')
                    ->label('Nama Mold')
                    ->searchable()
                    ->sortable()
                    ->default('-'),
                Tables\Columns\TextColumn::make('rack_location')
                    ->label('Lokasi Rak')
                    ->badge()
                    ->color('indigo'),
                Tables\Columns\TextColumn::make('required_qty')
                    ->label('Kebutuhan')
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(fn($record) => $record->mold_id ? $record->required_qty : '-')
                    ->color('warning')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('total_received')
                    ->label('Barang Masuk')
                    ->getStateUsing(fn($record) => $record->stock + $record->taken_qty)
                    ->alignCenter()
                    ->color('success')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('taken_qty')
                    ->label('Barang Dipakai')
                    ->getStateUsing(fn($record) => $record->taken_qty)
                    ->alignCenter()
                    ->color('info')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok Sekarang')
                    ->sortable()
                    ->alignCenter()
                    ->color(fn($record) => $record->mold_id ? 'primary' : ($record->is_low_stock ? 'danger' : 'success'))
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('not_ready')
                    ->label('Belum Datang')
                    ->getStateUsing(fn($record) => $record->mold_id ? max(0, $record->required_qty - ($record->stock + $record->taken_qty)) : '-')
                    ->alignCenter()
                    ->color(fn($state) => $state !== '-' && $state > 0 ? 'danger' : 'success')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label('Harga/Pcs')
                    ->money('idr', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_used')
                    ->label('Total Terpakai')
                    ->getStateUsing(fn($record) => $record->taken_qty * $record->unit_price)
                    ->money('idr', locale: 'id')
                    ->sortable(false)
                    ->color('danger')
                    ->weight('bold'),
                Tables\Columns\BadgeColumn::make('status')
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
                        'in_use' => 'Dipakai',
                        'pending_arrival' => 'Belum Datang',
                        'maintenance' => 'Maintenance',
                        'retired' => 'Pensiunkan',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'success' => ['complete', 'ready'], 
                        'warning' => ['on_progress', 'in_use'], 
                        'info' => ['proses_dipakai', 'pending_arrival'], 
                        'danger' => ['waiting', 'maintenance'],
                        'gray' => ['retired'],
                    ]),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Update')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
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
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Bagian')
                    ->options(ComponentCategory::pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('material_type_id')
                    ->attribute('material_type_id')
                    ->label('Tipe Material')
                    ->options(MaterialType::pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('machining_type_id')
                    ->attribute('machining_type_id')
                    ->label('Tipe Machining')
                    ->options(MachiningType::pluck('name', 'id')),
                Tables\Filters\SelectFilter::make('mold_id')
                    ->label('Mold')
                    ->options(Mold::pluck('name', 'id')),
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stok Menipis')
                    ->query(fn(Builder $query) => $query->whereColumn('stock', '<=', 'stock_minimum')),
            ])
            ->actions([
                Tables\Actions\Action::make('qr')
                    ->label('QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('indigo')
                    ->url(fn(Component $record) => route('components.qr', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('print_qr')
                        ->label('Print Barcode (QR)')
                        ->icon('heroicon-o-printer')
                        ->color('primary')
                        ->modalSubmitActionLabel('Print Sekarang')
                        ->form(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $schema = [];
                            foreach ($records as $record) {
                                $schema[] = Forms\Components\TextInput::make('copies_' . $record->id)
                                    ->label('Print ' . $record->code . ' (' . $record->name . ')')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(100);
                            }
                            return $schema;
                        })
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data) {
                            $idsWithCopies = [];
                            foreach ($records as $record) {
                                $idsWithCopies[$record->id] = $data['copies_' . $record->id] ?? 1;
                            }
                            return redirect()->route('components.qr.bulk', ['data' => urlencode(json_encode($idsWithCopies))]);
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('code');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComponents::route('/'),
            'create' => Pages\CreateComponent::route('/create'),
            'edit' => Pages\EditComponent::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereColumn('stock', '<=', 'stock_minimum')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
