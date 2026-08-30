<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use App\Models\SagyoNippo;
use App\Models\SagyoNippoItem;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class SagyoNippoEntryPage extends Page implements HasForms, HasTable
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;

    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Menu Operator';
    protected static ?string $navigationLabel = 'Sagyo Nippo';
    protected static ?string $title = 'Laporan Aktivitas Harian (Sagyo Nippo)';

    protected static string $view = 'filament.pages.sagyo-nippo-entry-page';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'user_id' => auth()->id(),
            'date' => now()->format('Y-m-d'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Buat Laporan Baru')
                    ->description('Masukkan rincian pekerjaan Anda di bawah ini.')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->options(\App\Models\User::pluck('name', 'id'))
                            ->label('Pekerja (Member)')
                            ->required()
                            ->searchable(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->required(),
                        Forms\Components\FileUpload::make('photo')
                            ->label('Bukti Foto / Laporan')
                            ->image()
                            ->directory('sagyo-nippo')
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('items')
                            ->label('Rincian Aktivitas')
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Tipe')
                                    ->options(\App\Models\SagyoType::pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),
                                Forms\Components\Select::make('project_id')
                                    ->options(\App\Models\Project::pluck('name', 'id'))
                                    ->label('Proyek')
                                    ->live()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('mold_id')
                                    ->options(function (Forms\Get $get) {
                                        $projectId = $get('project_id');
                                        $query = \App\Models\Mold::query();
                                        if ($projectId) {
                                            $query->where('project_id', $projectId);
                                        }
                                        return $query->pluck('name', 'id');
                                    })
                                    ->label('Mold')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('job_code_id')
                                    ->options(\App\Models\JobCode::all()->mapWithKeys(fn ($j) => [$j->id => "{$j->code} - {$j->item}"]))
                                    ->label('Job Code')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('part_code_id')
                                    ->options(\App\Models\PartCode::all()->mapWithKeys(fn ($p) => [$p->id => "{$p->code} - {$p->item}"]))
                                    ->label('Part Code')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\TextInput::make('hours')
                                    ->label('Jam')
                                    ->numeric()
                                    ->minValue(0.5)
                                    ->step(0.5)
                                    ->required(),
                                Forms\Components\Textarea::make('notes')
                                    ->label('Catatan')
                                    ->rows(1),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->columnSpanFull(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Simpan Laporan')
                ->submit('save')
                ->color('primary'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if (empty($data['items'])) {
            Notification::make()->title('Aktivitas tidak boleh kosong')->danger()->send();
            return;
        }

        $nippo = SagyoNippo::create([
            'user_id' => $data['user_id'],
            'date' => $data['date'],
            'photo' => $data['photo'] ?? null,
            'total_hours' => collect($data['items'])->sum('hours'),
        ]);

        foreach ($data['items'] as $item) {
            $nippo->items()->create([
                'type' => $item['type'] ?? null,
                'project_id' => $item['project_id'] ?? null,
                'mold_id' => $item['mold_id'],
                'job_code_id' => $item['job_code_id'],
                'part_code_id' => $item['part_code_id'],
                'hours' => $item['hours'],
                'notes' => $item['notes'] ?? null,
            ]);
        }

        Notification::make()->title('Laporan berhasil disimpan')->success()->send();

        // Reset form to default
        $this->form->fill([
            'user_id' => auth()->id(),
            'date' => now()->format('Y-m-d'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(SagyoNippoItem::query()->latest())
            ->columns([
                Tables\Columns\ImageColumn::make('sagyoNippo.photo')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-image.png'))
                    ->extraImgAttributes(['class' => 'zoomable-image']),
                Tables\Columns\TextColumn::make('sagyoNippo.date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sagyoNippo.user.name')
                    ->label('Member')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sagyoType.name')
                    ->label('Tipe')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Proyek')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('mold.name')
                    ->label('Mold')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jobCode.code')
                    ->label('Job Code')
                    ->badge()
                    ->description(fn ($record) => $record->jobCode->item)
                    ->searchable(),
                Tables\Columns\TextColumn::make('partCode.code')
                    ->label('Part Code')
                    ->badge()
                    ->description(fn ($record) => $record->partCode->item)
                    ->searchable(),
                Tables\Columns\TextColumn::make('hours')
                    ->label('Jam')
                    ->numeric(2)
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost')
                    ->label('Cost')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->hours * ($record->jobCode?->rate ?? 0))
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Nama Member')
                    ->options(\App\Models\User::pluck('name', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('sagyoNippo', fn($q) => $q->where('user_id', $data['value']));
                        }
                    })
                    ->searchable(),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options(\App\Models\SagyoType::pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Proyek')
                    ->options(\App\Models\Project::pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn ($query, $date) => $query->whereHas('sagyoNippo', fn($q) => $q->whereDate('date', '>=', $date)),
                            )
                            ->when(
                                $data['created_until'],
                                fn ($query, $date) => $query->whereHas('sagyoNippo', fn($q) => $q->whereDate('date', '<=', $date)),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\Select::make('type')
                            ->options(\App\Models\SagyoType::pluck('name', 'id'))
                            ->label('Tipe')
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('project_id')
                            ->options(\App\Models\Project::pluck('name', 'id'))
                            ->label('Proyek')
                            ->searchable(),
                        Forms\Components\Select::make('mold_id')
                            ->options(\App\Models\Mold::pluck('name', 'id'))
                            ->label('Mold')
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('job_code_id')
                            ->options(\App\Models\JobCode::all()->mapWithKeys(fn ($j) => [$j->id => "{$j->code} - {$j->item}"]))
                            ->label('Job Code')
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('part_code_id')
                            ->options(\App\Models\PartCode::all()->mapWithKeys(fn ($p) => [$p->id => "{$p->code} - {$p->item}"]))
                            ->label('Part Code')
                            ->required()
                            ->searchable(),
                        Forms\Components\TextInput::make('hours')
                            ->label('Jam')
                            ->numeric()
                            ->minValue(0.5)
                            ->step(0.5)
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2),
                    ]),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportAction::make()->exports([
                    \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                        ->withFilename('Sagyo_Nippo_' . date('Ymd'))
                        ->withColumns([
                            \pxlrbt\FilamentExcel\Columns\Column::make('sagyoNippo.date')->heading('Tanggal'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('sagyoNippo.user.name')->heading('Member'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('sagyoType.name')->heading('Tipe'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('project.name')->heading('Proyek'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('mold.name')->heading('Mold'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('jobCode.code')->heading('Job Code'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('partCode.code')->heading('Part Code'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('hours')->heading('Jam'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('cost')->heading('Cost'),
                        ]),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->color('primary')
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Resources\Components\Tab::make('Semua Laporan'),
            'project' => \Filament\Resources\Components\Tab::make('Project')
                ->modifyQueryUsing(fn ($query) => $query->whereNotNull('project_id')),
            'non_project' => \Filament\Resources\Components\Tab::make('Non-Project')
                ->modifyQueryUsing(fn ($query) => $query->whereNull('project_id')),
        ];
    }
}
