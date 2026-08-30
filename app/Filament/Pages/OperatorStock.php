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

class OperatorStock extends Page implements HasForms
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;

    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Ambil/Kembalikan Barang';
    protected static ?string $title = 'Ambil / Kembalikan Barang (Lama)';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?int $navigationSort = 0;
    protected static string $view = 'filament.pages.operator-stock';

    public ?array $filterData = [];
    public ?Component $selectedComponent = null;

    /** @var array<int, int> component_id => quantity */
    public array $cart = [];

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

    public function selectComponent($id): void
    {
        $this->selectedComponent = Component::find($id);
    }

    public function addToCart($id, $qty = 1): void
    {
        $qty = max(1, (int) $qty);
        $this->cart[$id] = ($this->cart[$id] ?? 0) + $qty;
        Notification::make()->title('Ditambahkan ke keranjang')->success()->send();
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
            $this->selectedComponent = $component;
            Notification::make()->title('Komponen ditemukan: ' . $component->name)->success()->send();
        } else {
            $this->selectedComponent = null;
            Notification::make()->title('Komponen tidak ditemukan')->danger()->send();
        }
    }

    public function returnItem($qty = 1): void
    {
        if (!$this->selectedComponent) {
            Notification::make()->title('Pilih komponen terlebih dahulu')->danger()->send();
            return;
        }

        $qty = max(1, (int) $qty);

        $this->createMovement($this->selectedComponent, 'return', $qty);

        Notification::make()->title('Barang berhasil dikembalikan')->success()->send();

        $this->selectedComponent = null;
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
            $this->createMovement($component, 'out', $qty);
        }

        Notification::make()->title('Semua barang di keranjang berhasil diambil')->success()->send();

        $this->cart = [];
    }

    protected function createMovement(Component $component, string $type, int $quantity): void
    {
        $movement = StockMovement::create([
            'component_id' => $component->id,
            'mold_id' => $component->mold_id,
            'requested_by' => auth()->id(),
            'type' => $type,
            'status' => 'pending',
            'quantity' => $quantity,
            'operator_name' => auth()->user()->name ?? 'Operator',
        ]);

        // Auto-approve, tanpa perlu persetujuan admin/leader
        $movement->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);
    }
}
