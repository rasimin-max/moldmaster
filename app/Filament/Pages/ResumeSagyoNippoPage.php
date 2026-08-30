<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use App\Models\SagyoNippoItem;
use Illuminate\Database\Eloquent\Builder;

class ResumeSagyoNippoPage extends Page implements HasTable
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;
    use \Filament\Pages\Concerns\ExposesTableToWidgets;
    use InteractsWithTable;

    public ?string $activeTab = null;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Resume Sagyo Nippo';
    protected static ?string $title = 'Resume Sagyo Nippo';
    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.resume-sagyo-nippo-page';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\SagyoNippoStats::class,
            \App\Filament\Widgets\SagyoNippoChartWidget::class,
            \App\Filament\Widgets\SagyoNippoComplianceChart::class,
            \App\Filament\Widgets\SagyoNippoUnfilledTable::class,
        ];
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
                                $data['created_from'] ?? null,
                                fn ($query, $date) => $query->whereHas('sagyoNippo', fn($q) => $q->whereDate('date', '>=', $date)),
                            )
                            ->when(
                                $data['created_until'] ?? null,
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
                        ->withFilename('Resume_Sagyo_Nippo_' . date('Ymd'))
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
}
