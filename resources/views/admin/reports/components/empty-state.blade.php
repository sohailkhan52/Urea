@props([
    'icon' => 'bi-inbox',
    'title' => 'No Data Found',
    'message' => 'No records match your filters. Try adjusting your search criteria.',
])

<div class="text-center py-5">
    <i class="bi {{ $icon }} text-muted" style="font-size: 4rem;"></i>
    <h4 class="mt-3 text-muted">{{ $title }}</h4>
    <p class="text-muted">{{ $message }}</p>
    {{ $slot }}
</div>
