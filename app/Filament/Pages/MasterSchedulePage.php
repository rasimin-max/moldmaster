<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Project;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\MaxWidth;

class MasterSchedulePage extends Page
{
    use \BezhanSalleh\FilamentShield\Traits\HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Detail Project';
    protected static ?string $navigationLabel = 'Master Schedule';
    protected static ?string $title = 'Global Project Schedule';

    protected static string $view = 'filament.pages.master-schedule-page';

    public ?string $filterMachineType = 'All';
    public ?string $filterPhase = 'All';
    public string $stasionViewMode = 'week';
    public string $projectViewMode = 'daily';
    public int $stasionDateOffset = 0;

    public function updatedStasionViewMode()
    {
        $this->stasionDateOffset = 0;
    }

    public function nextStasionDate()
    {
        $this->stasionDateOffset++;
    }

    public function previousStasionDate()
    {
        $this->stasionDateOffset--;
    }

    public function todayStasionDate()
    {
        $this->stasionDateOffset = 0;
    }

    protected function getViewData(): array
    {
        $projects = Project::with(['phases' => function ($query) {
            $query->orderBy('start_date');
        }])->where('status', 'active')->get();

        // --- STASION SCHEDULE LOGIC ---
        $now = now();
        if ($this->stasionViewMode === 'day') {
            $baseDate = $now->copy()->addDays($this->stasionDateOffset);
            $startDate = $baseDate->startOfDay();
            $endDate = $startDate->copy()->endOfDay();
            $daysCount = 1;
        } elseif ($this->stasionViewMode === 'month') {
            $baseDate = $now->copy()->addMonths($this->stasionDateOffset);
            $startDate = $baseDate->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $daysCount = $startDate->daysInMonth;
        } else {
            $baseDate = $now->copy()->addWeeks($this->stasionDateOffset);
            $startDate = $baseDate->startOfWeek(\Carbon\Carbon::MONDAY);
            $endDate = $startDate->copy()->addDays(6)->endOfDay();
            $daysCount = 7;
        }

        $machinesQuery = \App\Models\Machine::with(['operationRecords' => function ($query) use ($startDate, $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_time', [$startDate, $endDate])
                  ->orWhereBetween('end_time', [$startDate, $endDate]);
            })->with(['project', 'component']);
        }])->where('status', '!=', 'retired');

        if ($this->filterMachineType && $this->filterMachineType !== 'All') {
            $machinesQuery->where('type', $this->filterMachineType);
        }

        $machines = $machinesQuery->get();

        $machineTypes = \App\Models\Machine::where('status', '!=', 'retired')
            ->select('type')
            ->distinct()
            ->pluck('type')
            ->filter()
            ->toArray();
            
        // --- GLOBAL PROJECT SCHEDULE LOGIC ---
        $projectPeriods = [];
        $projectTotalDuration = 1; // Prevent division by zero
        $projectStartDate = now()->startOfDay();
        
        if ($this->projectViewMode === 'daily') {
            // Daily view: 30 days from start of current month
            $projectStartDate = now()->startOfMonth();
            $currentDate = $projectStartDate->copy();
            for ($i = 0; $i < $projectStartDate->daysInMonth; $i++) {
                $projectPeriods[] = [
                    'label' => $currentDate->format('j'),
                    'sub_label' => $currentDate->format('M'),
                    'start' => $currentDate->copy()->startOfDay(),
                    'end' => $currentDate->copy()->endOfDay(),
                ];
                $currentDate->addDay();
            }
        } elseif ($this->projectViewMode === 'weekly') {
            // Weekly view: 12 weeks from start of current week
            $projectStartDate = now()->startOfWeek();
            $currentDate = $projectStartDate->copy();
            for ($i = 0; $i < 12; $i++) {
                $projectPeriods[] = [
                    'label' => 'W' . $currentDate->format('W'),
                    'sub_label' => $currentDate->format('M'),
                    'start' => $currentDate->copy()->startOfWeek(),
                    'end' => $currentDate->copy()->endOfWeek(),
                ];
                $currentDate->addWeek();
            }
        } else {
            // Monthly view: Dynamically fit all project phases
            $earliestDate = now()->startOfMonth();
            $latestDate = now()->copy()->addMonths(5)->endOfMonth(); // default 6 months span
            
            foreach ($projects as $proj) {
                foreach ($proj->phases as $p) {
                    if ($p->start_date) {
                        $pDate = \Carbon\Carbon::parse($p->start_date)->startOfMonth();
                        if ($pDate->lessThan($earliestDate)) {
                            $earliestDate = $pDate->copy();
                        }
                    }
                    if ($p->end_date) {
                        $pEnd = \Carbon\Carbon::parse($p->end_date)->endOfMonth();
                        if ($pEnd->greaterThan($latestDate)) {
                            $latestDate = $pEnd->copy();
                        }
                    }
                }
            }
            
            $projectStartDate = $earliestDate;
            $currentDate = $projectStartDate->copy();
            
            // Loop sampai currentDate melewati latestDate
            while ($currentDate->lessThanOrEqualTo($latestDate)) {
                $projectPeriods[] = [
                    'label' => $currentDate->format('M Y'),
                    'sub_label' => $currentDate->format('Y'),
                    'start' => $currentDate->copy()->startOfMonth(),
                    'end' => $currentDate->copy()->endOfMonth(),
                ];
                $currentDate->addMonth();
            }
            
            // Minimal 6 bulan agar tampilan tidak terlalu kosong
            while (count($projectPeriods) < 6) {
                $projectPeriods[] = [
                    'label' => $currentDate->format('M Y'),
                    'sub_label' => $currentDate->format('Y'),
                    'start' => $currentDate->copy()->startOfMonth(),
                    'end' => $currentDate->copy()->endOfMonth(),
                ];
                $currentDate->addMonth();
            }
        }
        
        if (count($projectPeriods) > 0) {
            $projectEndDate = $projectPeriods[count($projectPeriods) - 1]['end'];
            $projectTotalDuration = $projectStartDate->diffInMinutes($projectEndDate);
            if ($projectTotalDuration <= 0) $projectTotalDuration = 1;
        }

        return [
            'projects' => $projects,
            'machines' => $machines,
            'stasionStartDate' => $startDate,
            'stasionDaysCount' => $daysCount,
            'machineTypes' => $machineTypes,
            'projectPeriods' => $projectPeriods,
            'projectStartDate' => $projectStartDate,
            'projectTotalDuration' => $projectTotalDuration,
        ];
    }

    public function createScheduleAction(): Action
    {
        return Action::make('createSchedule')
            ->label('Tambah Jadwal')
            ->icon('heroicon-o-plus')
            ->modalHeading('Tambah Jadwal Mesin (Wizard)')
            ->modalWidth(MaxWidth::FourExtraLarge)
            ->modalSubmitActionLabel('Selesai & Simpan')
            ->form([
                \Filament\Forms\Components\Wizard::make([
                    \Filament\Forms\Components\Wizard\Step::make('Pilih Proyek & Mesin')
                        ->schema([
                            Select::make('project_id')
                                ->label('Proyek')
                                ->options(\App\Models\Project::pluck('code', 'id'))
                                ->searchable()
                                ->required(),
                            Select::make('machine_id')
                                ->label('Mesin')
                                ->options(\App\Models\Machine::pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                            Select::make('component_id')
                                ->label('Komponen (Opsional)')
                                ->options(\App\Models\Component::pluck('name', 'id'))
                                ->searchable(),
                        ]),
                    \Filament\Forms\Components\Wizard\Step::make('Detail Operasi & Waktu')
                        ->schema([
                            TextInput::make('operation_type')
                                ->label('Jenis Operasi (cth: CNC, EDM)')
                                ->required(),
                            \Filament\Forms\Components\DateTimePicker::make('start_time')
                                ->label('Waktu Mulai')
                                ->required(),
                            \Filament\Forms\Components\DateTimePicker::make('end_time')
                                ->label('Waktu Selesai')
                                ->required(),
                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'pending' => 'Pending',
                                    'active' => 'Aktif',
                                    'completed' => 'Selesai',
                                ])
                                ->default('pending')
                                ->required(),
                        ]),
                ])
                ->submitAction(new \Illuminate\Support\HtmlString('<button type="submit" class="filament-button inline-flex items-center justify-center font-medium tracking-tight border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset rounded-lg bg-primary-600 text-white hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700 h-9 px-4">Simpan Jadwal</button>'))
            ])
            ->action(function (array $data) {
                \App\Models\MachineOperationRecord::create([
                    'project_id' => $data['project_id'],
                    'machine_id' => $data['machine_id'],
                    'component_id' => $data['component_id'] ?? null,
                    'operation_type' => $data['operation_type'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'status' => $data['status'],
                    'user_id' => auth()->id() ?? 1,
                ]);
            })
            ->successNotificationTitle('Jadwal baru berhasil ditambahkan');
    }

    public function editSingleScheduleAction(): Action
    {
        return Action::make('editSingleSchedule')
            ->modalHeading('Edit Detail Jadwal Operasi')
            ->modalWidth(MaxWidth::TwoExtraLarge)
            ->modalSubmitActionLabel('Simpan Perubahan')
            ->fillForm(function (array $arguments): array {
                $record = \App\Models\MachineOperationRecord::find($arguments['record']);
                return $record ? $record->toArray() : [];
            })
            ->form([
                \Filament\Forms\Components\Grid::make(2)->schema([
                    Select::make('project_id')
                        ->label('Proyek')
                        ->options(\App\Models\Project::pluck('code', 'id'))
                        ->searchable()
                        ->required(),
                    Select::make('machine_id')
                        ->label('Stasiun Mesin')
                        ->options(\App\Models\Machine::pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Select::make('component_id')
                        ->label('Komponen (Opsional)')
                        ->options(\App\Models\Component::pluck('name', 'id'))
                        ->searchable(),
                    TextInput::make('operation_type')
                        ->label('Jenis Operasi (cth: CNC, Wirecut)')
                        ->required(),
                    \Filament\Forms\Components\DateTimePicker::make('start_time')
                        ->label('Waktu Mulai')
                        ->required(),
                    \Filament\Forms\Components\DateTimePicker::make('end_time')
                        ->label('Waktu Selesai')
                        ->required(),
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending' => 'Pending',
                            'active' => 'Aktif',
                            'completed' => 'Selesai',
                        ])
                        ->required(),
                ])
            ])
            ->extraModalFooterActions([
                Action::make('delete')
                    ->label('Hapus Jadwal')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Jadwal Operasi?')
                    ->modalDescription('Apakah Anda yakin ingin menghapus jadwal ini? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->action(function (array $arguments, Action $action) {
                        $record = \App\Models\MachineOperationRecord::find($arguments['record']);
                        if ($record) {
                            $record->delete();
                            \Filament\Notifications\Notification::make()
                                ->title('Jadwal berhasil dihapus')
                                ->success()
                                ->send();
                        }
                        $action->cancel();
                    })
            ])
            ->action(function (array $data, array $arguments) {
                $record = \App\Models\MachineOperationRecord::find($arguments['record']);
                if ($record) {
                    $record->update($data);
                }
            })
            ->successNotificationTitle('Jadwal berhasil diperbarui');
    }

    public function editScheduleAction(): Action
    {
        return Action::make('editSchedule')
            ->modalHeading(fn (array $arguments) => 'Edit Jadwal: ' . Project::find($arguments['project'] ?? null)?->code)
            ->modalWidth(MaxWidth::SevenExtraLarge)
            ->modalSubmitActionLabel('Simpan Jadwal')
            ->fillForm(function (array $arguments): array {
                $project = Project::with('phases')->find($arguments['project']);
                return [
                    'phases' => $project ? $project->phases->toArray() : [],
                ];
            })
            ->form([
                Repeater::make('phases')
                    ->label('')
                    ->schema([
                        Select::make('name')
                            ->label('Nama Fase')
                            ->options([
                                'Design' => 'Design',
                                'Machining' => 'Machining',
                                'Assembly' => 'Assembly',
                                'Trial' => 'Trial',
                                'QC' => 'QC',
                            ])
                            ->required(),
                        DatePicker::make('start_date')
                            ->label('Mulai')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('Selesai')
                            ->required(),
                        TextInput::make('estimated_hours')
                            ->label('Estimasi Jam')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        TextInput::make('progress')
                            ->label('Progress (%)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'active' => 'Aktif',
                                'completed' => 'Selesai',
                            ])
                            ->default('pending')
                            ->required(),
                    ])
                    ->columns(6)
                    ->collapsible()
                    ->defaultItems(0)
            ])
            ->action(function (array $data, array $arguments) {
                $project = Project::find($arguments['project']);
                if ($project) {
                    $project->phases()->delete();
                    if (!empty($data['phases'])) {
                        $project->phases()->createMany($data['phases']);
                    }
                }
            })
            ->successNotificationTitle('Jadwal berhasil diperbarui');
    }

    public function updatePhaseDates($phaseId, $minutesShifted)
    {
        $phase = \App\Models\ProjectPhase::find($phaseId);
        \Log::info("updatePhaseDates called for Phase: {$phaseId}, minutesShifted: {$minutesShifted}");
        if ($phase && $phase->start_date && $phase->end_date) {
            $oldStart = $phase->start_date;
            $phase->update([
                'start_date' => \Carbon\Carbon::parse($phase->start_date)->addMinutes($minutesShifted),
                'end_date' => \Carbon\Carbon::parse($phase->end_date)->addMinutes($minutesShifted),
            ]);
            \Log::info("Phase {$phaseId} updated from {$oldStart} to {$phase->start_date}");
            
            \Filament\Notifications\Notification::make()
                ->title('Jadwal fase berhasil digeser')
                ->success()
                ->send();
        } else {
            \Log::info("Phase {$phaseId} failed to update. Exists? " . ($phase ? 'Yes' : 'No'));
        }
    }

    public function updateRecordShift($recordId, $machineId, $newStartTime, $segmentStartStr = null, $segmentEndStr = null)
    {
        $record = \App\Models\MachineOperationRecord::find($recordId);
        if (!$record) return;

        $newStart = \Carbon\Carbon::parse($newStartTime);

        // Jika segment tidak dikirim (fallback), pindahkan seluruh record
        if (!$segmentStartStr || !$segmentEndStr) {
            $duration = \Carbon\Carbon::parse($record->start_time)->diffInMinutes($record->end_time);
            $record->update([
                'machine_id' => $machineId,
                'start_time' => $newStart,
                'end_time' => $newStart->copy()->addMinutes($duration),
            ]);
            return;
        }

        $shiftStart = \Carbon\Carbon::parse($segmentStartStr);
        $shiftEnd = \Carbon\Carbon::parse($segmentEndStr);
        
        // Tentukan bagian record yang benar-benar ada di dalam shift ini
        $draggedStart = $record->start_time->greaterThan($shiftStart) ? $record->start_time : $shiftStart;
        $draggedEnd = $record->end_time->lessThan($shiftEnd) ? $record->end_time : $shiftEnd;
        
        $draggedDurationMinutes = $draggedStart->diffInMinutes($draggedEnd);
        if ($draggedDurationMinutes <= 0) return;

        $newEnd = $newStart->copy()->addMinutes($draggedDurationMinutes);

        // Jika record SEPENUHNYA ada di dalam shift ini (tidak perlu split)
        if ($record->start_time->greaterThanOrEqualTo($shiftStart) && $record->end_time->lessThanOrEqualTo($shiftEnd)) {
            $record->update([
                'machine_id' => $machineId,
                'start_time' => $newStart,
                'end_time' => $newEnd,
            ]);
        } else {
            // SPLIT RECORD
            
            // 1. Buat record baru untuk porsi yang dipindahkan (dragged part)
            $newRecord = $record->replicate();
            $newRecord->machine_id = $machineId;
            $newRecord->start_time = $newStart;
            $newRecord->end_time = $newEnd;
            $newRecord->save();
            
            // 2. Modifikasi record lama
            if ($draggedStart->equalTo($record->start_time)) {
                // Potong depan (Start time digeser maju)
                $record->update(['start_time' => $draggedEnd]);
            } elseif ($draggedEnd->equalTo($record->end_time)) {
                // Potong belakang (End time dimundurkan)
                $record->update(['end_time' => $draggedStart]);
            } else {
                // Potong tengah (Hole) -> Buat record ekor baru, record asli jadi kepala
                $tailRecord = $record->replicate();
                $tailRecord->start_time = $draggedEnd;
                $tailRecord->save();
                
                $record->update(['end_time' => $draggedStart]);
            }
        }
        
        \Filament\Notifications\Notification::make()
            ->title('Jadwal berhasil dipindahkan')
            ->success()
            ->send();
    }

    public function editMachineScheduleAction(): Action
    {
        return Action::make('editMachineSchedule')
            ->modalHeading(fn (array $arguments) => 'Edit Jadwal Mesin: ' . \App\Models\Machine::find($arguments['machine'] ?? null)?->name)
            ->modalWidth(MaxWidth::SevenExtraLarge)
            ->modalSubmitActionLabel('Simpan Jadwal')
            ->fillForm(function (array $arguments): array {
                $machine = \App\Models\Machine::with(['operationRecords' => function($q) {
                    // Hanya tampilkan yang aktif/pending atau yang di masa depan
                    $q->where('status', '!=', 'completed')->orderBy('start_time');
                }])->find($arguments['machine']);
                
                return [
                    'records' => $machine ? $machine->operationRecords->toArray() : [],
                ];
            })
            ->form([
                Repeater::make('records')
                    ->label('')
                    ->schema([
                        Select::make('project_id')
                            ->label('Proyek')
                            ->options(\App\Models\Project::pluck('code', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('component_id')
                            ->label('Komponen')
                            ->options(\App\Models\Component::pluck('name', 'id'))
                            ->searchable(),
                        TextInput::make('operation_type')
                            ->label('Jenis Operasi (cth: CNC, EDM)')
                            ->required(),
                        \Filament\Forms\Components\DateTimePicker::make('start_time')
                            ->label('Waktu Mulai')
                            ->required(),
                        \Filament\Forms\Components\DateTimePicker::make('end_time')
                            ->label('Waktu Selesai')
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'active' => 'Aktif',
                                'completed' => 'Selesai',
                            ])
                            ->default('pending')
                            ->required(),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->defaultItems(0)
            ])
            ->action(function (array $data, array $arguments) {
                $machine = \App\Models\Machine::find($arguments['machine']);
                if ($machine) {
                    $existingIds = collect($data['records'])->pluck('id')->filter()->toArray();
                    
                    // Hapus data jadwal pending/aktif yang dihapus dari form
                    $machine->operationRecords()
                        ->where('status', '!=', 'completed')
                        ->whereNotIn('id', $existingIds)
                        ->delete();
                        
                    foreach ($data['records'] as $recordData) {
                        if (!empty($recordData['id'])) {
                            $machine->operationRecords()->where('id', $recordData['id'])->update([
                                'project_id' => $recordData['project_id'],
                                'component_id' => $recordData['component_id'],
                                'operation_type' => $recordData['operation_type'],
                                'start_time' => $recordData['start_time'],
                                'end_time' => $recordData['end_time'],
                                'status' => $recordData['status'],
                            ]);
                        } else {
                            $machine->operationRecords()->create([
                                'project_id' => $recordData['project_id'],
                                'component_id' => $recordData['component_id'],
                                'operation_type' => $recordData['operation_type'],
                                'start_time' => $recordData['start_time'],
                                'end_time' => $recordData['end_time'],
                                'status' => $recordData['status'],
                                'user_id' => auth()->id() ?? 1,
                            ]);
                        }
                    }
                }
            })
            ->successNotificationTitle('Jadwal stasion mesin berhasil diperbarui');
    }
}
