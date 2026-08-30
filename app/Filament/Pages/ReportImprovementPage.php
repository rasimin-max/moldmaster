<?php

namespace App\Filament\Pages;

use App\Models\Improvement;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use Filament\Tables\Table;

class ReportImprovementPage extends Page implements HasForms, HasTable
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;

    use InteractsWithForms, InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';
    protected static ?string $navigationLabel = 'Info Improvement';
    protected static ?string $title = 'Lapor Ide Improvement';
    protected static ?string $navigationGroup = 'Menu Operator';
    protected static ?int $navigationSort = 7;

    protected static string $view = 'filament.pages.report-improvement-page';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('operator_name')
                    ->label('Nama Pelapor')
                    ->default(fn () => auth()->user()?->name)
                    ->disabled(),
                TextInput::make('title')
                    ->label('Judul Ide / Improvement')
                    ->required(),
                Textarea::make('description')
                    ->label('Deskripsi Improvement')
                    ->required()
                    ->rows(4),
                \Filament\Forms\Components\Grid::make(2)
                    ->schema([
                        \Filament\Forms\Components\FileUpload::make('photo_before')
                            ->label('Foto Before (Opsional)')
                            ->image()
                            ->directory('improvements'),
                        \Filament\Forms\Components\FileUpload::make('photo_after')
                            ->label('Foto After (Opsional)')
                            ->image()
                            ->directory('improvements'),
                    ]),
                \Filament\Forms\Components\Grid::make(3)
                    ->schema([
                        TextInput::make('cost_effect')
                            ->label('Estimasi Jumlah Cost Effect (Rp)')
                            ->numeric()
                            ->prefix('Rp'),
                        \Filament\Forms\Components\DatePicker::make('implementation_date')
                            ->label('Kapan Pelaksanaan')
                            ->native(false),
                        TextInput::make('cost_investment')
                            ->label('Cost Investment Jika Ada (Rp)')
                            ->numeric()
                            ->prefix('Rp'),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Kirim Ide')
                ->submit('submit')
                ->color('success'),
        ];
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        Improvement::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'photo_before' => $data['photo_before'] ?? null,
            'photo_after' => $data['photo_after'] ?? null,
            'cost_effect' => $data['cost_effect'] ?? null,
            'implementation_date' => $data['implementation_date'] ?? null,
            'cost_investment' => $data['cost_investment'] ?? null,
            'user_id' => auth()->id(),
            'reporter_name' => auth()->user()->name ?? 'Operator',
            'status' => 'pending',
        ]);

        Notification::make()->title('Ide Improvement Berhasil Dikirim!')->success()->send();
        $this->form->fill();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Improvement::query()
                    ->where('user_id', auth()->id())
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Info')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reporter_name')
                    ->label('Pelapor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Ide / Improvement')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->heading('Riwayat Info Improvement Anda');
    }
}
