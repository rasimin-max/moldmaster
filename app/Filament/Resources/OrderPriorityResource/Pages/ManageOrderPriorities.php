<?php

namespace App\Filament\Resources\OrderPriorityResource\Pages;

use App\Filament\Resources\OrderPriorityResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageOrderPriorities extends ManageRecords
{
    protected static string $resource = OrderPriorityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
