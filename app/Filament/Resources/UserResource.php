<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Administrasi';
    protected static ?string $navigationLabel = 'Manajemen User';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'Users';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi User')->schema([
                Forms\Components\TextInput::make('name')->label('Nama Lengkap')->required()->maxLength(255),
                Forms\Components\TextInput::make('employee_id')->label('ID Karyawan')->unique(ignoreRecord: true)->nullable(),
                Forms\Components\TextInput::make('email')->label('Email')->email()->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => !empty($state) ? Hash::make($state) : null)
                    ->dehydrated(fn($state) => !empty($state))
                    ->required(fn(string $context) => $context === 'create')
                    ->placeholder('Kosongkan jika tidak ingin ubah password'),
                Forms\Components\TextInput::make('phone')->label('No. Telepon')->nullable(),
                Forms\Components\Select::make('area')->label('Area/Divisi')
                    ->options([
                        'CNC' => 'CNC', 'EDM' => 'EDM', 'Wirecut' => 'Wirecut',
                        'Grinding' => 'Grinding', 'Polishing' => 'Polishing',
                        'Assembly' => 'Assembly', 'All' => 'Semua Area',
                    ])->nullable(),
                Forms\Components\Select::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name', function (\Illuminate\Database\Eloquent\Builder $query) {
                        if (!auth()->user()->hasRole('super_admin')) {
                            $query->where('name', '!=', 'super_admin');
                        }
                    })
                    ->getOptionLabelFromRecordUsing(fn ($record) => strtoupper(str_replace('_', ' ', $record->name)))
                    ->multiple()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('permissions')
                    ->label('Akses Tambahan (Izin Khusus)')
                    ->relationship('permissions', 'name', fn ($query) => $query->where('name', 'not like', '% %'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => ucwords(str_replace(['_', '::'], ' ', $record->name)))
                    ->searchable()
                    ->multiple()
                    ->preload(),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
                Forms\Components\TextInput::make('hourly_rate')
                    ->label('Upah per Jam (Cost)')
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                Forms\Components\FileUpload::make('avatar')->label('Foto')->image()->directory('avatars')->nullable(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')->label('')->circular()->defaultImageUrl(asset('images/default-avatar.png')),
                Tables\Columns\TextColumn::make('employee_id')->label('ID')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn($state) => strtoupper(str_replace('_', ' ', $state)))
                    ->color(fn($state) => match($state) {
                        'super_admin' => 'danger', 'admin' => 'warning', 'leader' => 'info',
                        'operator' => 'success', 'viewer' => 'gray', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('area')->label('Area')->badge()->color('indigo'),
                Tables\Columns\TextColumn::make('hourly_rate')->label('Rate/Jam')->money('IDR')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('last_login_at')->label('Login Terakhir')->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options(Role::pluck('name', 'name'))
                    ->query(fn(\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder => $data['value'] ? $query->role($data['value']) : $query),
                Tables\Filters\TernaryFilter::make('is_active')->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn(User $r) => $r->is_active ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn(User $r) => $r->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn(User $r) => $r->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(fn(User $record) => $record->update(['is_active' => !$record->is_active])),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array { 
        return [
            UserResource\RelationManagers\AuditLogsRelationManager::class,
        ]; 
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
