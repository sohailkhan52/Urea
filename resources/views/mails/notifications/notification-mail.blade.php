<x-mail::message>
# {{ $notification->getTitle() }}

{{ $notification->getBody() }}

@if($notification->getRoute())
<x-mail::button :url="$notification->getRoute()">
View {{ ucfirst($notification->related_type ?? 'Item') }}
</x-mail::button>
@endif

## Notification Details

**Type:** {{ $notification->getTypeLabel() }}  
**Time:** {{ $notification->created_at->format('M d, Y g:i A') }}

@if($notification->warehouse)
**Warehouse:** {{ $notification->warehouse->name }}
@endif

---

You received this notification because you're an administrator in {{ config('app.name') }}.

<x-mail::subcopy>
If you don't want to receive these notifications, you can update your [notification preferences]({{ route('admin.notifications.api.preferences') }}).
</x-mail::subcopy>
</x-mail::message>
