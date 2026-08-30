<?php

namespace App\Filament\Resources\ToolLoanResource\Pages;

use App\Filament\Resources\ToolLoanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditToolLoan extends EditRecord
{
    protected static string $resource = ToolLoanResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
