<?php

namespace App\Filament\Resources\ComponentCategoryResource\Pages;

use App\Filament\Resources\ComponentCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComponentCategory extends EditRecord
{
    protected static string $resource = ComponentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (\App\Models\ComponentCategory $record, Actions\DeleteAction $action) {
                    if ($record->components()->withTrashed()->count() > 0) {
                        \Filament\Notifications\Notification::make()
                            ->warning()
                            ->title('Gagal Menghapus')
                            ->body('Bagian ini tidak dapat dihapus karena masih digunakan oleh beberapa komponen.')
                            ->persistent()
                            ->send();
                        $action->cancel();
                    }
                }),
        ];
    }
}
