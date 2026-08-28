@extends('layouts.admin')

@section('title', 'Notifications')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Notifications</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Notifications</h5>
                <div>
                    <button class="btn btn-sm btn-outline-primary" id="markAllRead">
                        <i class="bi bi-check-all"></i> Mark All As Read
                    </button>
                    <a href="{{ route('admin.notifications.api.preferences') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-gear"></i> Preferences
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                @if($notifications->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($notifications as $notification)
                            <a href="{{ $notification->getRoute() ?? '#' }}" class="list-group-item list-group-item-action {{ $notification->status === 'unread' ? 'active' : '' }}">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <i class="bi {{ $notification->getIcon() }} fs-5"></i>
                                    </div>
                                    <div class="col">
                                        <div class="fw-bold">{{ $notification->getTitle() }}</div>
                                        <small class="text-muted d-block">{{ $notification->getBody() }}</small>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="col-auto">
                                        @if($notification->status === 'unread')
                                            <span class="badge bg-primary rounded-pill">New</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="p-3 d-flex justify-content-center">
                        {{ $notifications->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                        <p class="text-muted mt-2">No notifications yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('markAllRead').addEventListener('click', function() {
    fetch('{{ route("admin.notifications.api.mark-all-read") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>
@endpush
