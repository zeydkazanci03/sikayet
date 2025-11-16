@extends('layouts.frontend')

@section('title', $user->name . ' - Profil')

@section('content')
<div class="container py-4">
    <div class="row">
        {{-- Sol Sidebar --}}
        <div class="col-lg-3">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <img src="{{ $user->avatar_url }}" class="rounded-circle mb-3" width="120" height="120" alt="{{ $user->name }}">
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted small mb-3">{{ '@' . $user->username }}</p>

                    @if(auth()->check() && auth()->id() === $user->id)
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                        <i class="bi bi-pencil"></i> Profili Düzenle
                    </a>
                    @else
                    <button class="btn btn-primary btn-sm w-100 mb-2">
                        <i class="bi bi-person-plus"></i> Takip Et
                    </button>
                    @endif

                    <div class="row text-center mt-3">
                        <div class="col-4">
                            <strong class="d-block">{{ $user->complaints_count }}</strong>
                            <small class="text-muted">Şikayet</small>
                        </div>
                        <div class="col-4">
                            <strong class="d-block">{{ $user->followers_count }}</strong>
                            <small class="text-muted">Takipçi</small>
                        </div>
                        <div class="col-4">
                            <strong class="d-block">{{ $user->following_count }}</strong>
                            <small class="text-muted">Takip</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kullanıcı Bilgileri --}}
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="mb-3">Hakkında</h6>
                    @if($user->bio)
                    <p class="small">{{ $user->bio }}</p>
                    @else
                    <p class="text-muted small">Henüz biyografi eklenmemiş.</p>
                    @endif

                    <div class="mt-3">
                        <div class="mb-2">
                            <i class="bi bi-calendar text-muted"></i>
                            <small class="text-muted">Katıldı: {{ $user->created_at->format('F Y') }}</small>
                        </div>
                        @if($user->location)
                        <div class="mb-2">
                            <i class="bi bi-geo-alt text-muted"></i>
                            <small class="text-muted">{{ $user->location }}</small>
                        </div>
                        @endif
                        @if($user->website)
                        <div class="mb-2">
                            <i class="bi bi-link-45deg text-muted"></i>
                            <a href="{{ $user->website }}" target="_blank" class="small">{{ $user->website }}</a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Rozetler --}}
            @if($user->badges && $user->badges->count() > 0)
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Rozetler</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($user->badges as $badge)
                        <span class="badge bg-{{ $badge->color }}" title="{{ $badge->description }}">
                            <i class="bi bi-{{ $badge->icon }}"></i> {{ $badge->name }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Ana İçerik --}}
        <div class="col-lg-9">
            {{-- Sekme Navigasyonu --}}
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#complaints">
                        <i class="bi bi-megaphone"></i> Şikayetler ({{ $user->complaints_count }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#comments">
                        <i class="bi bi-chat"></i> Yorumlar ({{ $user->comments_count }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#activity">
                        <i class="bi bi-activity"></i> Aktiviteler
                    </a>
                </li>
            </ul>

            {{-- Sekme İçeriği --}}
            <div class="tab-content">
                {{-- Şikayetler --}}
                <div class="tab-pane fade show active" id="complaints">
                    @forelse($complaints as $complaint)
                        <x-complaint-card :complaint="$complaint" />
                    @empty
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
                        <p class="mb-0">Henüz şikayet bulunmamaktadır.</p>
                    </div>
                    @endforelse

                    @if($complaints->hasPages())
                    <div class="mt-4">
                        {{ $complaints->links() }}
                    </div>
                    @endif
                </div>

                {{-- Yorumlar --}}
                <div class="tab-pane fade" id="comments">
                    @forelse($comments as $comment)
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">
                                    <a href="{{ route('complaints.show', $comment->complaint) }}">
                                        {{ $comment->complaint->title }}
                                    </a> üzerine yorum yaptı
                                </small>
                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-0">{{ $comment->content }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
                        <p class="mb-0">Henüz yorum yapılmamış.</p>
                    </div>
                    @endforelse

                    @if($comments->hasPages())
                    <div class="mt-4">
                        {{ $comments->links() }}
                    </div>
                    @endif
                </div>

                {{-- Aktiviteler --}}
                <div class="tab-pane fade" id="activity">
                    @forelse($activities as $activity)
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-{{ $activity->icon }} fs-4 text-{{ $activity->color }}"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-1">{{ $activity->description }}</p>
                                    <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
                        <p class="mb-0">Henüz aktivite bulunmamaktadır.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
