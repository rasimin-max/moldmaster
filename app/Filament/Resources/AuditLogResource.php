<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Model;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Administrasi';
    protected static ?string $navigationLabel = 'Audit Log';
    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Waktu')->dateTime('d/m/Y H:i:s')->sortable()->width('160px'),
                Tables\Columns\TextColumn::make('user_name')->label('User')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('action')
                    ->label('Aksi')
                    ->colors([
                        'success' => fn($state) => in_array($state, ['created', 'approved', 'login']),
                        'danger' => fn($state) => in_array($state, ['deleted', 'rejected']),
                        'warning' => fn($state) => in_array($state, ['updated', 'logout']),
                        'info' => 'viewed',
                    ]),
                Tables\Columns\TextColumn::make('model_type')
                    ->label('Model')
                    ->formatStateUsing(fn($state) => class_basename($state ?? ''))
                    ->badge()->color('gray'),
                Tables\Columns\TextColumn::make('description')->label('Deskripsi')->limit(60)->wrap(),
                Tables\Columns\TextColumn::make('ip_address')->label('IP')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['changes'] = [];
                        if (!empty($data['old_values']) || !empty($data['new_values'])) {
                            $old = is_array($data['old_values']) ? $data['old_values'] : json_decode($data['old_values'], true);
                            $new = is_array($data['new_values']) ? $data['new_values'] : json_decode($data['new_values'], true);
                            
                            $keys = array_unique(array_merge(array_keys($old ?? []), array_keys($new ?? [])));
                            foreach ($keys as $key) {
                                $data['changes'][] = [
                                    'field' => $key,
                                    'old' => $old[$key] ?? null,
                                    'new' => $new[$key] ?? null,
                                ];
                            }
                        }
                        return $data;
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options(['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'login' => 'Login', 'logout' => 'Logout']),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Log')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')->label('Waktu')->dateTime('d/m/Y H:i:s'),
                        Infolists\Components\TextEntry::make('user_name')->label('User (Pelaku)'),
                        Infolists\Components\TextEntry::make('action')->label('Aksi')->badge()->color(fn($state) => match($state) {
                            'created', 'approved', 'login' => 'success',
                            'deleted', 'rejected' => 'danger',
                            'updated', 'logout' => 'warning',
                            default => 'gray',
                        }),
                        Infolists\Components\TextEntry::make('model_type')->label('Target Model'),
                        Infolists\Components\TextEntry::make('description')->label('Deskripsi')->columnSpanFull(),
                        Infolists\Components\TextEntry::make('ip_address')->label('IP Address'),
                        Infolists\Components\TextEntry::make('user_agent')->label('User Agent')->columnSpanFull(),
                    ])->columns(2),

                Infolists\Components\Section::make('Perubahan Data (Old vs New)')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('changes')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('field')->label('Field/Kolom')->weight('bold'),
                                Infolists\Components\TextEntry::make('old')->label('Nilai Lama')->color('danger'),
                                Infolists\Components\TextEntry::make('new')->label('Nilai Baru')->color('success'),
                            ])
                            ->columns(3)
                            ->visible(fn ($record) => !empty($record->old_values) || !empty($record->new_values)),
                    ])
            ]);
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit(Model $record): bool { return false; }
    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAuditLogs::route('/')];
    }
}
