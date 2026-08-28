@props([
    'cards' => [],
])

@if(count($cards) > 0)
<div class="row mb-4">
    @foreach($cards as $card)
    <div class="col-md-{{ $card['col'] ?? '3' }}">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">{{ $card['label'] }}</p>
                        <h3 class="mb-0 {{ $card['valueClass'] ?? '' }}">{{ $card['value'] }}</h3>
                        @if(isset($card['subtext']))
                        <p class="text-muted small mb-0 mt-1">{{ $card['subtext'] }}</p>
                        @endif
                    </div>
                    <div class="p-3 rounded-circle" style="background-color: {{ $card['bgColor'] ?? '#e7f3ff' }}">
                        <i class="bi {{ $card['icon'] ?? 'bi-info-circle' }} fs-4" style="color: {{ $card['iconColor'] ?? '#0066cc' }}"></i>
                    </div>
                </div>
                @if(isset($card['trend']))
                <div class="mt-2">
                    <span class="badge {{ $card['trend'] > 0 ? 'bg-success' : ($card['trend'] < 0 ? 'bg-danger' : 'bg-secondary') }}">
                        <i class="bi {{ $card['trend'] > 0 ? 'bi-arrow-up' : ($card['trend'] < 0 ? 'bi-arrow-down' : 'bi-dash') }}"></i>
                        {{ abs($card['trend']) }}%
                    </span>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
