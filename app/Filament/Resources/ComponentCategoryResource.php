<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComponentCategoryResource\Pages;
use App\Models\ComponentCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ComponentCategoryResource extends Resource
{
    protected static ?string $model = ComponentCategory::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Bagian';
    protected static ?string $modelLabel = 'Bagian';
    protected static ?string $pluralModelLabel = 'Bagian';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Bagian')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Bagian')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (ComponentCategory $record, Tables\Actions\DeleteAction $action) {
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, Tables\Actions\DeleteBulkAction $action) {
                            $inUse = 0;
                            foreach ($records as $record) {
                                if ($record->components()->withTrashed()->count() > 0) {
                                    $inUse++;
                                } else {
                                    $record->delete();
                                }
                            }
                            
                            if ($inUse > 0) {
                                \Filament\Notifications\Notification::make()
                                    ->warning()
                                    ->title("{$inUse} Bagian Gagal Dihapus")
                                    ->body("Beberapa bagian tidak dapat dihapus karena masih digunakan oleh komponen.")
                                    ->persistent()
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComponentCategories::route('/'),
            'create' => Pages\CreateComponentCategory::route('/create'),
            'edit' => Pages\EditComponentCategory::route('/{record}/edit'),
        ];
    }
}
