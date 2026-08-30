@php
    $status = $getRecord()->status;
    
    $steps = [
        ['label' => 'Request Opr', 'active' => in_array($status, ['draft', 'sent', 'ordered', 'partial', 'arrived', 'closed', 'cancelled'])],
        ['label' => 'Approve Leader', 'active' => in_array($status, ['sent', 'ordered', 'partial', 'arrived', 'closed'])],
        ['label' => 'Order Admin', 'active' => in_array($status, ['ordered', 'partial', 'arrived', 'closed'])],
        ['label' => 'Barang Datang', 'active' => in_array($status, ['arrived', 'closed'])],
    ];

    $isCancelled = $status === 'cancelled';
@endphp

<div class="flex items-center w-full min-w-[300px] gap-2">
    @foreach ($steps as $index => $step)
        <div class="flex flex-col items-center flex-1 relative">
            <!-- Line connecting to previous step -->
            @if ($index > 0)
                <div class="absolute w-[calc(100%-1.5rem)] h-1 top-3 -left-[calc(50%-0.75rem)] -translate-y-1/2 -z-10
                    {{ $step['active'] ? 'bg-primary-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
            @endif

            <!-- Circle -->
            <div class="z-10 flex items-center justify-center w-6 h-6 rounded-full border-2 
                @if($isCancelled && $step['active'] && ($index === count($steps)-1 || !$steps[$index+1]['active']))
                    border-danger-500 bg-danger-50 text-danger-500
                @elseif($step['active'])
                    border-primary-500 bg-primary-50 text-primary-500 dark:bg-primary-900
                @else
                    border-gray-300 bg-white text-gray-300 dark:border-gray-600 dark:bg-gray-800
                @endif
                ">
                @if($isCancelled && $step['active'] && ($index === count($steps)-1 || !$steps[$index+1]['active']))
                    <x-heroicon-s-x-mark class="w-3 h-3" />
                @elseif($step['active'])
                    <x-heroicon-s-check class="w-3 h-3" />
                @endif
            </div>

            <!-- Label -->
            <div class="mt-2 text-[10px] text-center font-medium
                @if($isCancelled && $step['active'] && ($index === count($steps)-1 || !$steps[$index+1]['active']))
                    text-danger-500
                @elseif($step['active'])
                    text-primary-600 dark:text-primary-400
                @else
                    text-gray-400 dark:text-gray-500
                @endif
                ">
                {{ $step['label'] }}
            </div>
        </div>
    @endforeach
</div>
