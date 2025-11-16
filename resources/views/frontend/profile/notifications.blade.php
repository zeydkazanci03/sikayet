@extends('layouts.frontend')

@section('title', 'Bildirimler')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            {{-- Sidebar Menü --}}
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-person-circle"></i> Profilim</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('profile.show', auth()->user()) }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-person"></i> Profilim
                    </a>
                    <a href="{{ route('profile.complaints') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-megaphone"></i> Şikayetlerim
                    </a>
                    <a href="{{ route('profile.notifications') }}" class="list-group-item list-group-item-action active">
                        <i class="bi bi-bell"></i> Bildirimler
                    </a>
                    <a href="{{ route('profile.edit') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-gear"></i> Ayarlar
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-bell"></i> Bildirimler
                            @if($unreadCount > 0)
                            <span class="badge bg-danger">{{ $unreadCount }}</span>
                            @endif
                        </h4>
                        <div class="btn-group">
                            @if($unreadCount > 0)
                            <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-check-all"></i> Tümünü Okundu İşaretle
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('notifications.clear-all') }}" method="POST" class="d-inline ms-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tüm bildirimleri silmek istediğinizden emin misiniz?')">
                                    <i class="bi bi-trash"></i> Tümünü Temizle
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    {{-- Filtre Sekmeleri --}}
                    <ul class="nav nav-tabs px-3 pt-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#all">
                                Tümü ({{ $notifications->total() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#unread">
                                Okunmamış ({{ $unreadCount }})
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="all">
                            @forelse($notifications as $notification)
                            <div class="list-group-item list-group-item-action {{ $notification->read_at ? '' : 'bg-light' }}">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                <i class="bi bi-{{ $notification->icon }} fs-4 text-{{ $notification->color }}"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $notification->title }}</h6>
                                                <p class="mb-1 text-muted">{{ $notification->message }}</p>
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
                                @if($notification->action_url)
                                <div class="mt-2">
                                    <a href="{{ $notification->action_url }}" class="btn btn-sm btn-primary">
                                        {{ $notification->action_text ?? 'Görüntüle' }} <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                                @endif
                            </div>
                            @empty
                            <div class="p-5 text-center">
                                <i class="bi bi-bell-slash fs-1 text-muted d-block mb-3"></i>
                                <h5 class="text-muted">Bildirim Yok</h5>
                                <p class="text-muted">Henüz hiç bildiriminiz bulunmamaktadır.</p>
                            </div>
                            @endforelse
                        </div>

                        <div class="tab-pane fade" id="unread">
                            @forelse($notifications->where('read_at', null) as $notification)
                            <x-notification-item :notification="$notification" />
                            @empty
                            <div class="p-5 text-center">
                                <i class="bi bi-check-circle fs-1 text-success d-block mb-3"></i>
                                <h5 class="text-muted">Hepsi Okundu!</h5>
                                <p class="text-muted">Okunmamış bildiriminiz bulunmamaktadır.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Sayfalama --}}
                    @if($notifications->hasPages())
                    <div class="p-3 border-top">
                        {{ $notifications->links() }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- Bildirim Ayarları --}}
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Bildirim Ayarları</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.notification-settings') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="email_notifications" class="form-check-input" id="emailNotif"
                                       {{ auth()->user()->email_notifications ? 'checked' : '' }}>
                                <label class="form-check-label" for="emailNotif">
                                    E-posta ile bildirim al
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="push_notifications" class="form-check-input" id="pushNotif"
                                       {{ auth()->user()->push_notifications ? 'checked' : '' }}>
                                <label class="form-check-label" for="pushNotif">
                                    Push bildirimleri al
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="comment_notifications" class="form-check-input" id="commentNotif"
                                       {{ auth()->user()->comment_notifications ? 'checked' : '' }}>
                                <label class="form-check-label" for="commentNotif">
                                    Yorum bildirimleri
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="response_notifications" class="form-check-input" id="responseNotif"
                                       {{ auth()->user()->response_notifications ? 'checked' : '' }}>
                                <label class="form-check-label" for="responseNotif">
                                    Marka yanıt bildirimleri
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Kaydet
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
