@props(['status'])

<span class="badge {{ $status->badgeClass() }}">
    @if (method_exists($status, 'icon'))
        <i class="bi {{ $status->icon() }} me-1"></i>
    @endif
    {{ $status->label() }}
</span>
