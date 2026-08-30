<?php

namespace App\Filament\Resources\ToolLoanResource\Pages;

use App\Filament\Resources\ToolLoanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateToolLoan extends CreateRecord
{
    protected static string $resource = ToolLoanResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['borrower_id'])) {
            $data['borrower_id'] = auth()->id();
        }
        return $data;
    }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
