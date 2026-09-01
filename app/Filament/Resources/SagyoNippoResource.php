<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SagyoNippoResource\Pages;
use App\Models\SagyoNippo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SagyoNippoResource extends Resource
{
    protected static ?string $model = SagyoNippo::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Menu Operator';
    protected static ?string $navigationLabel = 'Sagyo Nippo';
    protected static ?string $modelLabel = 'Laporan Harian (Sagyo Nippo)';
    protected static ?string $pluralModelLabel = 'Laporan Harian (Sagyo Nippo)';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Laporan Harian')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Pekerja (Member)')
                            ->default(auth()->id())
                            ->required()
                            ->searchable(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->default(now())
                            ->required(),
                        Forms\Components\FileUpload::make('photo')
                    ->disk('cloudinary')
                            ->label('Bukti Foto / Laporan')
                            ->image()
                            ->directory('sagyo-nippo')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Rincian Aktivitas')
                    ->description('Tambahkan semua pekerjaan yang Anda lakukan pada hari tersebut (misal hingga 8 jam).')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->label('')
                            ->schema([
                                Forms\Components\Select::make('project_id')
                                    ->relationship('project', 'name')
                                    ->label('Proyek (Project)')
                                    ->live()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('mold_id')
                                    ->relationship('mold', 'name', function (Builder $query, Forms\Get $get) {
                                        $projectId = $get('project_id');
                                        if ($projectId) {
                                            $query->where('project_id', $projectId);
                                        }
                                    })
                                    ->label('Mold')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('job_code_id')
                                    ->relationship('jobCode', 'item')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} - {$record->item}")
                                    ->label('Job Code')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('part_code_id')
                                    ->relationship('partCode', 'item')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} - {$record->item}")
                                    ->label('Part Code')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\TextInput::make('hours')
                                    ->label('Durasi Kerja (Jam)')
                                    ->numeric()
                                    ->minValue(0.5)
                                    ->step(0.5)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state, $livewire) {
                                        // This triggers update if we wanted to show live total, but let's just let it be.
                                    }),
                                Forms\Components\Textarea::make('notes')
                                    ->label('Catatan')
                                    ->rows(1),
                            ])
                            ->columns(3)
                            ->addActionLabel('+ Tambah Aktivitas Lainnya')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['hours'] ?? null ? "Aktivitas ({$state['hours']} Jam)" : null)
                            ->defaultItems(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-image.png')) // Use a default if empty
                    ->extraImgAttributes(['class' => 'zoomable-image']),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Member')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Jml Aktivitas')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('items.hours')
                    ->label('Total Jam')
                    ->getStateUsing(fn (SagyoNippo $record) => $record->items->sum('hours') . ' Jam')
                    ->badge()
                    ->color(fn ($state) => ((float) $state >= 8) ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn ($query, $date) => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn ($query, $date) => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    \pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSagyoNippos::route('/'),
            'create' => Pages\CreateSagyoNippo::route('/create'),
            'edit' => Pages\EditSagyoNippo::route('/{record}/edit'),
        ];
    }
}
