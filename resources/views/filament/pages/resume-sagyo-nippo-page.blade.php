<x-filament-panels::page>
    @if ($headerWidgets = $this->getVisibleHeaderWidgets())
        <x-filament-widgets::widgets
            :columns="$this->getHeaderWidgetsColumns()"
            :data="$this->getWidgetData()"
            :widgets="$headerWidgets"
            class="fi-page-header-widgets mb-6"
        />
    @endif

    <div class="mt-4">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
