<div class="space-y-6">
    {{-- Status Timeline --}}
    <div class="border-l-2 border-primary-500 pl-4 space-y-4">
        <div>
            <p class="text-sm text-gray-500">Dilaporkan pada: {{ $record->reported_at?->format('d M Y H:i') ?? $record->created_at->format('d M Y H:i') }}</p>
            <p class="font-medium">Status: <span class="text-warning-600">Pending / Open</span></p>
        </div>

        @if($record->started_at)
        <div>
            <p class="text-sm text-gray-500">Diproses pada: {{ $record->started_at->format('d M Y H:i') }}</p>
            <p class="font-medium">Status: <span class="text-primary-600">In Progress</span> oleh {{ $record->technician?->name ?? '-' }}</p>
            @if($record->target_due_date)
            <p class="text-sm text-gray-500">Target Selesai: {{ $record->target_due_date->format('d M Y H:i') }}</p>
            @endif
        </div>
        @endif

        @if($record->completed_at)
        <div>
            <p class="text-sm text-gray-500">Selesai diperbaiki pada: {{ $record->completed_at->format('d M Y H:i') }}</p>
            <p class="font-medium">Status: <span class="text-warning-600">Need Verification</span></p>
            <p class="text-sm text-gray-500">Downtime: {{ number_format($record->downtime_hours, 2) }} jam</p>
        </div>
        @endif

        @if($record->verified_at)
        <div>
            <p class="text-sm text-gray-500">Diverifikasi pada: {{ $record->verified_at->format('d M Y H:i') }}</p>
            <p class="font-medium">Status: <span class="text-success-600">Closed</span></p>
            <p class="text-sm text-gray-500">Verifikator: {{ $record->verifier?->name ?? '-' }}</p>
        </div>
        @endif
    </div>

    {{-- Detail Countermeasure --}}
    @if($record->temporary_action || $record->permanent_countermeasure)
    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg space-y-4">
        <h3 class="font-bold text-lg">Action Plan & Countermeasure</h3>
        
        <div>
            <h4 class="font-medium text-sm text-gray-500">Temporary Action</h4>
            <p>{{ $record->temporary_action ?? '-' }}</p>
        </div>

        <div>
            <h4 class="font-medium text-sm text-gray-500">Root Cause Analysis</h4>
            <ul class="list-disc list-inside text-sm mt-1">
                <li><strong>Man:</strong> {{ $record->rca_man ?? '-' }}</li>
                <li><strong>Machine:</strong> {{ $record->rca_machine ?? '-' }}</li>
                <li><strong>Material:</strong> {{ $record->rca_material ?? '-' }}</li>
                <li><strong>Method:</strong> {{ $record->rca_method ?? '-' }}</li>
            </ul>
        </div>

        <div>
            <h4 class="font-medium text-sm text-gray-500">Permanent Countermeasure</h4>
            <p>{{ $record->permanent_countermeasure ?? '-' }}</p>
        </div>

        <div>
            <h4 class="font-medium text-sm text-gray-500">Sparepart Diganti</h4>
            <p>{{ $record->replaced_parts_note ?? '-' }}</p>
        </div>
        
        @if($record->photo_after)
        <div>
            <h4 class="font-medium text-sm text-gray-500">Foto Setelah Perbaikan</h4>
            <div class="mt-2">
                <img src="{{ Storage::disk('cloudinary')->url($record->photo_after) }}" alt="Foto After" class="max-w-xs rounded-lg shadow-sm border">
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Catatan Pengawas --}}
    @if($record->notes)
    <div class="bg-warning-50 dark:bg-warning-900/20 p-4 rounded-lg border border-warning-200 dark:border-warning-800">
        <h4 class="font-medium text-warning-800 dark:text-warning-300">Catatan Pengawas (Verifikator)</h4>
        <p class="text-sm mt-1 text-warning-700 dark:text-warning-400">{{ $record->notes }}</p>
    </div>
    @endif
</div>
