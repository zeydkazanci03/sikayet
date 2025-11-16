{{-- Notification Item Komponenti --}}
@props(['notification'])

<div class="list-group-item list-group-item-action {{ $notification->read_at ? '' : 'bg-light' }}">
    <div class="d-flex w-100 justify-content-between align-items-start">
        <div class="flex-grow-1">
            <div class="d-flex align-items-start">
                <div class="me-3">
                    <i class="bi bi-{{ $notification->icon ?? 'bell' }} fs-4 text-{{ $notification->color ?? 'primary' }}"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">{{ $notification->title ?? $notification->data['title'] ?? 'Bildirim' }}</h6>
                    <p class="mb-1 text-muted">{{ $notification->message ?? $notification->data['message'] ?? '' }}</p>
                    <small class="text-muted">
                        <i class="bi bi-clock"></i> {{ $notification->created_at->diffForHumans() }}
                    </small>
                </div>
            </div>
        </div>
        <div class="ms-3">
            <div class="btn-group btn-group-sm">
                @if(!$notification->read_at)
                <form action="{{ route('notifications.mark-read', $notification) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Okundu işaretle">
                        <i class="bi bi-check"></i>
                    </button>
                </form>
                @endif
                <form action="{{ route('notifications.destroy', $notification) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Sil">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @if($notification->action_url ?? $notification->data['action_url'] ?? null)
    <div class="mt-2">
        <a href="{{ $notification->action_url ?? $notification->data['action_url'] }}" class="btn btn-sm btn-primary">
            {{ $notification->action_text ?? $notification->data['action_text'] ?? 'Görüntüle' }} <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    @endif
</div>
