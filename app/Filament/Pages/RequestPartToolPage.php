<?php

namespace App\Filament\Pages;

use App\Models\PurchaseOrder;
use App\Models\PoItem;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use Filament\Tables\Table;

class RequestPartToolPage extends Page implements HasForms, HasTable
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;

    use InteractsWithForms, InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Request Part / Tool';
    protected static ?string $title = 'Request Part / Tool';
    protected static ?string $navigationGroup = 'Menu Operator';
    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.request-part-tool-page';

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
                    ->label('Nama Requester')
                    ->default(fn () => auth()->user()?->name)
                    ->disabled(),
                TextInput::make('item_name')
                    ->label('Nama Part / Tool')
                    ->required(),
                TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->default(1),
                Textarea::make('description')
                    ->label('Spesifikasi / Deskripsi / Alasan Request')
                    ->required()
                    ->rows(3),
                \Filament\Forms\Components\FileUpload::make('photo')
                    ->label('Foto Referensi (Opsional)')
                    ->image()
                    ->directory('po_requests'),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Ajukan Request')
                ->submit('submit')
                ->color('primary'),
        ];
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        // Cari atau buat Supplier "Internal Request"
        $vendor = \App\Models\Vendor::firstOrCreate(
            ['name' => 'Internal Request'],
            ['code' => 'VND-INT', 'pic_name' => 'Internal', 'email' => 'internal@example.com', 'phone' => '-']
        );

        $po = PurchaseOrder::create([
            'po_number' => 'REQ-' . date('Ymd') . '-' . rand(1000, 9999),
            'vendor_id' => $vendor->id,
            'created_by' => auth()->id(),
            'status' => 'draft',
            'po_date' => now(),
            'notes' => 'Request from operator: ' . (auth()->user()->name ?? 'Unknown'),
        ]);

        $category = \App\Models\ComponentCategory::firstOrCreate(
            ['name' => 'Requested Parts'],
            ['description' => 'Kategori untuk part yang baru di-request']
        );

        $component = \App\Models\Component::create([
            'code' => 'CPT-REQ-' . time(),
            'name' => $data['item_name'],
            'category_id' => $category->id,
            'status' => 'pending_arrival',
            'description' => $data['description'],
        ]);

        PoItem::create([
            'purchase_order_id' => $po->id,
            'component_id' => $component->id,
            'specifications' => $data['item_name'] . "\n" . $data['description'],
            'qty_ordered' => $data['quantity'],
            'unit_price' => 0,
            'subtotal' => 0,
            'photo' => $data['photo'] ?? null,
        ]);

        Notification::make()->title('Berhasil mengajukan request!')->success()->send();
        $this->form->fill();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PurchaseOrder::query()
                    ->where('notes', 'like', 'Request from operator:%')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Requester')
                    ->searchable(),
                Tables\Columns\TextColumn::make('po_number')
                    ->label('Nomor Request')
                    ->searchable(),
                Tables\Columns\ViewColumn::make('status')
                    ->label('Progress Status')
                    ->view('filament.columns.timeline-progress')
                    ->alignCenter(),
            ])
            ->heading('Riwayat Request Anda');
    }
}
