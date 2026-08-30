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

class BorrowToolPage extends Page implements HasForms, HasTable
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;

    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-wrench';
    protected static ?string $navigationLabel = 'Pinjam Alat';
    protected static ?string $title = 'Pinjam Alat';
    protected static ?string $navigationGroup = 'Menu Operator';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.borrow-tool-page';

    public ?array $filterData = [];
    public ?Tool $selectedTool = null;

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
                Forms\Components\Select::make('category')
                    ->label('Kategori Alat')
                    ->options(Tool::distinct()->pluck('category', 'category'))
                    ->searchable()
                    ->live(),
            ])
            ->columns(2)
            ->statePath('filterData');
    }

    public function getFilteredToolsProperty(): Collection
    {
        $query = Tool::query();

        if (!empty($this->filterData['barcode'])) {
            $search = $this->filterData['barcode'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filterData['category'])) {
            $query->where('category', $this->filterData['category']);
        }

        return $query->where('condition', 'good')->where('available_quantity', '>', 0)->limit(100)->get();
    }

    public function selectTool($id): void
    {
        $this->selectedTool = Tool::find($id);
    }

    public function findByCode(string $code): void
    {
        $tool = Tool::where('code', $code)->first();

        if ($tool) {
            if ($tool->condition !== 'good') {
                Notification::make()->title('Alat sedang rusak / tidak bisa dipinjam')->danger()->send();
                return;
            }
            if ($tool->available_quantity <= 0) {
                Notification::make()->title('Stok alat habis')->danger()->send();
                return;
            }
            
            $this->selectedTool = $tool;
            Notification::make()->title('Alat ditemukan: ' . $tool->name)->success()->send();
        } else {
            $this->selectedTool = null;
            Notification::make()->title('Alat tidak ditemukan')->danger()->send();
        }
    }

    public function borrowTool($qty = 1, $purpose = ''): void
    {
        if (!$this->selectedTool) {
            Notification::make()->title('Pilih alat terlebih dahulu')->danger()->send();
            return;
        }

        $qty = max(1, (int) $qty);

        if ($qty > $this->selectedTool->available_quantity) {
            Notification::make()->title('Jumlah melebihi stok yang tersedia')->danger()->send();
            return;
        }

        if (empty($purpose)) {
            Notification::make()->title('Harap isi keperluan peminjaman')->danger()->send();
            return;
        }

        ToolLoan::create([
            'tool_id' => $this->selectedTool->id,
            'borrower_id' => auth()->id(),
            'quantity' => $qty,
            'purpose' => $purpose,
            'status' => 'borrowed',
            'borrowed_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        Notification::make()->title('Berhasil meminjam alat!')->success()->send();

        $this->selectedTool = null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ToolLoan::query()
                    ->with('tool')
                    ->where('borrower_id', auth()->id())
                    ->where('status', 'borrowed')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('borrowed_at')
                    ->label('Tgl Pinjam')
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
                Tables\Columns\TextColumn::make('purpose')
                    ->label('Keperluan'),
            ])
            ->heading('Alat yang Sedang Anda Pinjam');
    }
}
