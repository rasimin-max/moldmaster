<?php

namespace App\Filament\Pages;

use App\Models\PurchaseOrder;
use App\Models\PoItem;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class ApprovalRequestPage extends Page implements HasForms, HasTable
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;

    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationLabel = 'Kontrol Request Part/Tool';
    protected static ?string $title = 'Kontrol Request Part & Tool';
    protected static ?string $navigationGroup = 'Menu Approval';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.approval-request-page';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PurchaseOrder::query()
                    ->where('status', 'draft')
                    ->where('notes', 'like', 'Request from operator:%')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Request')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('po_number')
                    ->label('Nomor Request')
                    ->searchable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Pemohon'),
                Tables\Columns\TextColumn::make('items.specifications')
                    ->label('Detail Request')
                    ->limit(50),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color('warning'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->action(function (PurchaseOrder $record) {
                        $record->update([
                            'status' => 'sent',
                        ]);
                        // If it has a temporary component, update its status maybe?
                        Notification::make()->title('Request Disetujui (Menunggu Order Admin)')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->action(function (PurchaseOrder $record) {
                        $record->update([
                            'status' => 'cancelled',
                        ]);
                        Notification::make()->title('Request Ditolak')->success()->send();
                    }),
            ])
            ->emptyStateHeading('Tidak ada request part/tool yang perlu di-approve saat ini.');
    }
}
