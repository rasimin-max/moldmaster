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

class TakeItemPage extends Page implements HasForms, HasTable
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;

    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-top-right-on-square';
    protected static ?string $navigationLabel = 'Ambil Barang';
    protected static ?string $title = 'Ambil Barang';
    protected static ?string $navigationGroup = 'Menu Operator';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.take-item-page';

    public ?array $filterData = [];
    
    /** @var array<int, int> component_id => quantity */
    public array $cart = [];
    
    /** @var array<int, int> component_id => quantity */
    public array $inputQty = [];

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

    public function getCartItemsProperty(): Collection
    {
        if (empty($this->cart)) {
            return collect();
        }

        return Component::whereIn('id', array_keys($this->cart))
            ->get()
            ->map(function (Component $c) {
                $c->cart_qty = $this->cart[$c->id];
                return $c;
            });
    }

    public function addToCart($id, $qty = 1): void
    {
        $qty = max(1, (int) $qty);
        $this->cart[$id] = ($this->cart[$id] ?? 0) + $qty;
        Notification::make()->title('Ditambahkan ke keranjang')->success()->send();
    }

    public function addFromInput($id): void
    {
        $qty = (int) ($this->inputQty[$id] ?? 0);
        if ($qty > 0) {
            $this->addToCart($id, $qty);
            $this->inputQty[$id] = 0; // Reset after adding
        } else {
            Notification::make()->title('Jumlah tidak valid')->danger()->send();
        }
    }

    public function incrementCartItem($id): void
    {
        $this->cart[$id] = ($this->cart[$id] ?? 0) + 1;
    }

    public function decrementCartItem($id): void
    {
        if (!isset($this->cart[$id])) {
            return;
        }
        $this->cart[$id]--;
        if ($this->cart[$id] <= 0) {
            unset($this->cart[$id]);
        }
    }

    public function removeFromCart($id): void
    {
        unset($this->cart[$id]);
    }

    public function findByCode(string $code): void
    {
        $component = Component::where('code', $code)
            ->orWhere('qr_code', $code)
            ->first();

        if ($component) {
            // Automatically add 1 to cart
            $this->addToCart($component->id, 1);
        } else {
            Notification::make()->title('Komponen tidak ditemukan')->danger()->send();
        }
    }

    public function confirmCart(): void
    {
        if (empty($this->cart)) {
            Notification::make()->title('Keranjang masih kosong')->danger()->send();
            return;
        }

        foreach ($this->cart as $componentId => $qty) {
            $component = Component::find($componentId);
            if (!$component) {
                continue;
            }
            if ($component->available_stock < $qty) {
                Notification::make()->title("Stok {$component->name} tidak mencukupi, dilewati")->warning()->send();
                continue;
            }
            
            StockMovement::create([
                'component_id' => $component->id,
                'mold_id' => $component->mold_id,
                'requested_by' => auth()->id(),
                'type' => 'out',
                'status' => 'approved',
                'quantity' => $qty,
                'operator_name' => auth()->user()->name ?? 'Operator',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        }

        Notification::make()->title('Semua barang di keranjang berhasil diambil')->success()->send();

        $this->cart = [];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StockMovement::query()
                    ->where('requested_by', auth()->id())
                    ->where('type', 'out')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Pengambil')
                    ->searchable(),
                Tables\Columns\TextColumn::make('component.code')
                    ->label('Barcode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('component.name')
                    ->label('Nama Item')
                    ->searchable(),
                Tables\Columns\TextColumn::make('component.size_spec')
                    ->label('Spek')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mold.code')
                    ->label('Nomor Mold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mold.project_name')
                    ->label('Project')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->heading('Laporan Pengambilan Barang Anda');
    }
}
