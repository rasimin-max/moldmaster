<?php

namespace App\Filament\Pages;

use App\Models\Machine;
use App\Models\Maintenance;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use Filament\Tables\Table;

class ReportAbnormalityPage extends Page implements HasForms, HasTable
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;

    use InteractsWithForms, InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel = 'Lapor Abnormality';
    protected static ?string $title = 'Lapor Abnormality Mesin / Part';
    protected static ?string $navigationGroup = 'Menu Operator';
    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.report-abnormality-page';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('reporter_name')
                    ->label('Nama Pelapor')
                    ->default(fn () => auth()->user()?->name)
                    ->disabled(),
                Select::make('machine_id')
                    ->label('Pilih Mesin / Asset')
                    ->options(Machine::pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                TextInput::make('problem_title')
                    ->label('Judul Masalah')
                    ->required(),
                Textarea::make('description')
                    ->label('Deskripsi Kerusakan / Kendala')
                    ->required()
                    ->rows(4),
                \Filament\Forms\Components\FileUpload::make('photo')
                    ->label('Foto Abnormality (Opsional)')
                    ->image()
                    ->directory('maintenances'),
                DatePicker::make('maintenance_date')
                    ->label('Tanggal Kejadian / Laporan')
                    ->default(now())
                    ->required(),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Kirim Laporan')
                ->submit('submit')
                ->color('danger'),
        ];
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        Maintenance::create([
            'machine_id' => $data['machine_id'],
            'reported_by' => auth()->id(),
            'type' => 'breakdown',
            'status' => 'pending', // usually reported/pending
            'reported_at' => $data['maintenance_date'],
            'problem_description' => "Judul: {$data['problem_title']}\nDeskripsi: {$data['description']}",
            'photo' => $data['photo'] ?? null,
            'priority' => 'medium',
        ]);

        Notification::make()->title('Laporan Abnormality Berhasil Dikirim!')->success()->send();
        $this->form->fill();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Maintenance::query()
                    ->where('reported_by', auth()->id())
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('work_order_number')
                    ->label('ID Laporan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('reporter.name')
                    ->label('Pelapor')
                    ->searchable(),
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
                    ->color(fn ($record) => $record->priority_badge_color),
                Tables\Columns\TextColumn::make('problem_description')
                    ->label('Masalah')
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record) => $record->status_badge_color),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Lapor')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->heading('Riwayat Laporan Abnormality Anda');
    }
}
