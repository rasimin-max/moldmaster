@php
    $state = $getState();
    $imageUrl = $state ? \Illuminate\Support\Facades\Storage::disk('public')->url($state) : null;
@endphp

@if($imageUrl)
    <div 
        x-data="{ show: false }"
        @mouseenter="show = true"
        @mouseleave="show = false"
        style="position: relative; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px;"
    >
        <!-- Thumbnail in table -->
        <img src="{{ $imageUrl }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #e5e7eb;" alt="Foto">
        
        <!-- Large Hover Image -->
        <template x-if="show">
            <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999; pointer-events: none; display: flex; align-items: center; justify-content: center;">
                <div style="background-color: rgba(255, 255, 255, 0.95); padding: 8px; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                    <img src="{{ $imageUrl }}" style="width: 300px; height: 300px; border-radius: 8px; object-fit: contain;" alt="Foto Besar">
                </div>
            </div>
        </template>
    </div>
@else
    <span style="color: #9ca3af;">-</span>
@endif
