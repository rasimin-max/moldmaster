<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MachineOperationRecordResource\Pages;
use App\Models\MachineOperationRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MachineOperationRecordResource extends Resource
{
    protected static ?string $model = MachineOperationRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-play-circle';
    protected static ?string $navigationGroup = 'Menu Operator';
    protected static ?string $modelLabel = 'Machine operation record';
    protected static ?string $pluralModelLabel = 'Machine operation records';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Photo & Notes')
                    ->schema([
                        Forms\Components\FileUpload::make('photo')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('4:3')
                            ->disk('public')
                            ->directory('operation_records')
                            ->nullable()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Operation Details')
                    ->schema([
                        Forms\Components\TextInput::make('scan_barcode')
                            ->label('Scan Barcode Program')
                            ->placeholder('Ketikan atau scan barcode di sini...')
                            ->autofocus()
                            ->live()
                            ->dehydrated(false) // tidak disimpan ke database
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if ($state) {
                                    $program = \App\Models\MachineProgram::where('barcode', $state)->first();
                                    if ($program) {
                                        $set('machine_program_id', $program->id);
                                        $set('machine_id', $program->machine_id);
                                        $set('project_id', $program->project_id);
                                        $set('mold_id', $program->mold_id);
                                        $set('component_id', $program->component_id);
                                        
                                        if ($program->estimated_time) {
                                            $time = str_replace(',', '.', $program->estimated_time);
                                            $set('planned_duration_minutes', $time);
                                        }

                                        // Set technical details for display
                                        $set('r_f', $program->r_f);
                                        $set('b', $program->b);
                                        $set('tool_no', $program->tool_no);
                                        $set('tool_name', $program->tool_name);
                                        $set('tool_dia', $program->tool_dia);
                                        $set('tool_r', $program->tool_r);
                                        $set('tool_length_total', $program->tool_length_total);
                                        $set('tool_length_eff', $program->tool_length_eff);
                                        $set('tool_num', $program->tool_num);
                                        $set('holder', $program->holder);
                                        $set('ps_thick', $program->ps_thick);
                                        $set('rpm', $program->rpm);
                                        $set('feed', $program->feed);
                                        $set('doc', $program->doc);
                                        $set('setting', $program->setting);
                                    }
                                }
                            })
                            ->columnSpanFull(),
                        Forms\Components\Group::make([
                            Forms\Components\Select::make('machine_id')
                                ->relationship('machine', 'name')
                                ->label('Nama Mesin')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Forms\Components\Select::make('project_id')
                                ->relationship('project', 'name')
                                ->label('Nama Project')
                                ->searchable()
                                ->preload(),
                            Forms\Components\Select::make('mold_id')
                                ->relationship('mold', 'name')
                                ->label('Nomor / Nama Mold')
                                ->searchable()
                                ->preload(),
                            Forms\Components\Select::make('component_id')
                                ->relationship('component', 'name')
                                ->label('Nama Komponen Proses')
                                ->searchable()
                                ->preload(),
                            Forms\Components\Select::make('machine_program_id')
                                ->relationship('machineProgram', 'name')
                                ->label('Nama Program')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function (Forms\Set $set, $state) {
                                    if ($state) {
                                        $program = \App\Models\MachineProgram::find($state);
                                        if ($program) {
                                            if ($program->estimated_time) {
                                                // Ganti koma jadi titik agar terbaca sebagai desimal di database
                                                $time = str_replace(',', '.', $program->estimated_time);
                                                $set('planned_duration_minutes', $time);
                                            }
                                            // Set technical details for display
                                            $set('r_f', $program->r_f);
                                            $set('b', $program->b);
                                            $set('tool_no', $program->tool_no);
                                            $set('tool_name', $program->tool_name);
                                            $set('tool_dia', $program->tool_dia);
                                            $set('tool_r', $program->tool_r);
                                            $set('tool_length_total', $program->tool_length_total);
                                            $set('tool_length_eff', $program->tool_length_eff);
                                            $set('tool_num', $program->tool_num);
                                            $set('holder', $program->holder);
                                            $set('ps_thick', $program->ps_thick);
                                            $set('rpm', $program->rpm);
                                            $set('feed', $program->feed);
                                            $set('doc', $program->doc);
                                            $set('setting', $program->setting);
                                        }
                                    }
                                }),
                            Forms\Components\Select::make('user_id')
                                ->relationship('user', 'name')
                                ->default(fn () => Auth::id())
                                ->searchable()
                                ->preload()
                                ->label('Operator'),
                            Forms\Components\Select::make('operation_type')
                                ->options([
                                    'production' => 'Production',
                                    'setup' => 'Setup/Molding',
                                    'trial' => 'Trial',
                                    'maintenance' => 'Maintenance',
                                ])
                                ->required()
                                ->default('production'),
                            Forms\Components\TextInput::make('cycles')
                                ->numeric()
                                ->label('Cycles / Shots (optional)')
                                ->helperText('Isi jika mengukur dalam jumlah cycle/shot'),
                        ])->columns(2)->columnSpanFull(),
                        
                        Forms\Components\Section::make('Program Details (Auto-filled)')
                            ->schema([
                                Forms\Components\TextInput::make('r_f')->label('R/F')->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('b')->label('B')->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('tool_no')->label('Tool No')->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('tool_name')->label('Tool Name')->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('tool_dia')->label('Tool Dia')->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('tool_r')->label('Tool R')->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('tool_length_total')->label('Length Total')->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('tool_length_eff')->label('Length Eff')->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('tool_num')->label('Tool Num')->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('holder')->label('Holder')->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('ps_thick')->label('PS Thick')->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('rpm')->label('RPM')->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('feed')->label('Feed')->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('doc')->label('DoC')->disabled()->dehydrated(false),
                                Forms\Components\TextInput::make('setting')->label('Setting')->disabled()->dehydrated(false),
                            ])->columns(3)->collapsible()->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Time Tracking')
                    ->schema([
                        Forms\Components\TextInput::make('planned_duration_minutes')
                            ->numeric()
                            ->step(0.01)
                            ->label('Plan Waktu Proses (Jam)'),
                        Forms\Components\DateTimePicker::make('start_time')
                            ->default(now())
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                $start = $get('start_time');
                                $end = $get('end_time');
                                if ($start && $end) {
                                    $set('duration_minutes', \Carbon\Carbon::parse($start)->diffInMinutes(\Carbon\Carbon::parse($end)));
                                }
                            }),
                        Forms\Components\DateTimePicker::make('end_time')
                            ->helperText('Kosongkan jika mesin masih berjalan.')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                $start = $get('start_time');
                                $end = $get('end_time');
                                if ($start && $end) {
                                    $set('duration_minutes', \Carbon\Carbon::parse($start)->diffInMinutes(\Carbon\Carbon::parse($end)));
                                }
                            }),
                        Forms\Components\TextInput::make('duration_minutes')
                            ->numeric()
                            ->label('Aktual Waktu Proses (Menit)')
                            ->helperText('Dihitung otomatis atau isi manual dalam satuan menit.'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'plan_job' => 'Plan Job',
                                'running' => 'Running',
                                'completed' => 'Completed',
                            ])
                            ->required()
                            ->default('running'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ViewColumn::make('photo')
                    ->label('Foto')
                    ->view('filament.tables.columns.hover-image'),
                Tables\Columns\TextColumn::make('machine.name')
                    ->label('Nama Mesin')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Nama Project')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('mold.name')
                    ->label('Nama Mold')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('component.name')
                    ->label('Nama Komponen')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('machineProgram.name')
                    ->label('Nama Program')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Operator')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('operation_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'production' => 'success',
                        'setup' => 'warning',
                        'trial' => 'info',
                        'maintenance' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('start_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('planned_duration_minutes')
                    ->label('Plan Waktu (Hrs)')
                    ->formatStateUsing(fn ($state) => $state ? number_format((float)$state, 2) . ' Hrs' : '-')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Aktual Proses')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 60, 2) . ' Hrs' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('machine.hourly_rate')
                    ->label('Rate Mesin/Jam')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('cost')
                    ->label('Total Biaya')
                    ->state(function (MachineOperationRecord $record) {
                        if (!$record->duration_minutes || !$record->machine || !$record->machine->hourly_rate) return 0;
                        return ($record->duration_minutes / 60) * $record->machine->hourly_rate;
                    })
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('cycles')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'plan_job' => 'info',
                        'running' => 'success',
                        'completed' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->relationship('project', 'name')
                    ->label('Project')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('mold_id')
                    ->relationship('mold', 'name')
                    ->label('Nama Mold')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('machine_id')
                    ->relationship('machine', 'name')
                    ->label('Mesin')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Operator')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('component_id')
                    ->relationship('component', 'name')
                    ->label('Nama Komponen')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'plan_job' => 'Plan Job',
                        'running' => 'Running',
                        'completed' => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('StartJob')
                    ->label('Mulai Job')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->hidden(fn (MachineOperationRecord $record) => $record->status !== 'plan_job')
                    ->action(function (MachineOperationRecord $record) {
                        $record->update([
                            'status' => 'running',
                            'start_time' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('Stop')
                    ->icon('heroicon-o-stop-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->hidden(fn (MachineOperationRecord $record) => in_array($record->status, ['completed', 'plan_job']))
                    ->action(function (MachineOperationRecord $record) {
                        $record->update([
                            'status' => 'completed',
                            'end_time' => now(),
                        ]);
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    \pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction::make()
                        ->label('Export Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->exports([
                            \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                                ->withFilename(date('Y-m-d') . ' - Machine Operation Records')
                                ->withColumns([
                                    \pxlrbt\FilamentExcel\Columns\Column::make('machine.name')->heading('Nama Mesin'),
                                    \pxlrbt\FilamentExcel\Columns\Column::make('project.name')->heading('Nama Project'),
                                    \pxlrbt\FilamentExcel\Columns\Column::make('mold.name')->heading('Nama Mold'),
                                    \pxlrbt\FilamentExcel\Columns\Column::make('component.name')->heading('Nama Komponen'),
                                    \pxlrbt\FilamentExcel\Columns\Column::make('machineProgram.name')->heading('Nama Program'),
                                    \pxlrbt\FilamentExcel\Columns\Column::make('user.name')->heading('Operator'),
                                    \pxlrbt\FilamentExcel\Columns\Column::make('operation_type')->heading('Operation Type'),
                                    \pxlrbt\FilamentExcel\Columns\Column::make('start_time')->heading('Start Time'),
                                    \pxlrbt\FilamentExcel\Columns\Column::make('end_time')->heading('End Time'),
                                    \pxlrbt\FilamentExcel\Columns\Column::make('planned_duration_minutes')->heading('Plan Waktu (Hrs)')->formatStateUsing(fn ($state) => $state ? number_format((float)$state, 2) . ' Hrs' : '-'),
                                    \pxlrbt\FilamentExcel\Columns\Column::make('duration_minutes')->heading('Aktual Proses')->formatStateUsing(fn ($state) => $state ? number_format($state / 60, 2) . ' Hrs' : '-'),
                                    \pxlrbt\FilamentExcel\Columns\Column::make('machine.hourly_rate')->heading('Rate Mesin/Jam'),
                                    \pxlrbt\FilamentExcel\Columns\Column::make('cost')->heading('Total Biaya')->getStateUsing(function ($record) {
                                        if (!$record->duration_minutes || !$record->machine || !$record->machine->hourly_rate) return 0;
                                        return ($record->duration_minutes / 60) * $record->machine->hourly_rate;
                                    }),
                                    \pxlrbt\FilamentExcel\Columns\Column::make('cycles')->heading('Cycles'),
                                    \pxlrbt\FilamentExcel\Columns\Column::make('status')->heading('Status'),
                                ])
                        ]),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMachineOperationRecords::route('/'),
            'create' => Pages\CreateMachineOperationRecord::route('/create'),
            'edit' => Pages\EditMachineOperationRecord::route('/{record}/edit'),
        ];
    }
}
