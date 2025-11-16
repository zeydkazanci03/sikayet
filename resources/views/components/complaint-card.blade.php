{{-- Şikayet Kartı Komponenti --}}
@props(['complaint'])

<div class="card shadow-sm mb-3 complaint-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="card-title mb-0">
                <a href="{{ route('complaints.show', $complaint) }}" class="text-decoration-none text-dark">
                    {{ $complaint->title }}
                </a>
            </h5>
            <span class="badge bg-{{ $complaint->status_color }} ms-2">
                {{ $complaint->status_label }}
            </span>
        </div>

        {{-- Marka ve Kategori --}}
        <div class="mb-2">
            <a href="{{ route('brands.show', $complaint->brand) }}" class="text-decoration-none">
                <img src="{{ $complaint->brand->logo_url }}" alt="{{ $complaint->brand->name }}"
                     class="me-1" style="height: 20px;">
                <strong>{{ $complaint->brand->name }}</strong>
            </a>
            <span class="text-muted mx-1">•</span>
            <a href="{{ route('categories.show', $complaint->category) }}" class="text-muted">
                {{ $complaint->category->name }}
            </a>
        </div>

        {{-- Açıklama --}}
        <p class="card-text text-muted">
            {{ Str::limit($complaint->description, 200) }}
        </p>

        {{-- Marka Yanıtı --}}
        @if($complaint->response)
        <div class="alert alert-success alert-sm mb-2">
            <i class="bi bi-building"></i>
            <strong>Marka Yanıt Verdi</strong>
        </div>
        @endif

        {{-- Alt Bilgiler --}}
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                {{-- Kullanıcı --}}
                <a href="{{ route('profile.show', $complaint->user) }}" class="text-muted text-decoration-none">
                    <img src="{{ $complaint->user->avatar_url }}" class="rounded-circle" width="20" height="20" alt="{{ $complaint->user->name }}">
                    {{ $complaint->is_anonymous ? 'Anonim' : $complaint->user->name }}
                </a>
                <span class="mx-1">•</span>
                <span title="{{ $complaint->created_at->format('d.m.Y H:i') }}">
                    {{ $complaint->created_at->diffForHumans() }}
                </span>
            </div>

            <div class="text-muted small">
                <span title="Görüntülenme">
                    <i class="bi bi-eye"></i> {{ $complaint->views_count }}
                </span>
                <span class="ms-2" title="Beğeni">
                    <i class="bi bi-hand-thumbs-up"></i> {{ $complaint->likes_count }}
                </span>
                <span class="ms-2" title="Yorum">
                    <i class="bi bi-chat"></i> {{ $complaint->comments_count }}
                </span>
            </div>
        </div>

        {{-- Etiketler --}}
        @if($complaint->tags && $complaint->tags->count() > 0)
        <div class="mt-2">
            @foreach($complaint->tags->take(3) as $tag)
            <a href="{{ route('tags.show', $tag) }}" class="badge bg-light text-dark text-decoration-none me-1">
                #{{ $tag->name }}
            </a>
            @endforeach
        </div>
        @endif
    </div>

    @if(isset($showActions) && $showActions)
    <div class="card-footer bg-white">
        <div class="btn-group btn-group-sm">
            <a href="{{ route('complaints.show', $complaint) }}" class="btn btn-outline-primary">
                <i class="bi bi-eye"></i> Görüntüle
            </a>
            @auth
                @if($complaint->user_id === auth()->id())
                <a href="{{ route('complaints.edit', $complaint) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-pencil"></i> Düzenle
                </a>
                @endif
            @endauth
        </div>
    </div>
    @endif
</div>
