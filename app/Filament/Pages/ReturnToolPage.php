<?php

namespace App\Filament\Pages;

use App\Models\Tool;
use App\Models\ToolLoan;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use Filament\Tables\Table;

class ReturnToolPage extends Page implements HasForms, HasTable
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;

    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Pengembalian Alat';
    protected static ?string $title = 'Pengembalian Alat';
    protected static ?string $navigationGroup = 'Menu Operator';
    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.return-tool-page';

    public ?array $filterData = [];
    public ?ToolLoan $selectedLoan = null;

    public function mount(): void
    {
        $this->filterForm->fill();
    }

    protected function getForms(): array
    {
        return [
            'filterForm',
        ];
    }

    public function filterForm(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('barcode')
                    ->label('Cari / Scan Barcode Alat')
                    ->placeholder('Ketik nama atau kode alat...')
                    ->prefixIcon('heroicon-o-magnifying-glass')
                    ->live(debounce: 300)
                    ->columnSpanFull(),
            ])
            ->columns(1)
            ->statePath('filterData');
    }

    public function getActiveLoansProperty(): Collection
    {
        $query = ToolLoan::with('tool')
            ->where('borrower_id', auth()->id())
            ->where('status', 'borrowed');

        if (!empty($this->filterData['barcode'])) {
            $search = $this->filterData['barcode'];
            $query->whereHas('tool', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    public function selectLoan($id): void
    {
        $this->selectedLoan = ToolLoan::with('tool')->find($id);
    }

    public function findByCode(string $code): void
    {
        $loan = ToolLoan::with('tool')
            ->where('borrower_id', auth()->id())
            ->where('status', 'borrowed')
            ->whereHas('tool', function ($q) use ($code) {
                $q->where('code', $code);
            })->first();

        if ($loan) {
            $this->selectedLoan = $loan;
            Notification::make()->title('Data peminjaman ditemukan: ' . $loan->tool->name)->success()->send();
        } else {
            $this->selectedLoan = null;
            Notification::make()->title('Anda tidak sedang meminjam alat ini')->danger()->send();
        }
    }

    public function returnTool($condition = 'good', $notes = ''): void
    {
        if (!$this->selectedLoan) {
            Notification::make()->title('Pilih alat terlebih dahulu')->danger()->send();
            return;
        }

        $this->selectedLoan->update([
            'status' => 'returned',
            'returned_at' => now(),
            'notes' => $notes,
        ]);

        if ($condition !== 'good') {
            $this->selectedLoan->tool->update(['condition' => $condition]);
        }

        Notification::make()->title('Berhasil mengembalikan alat!')->success()->send();

        $this->selectedLoan = null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ToolLoan::query()
                    ->with('tool')
                    ->where('borrower_id', auth()->id())
                    ->where('status', 'returned')
                    ->latest('returned_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('returned_at')
                    ->label('Tgl Kembali')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('borrower.name')
                    ->label('Peminjam')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tool.name')
                    ->label('Alat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan'),
            ])
            ->heading('Riwayat Pengembalian Alat Anda');
    }
}
