<?php

namespace App\Filament\Pages;

use App\Models\Component;
use App\Models\ComponentCategory;
use App\Models\Mold;
use App\Models\StockMovement;
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

class ReturnItemPage extends Page implements HasForms, HasTable
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;

    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-left';
    protected static ?string $navigationLabel = 'Pengembalian Barang';
    protected static ?string $title = 'Pengembalian Barang';
    protected static ?string $navigationGroup = 'Menu Operator';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.return-item-page';

    public ?array $filterData = [];
    public ?Component $selectedComponent = null;

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
                    ->label('Cari / Scan Barcode')
                    ->placeholder('Ketik nama atau kode komponen...')
                    ->prefixIcon('heroicon-o-magnifying-glass')
                    ->live(debounce: 300)
                    ->columnSpanFull(),
                Forms\Components\Select::make('project_name')
                    ->label('Project')
                    ->options(fn () => Mold::whereNotNull('project_name')->distinct()->pluck('project_name', 'project_name'))
                    ->searchable()
                    ->live(),
                Forms\Components\Select::make('mold_id')
                    ->label('Mold')
                    ->options(function (Forms\Get $get) {
                        $query = Mold::query();
                        if ($get('project_name')) {
                            $query->where('project_name', $get('project_name'));
                        }
                        return $query->get()->mapWithKeys(fn ($m) => [$m->id => "{$m->code} - {$m->name}"]);
                    })
                    ->searchable()
                    ->live(),
                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->options(ComponentCategory::pluck('name', 'id'))
                    ->searchable()
                    ->live(),
            ])
            ->columns(3)
            ->statePath('filterData');
    }

    public function getFilteredComponentsProperty(): Collection
    {
        $query = Component::query();

        if (!empty($this->filterData['barcode'])) {
            $search = $this->filterData['barcode'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('qr_code', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filterData['mold_id'])) {
            $query->where('mold_id', $this->filterData['mold_id']);
        }

        if (!empty($this->filterData['category_id'])) {
            $query->where('category_id', $this->filterData['category_id']);
        }

        if (!empty($this->filterData['project_name'])) {
            $query->whereHas('mold', fn ($q) => $q->where('project_name', $this->filterData['project_name']));
        }

        return $query->limit(100)->get();
    }

    public function selectComponent($id): void
    {
        $this->selectedComponent = Component::find($id);
    }

    public function findByCode(string $code): void
    {
        $component = Component::where('code', $code)
            ->orWhere('qr_code', $code)
            ->first();

        if ($component) {
            $this->selectedComponent = $component;
            Notification::make()->title('Komponen ditemukan: ' . $component->name)->success()->send();
        } else {
            $this->selectedComponent = null;
            Notification::make()->title('Komponen tidak ditemukan')->danger()->send();
        }
    }

    public function returnItem($qty = 1, $condition = 'good', $notes = ''): void
    {
        if (!$this->selectedComponent) {
            Notification::make()->title('Pilih komponen terlebih dahulu')->danger()->send();
            return;
        }

        $qty = max(1, (int) $qty);

        StockMovement::create([
            'component_id' => $this->selectedComponent->id,
            'mold_id' => $this->selectedComponent->mold_id,
            'requested_by' => auth()->id(),
            'type' => 'return',
            'status' => 'approved',
            'quantity' => $qty,
            'operator_name' => auth()->user()->name ?? 'Operator',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes' => $notes,
        ]);

        Notification::make()->title('Barang berhasil dikembalikan')->success()->send();

        $this->selectedComponent = null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StockMovement::query()
                    ->where('requested_by', auth()->id())
                    ->where('type', 'return')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Pengembali')
                    ->searchable(),
                Tables\Columns\TextColumn::make('component.name')
                    ->label('Komponen')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan'),
            ])
            ->heading('Laporan Pengembalian Barang Anda');
    }
}
