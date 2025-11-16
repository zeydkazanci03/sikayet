@extends('layouts.frontend')

@section('title', $complaint->title)

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8">
            {{-- Şikayet Kartı --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h1 class="h4 mb-2">{{ $complaint->title }}</h1>
                            <div class="text-muted small">
                                <span><i class="bi bi-calendar"></i> {{ $complaint->created_at->diffForHumans() }}</span>
                                <span class="ms-3"><i class="bi bi-eye"></i> {{ $complaint->views_count }} görüntülenme</span>
                            </div>
                        </div>
                        <span class="badge bg-{{ $complaint->status_color }} fs-6">
                            {{ $complaint->status_label }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Marka ve Kategori --}}
                    <div class="mb-3">
                        <a href="{{ route('frontend.brands.show', $complaint->brand) }}" class="text-decoration-none">
                            <img src="{{ $complaint->brand->logo_url }}" alt="{{ $complaint->brand->name }}" class="me-2" style="height: 30px;">
                            <strong>{{ $complaint->brand->name }}</strong>
                        </a>
                        <span class="text-muted ms-2">•</span>
                        <a href="{{ route('categories.show', $complaint->category) }}" class="text-muted ms-2">
                            {{ $complaint->category->name }}
                        </a>
                    </div>

                    {{-- Şikayet İçeriği --}}
                    <div class="complaint-content">
                        {!! nl2br(e($complaint->description)) !!}
                    </div>

                    {{-- Medya Dosyaları --}}
                    @if($complaint->media && $complaint->media->count() > 0)
                    <div class="mt-4">
                        <h6>Ek Dosyalar</h6>
                        <div class="row g-2">
                            @foreach($complaint->media as $media)
                            <div class="col-md-4">
                                <img src="{{ $media->url }}" class="img-fluid rounded" alt="Ek">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Etiketler --}}
                    @if($complaint->tags && $complaint->tags->count() > 0)
                    <div class="mt-3">
                        @foreach($complaint->tags as $tag)
                        <span class="badge bg-light text-dark me-1">#{{ $tag->name }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button class="btn btn-sm btn-outline-success" id="likeBtn">
                                <i class="bi bi-hand-thumbs-up"></i> Beğen ({{ $complaint->likes_count }})
                            </button>
                            <button class="btn btn-sm btn-outline-secondary ms-2">
                                <i class="bi bi-share"></i> Paylaş
                            </button>
                        </div>
                        @auth
                            @if($complaint->user_id === auth()->id())
                            <div>
                                <a href="{{ route('frontend.complaints.edit', $complaint) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Düzenle
                                </a>
                            </div>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>

            {{-- Marka Yanıtı --}}
            @if($complaint->response)
            <div class="card shadow-sm mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-building"></i> Marka Yanıtı
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>{{ $complaint->brand->name }}</strong>
                        <span class="text-muted small ms-2">{{ $complaint->response->created_at->diffForHumans() }}</span>
                    </div>
                    <div>{!! nl2br(e($complaint->response->content)) !!}</div>
                </div>
            </div>
            @endif

            {{-- Yorumlar --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Yorumlar ({{ $complaint->comments_count }})</h5>
                </div>
                <div class="card-body">
                    @auth
                    <form action="{{ route('comments.store', $complaint) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <textarea name="content" class="form-control" rows="3" placeholder="Yorumunuzu yazın..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Yorum Yap
                        </button>
                    </form>
                    @else
                    <div class="alert alert-info">
                        Yorum yapmak için <a href="{{ route('login') }}">giriş yapın</a>.
                    </div>
                    @endauth

                    {{-- Yorum Listesi --}}
                    @forelse($complaint->comments as $comment)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex">
                            <img src="{{ $comment->user->avatar_url }}" class="rounded-circle me-3" width="40" height="40" alt="{{ $comment->user->name }}">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $comment->user->name }}</strong>
                                    <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-1">{{ $comment->content }}</p>
                                @auth
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-link text-muted p-0 me-2">
                                        <i class="bi bi-hand-thumbs-up"></i> {{ $comment->likes_count }}
                                    </button>
                                    <button class="btn btn-link text-muted p-0">Yanıtla</button>
                                </div>
                                @endauth
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center">Henüz yorum yapılmamış.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Şikayet Sahibi --}}
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="card-title">Şikayet Sahibi</h6>
                    <div class="d-flex align-items-center">
                        <img src="{{ $complaint->user->avatar_url }}" class="rounded-circle me-2" width="50" height="50" alt="{{ $complaint->user->name }}">
                        <div>
                            <strong>{{ $complaint->user->name }}</strong>
                            <div class="text-muted small">{{ $complaint->user->complaints_count }} şikayet</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Benzer Şikayetler --}}
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Benzer Şikayetler</h6>
                    @foreach($similarComplaints as $similar)
                    <div class="mb-3">
                        <a href="{{ route('frontend.complaints.show', $similar) }}" class="text-decoration-none">
                            <strong class="d-block">{{ Str::limit($similar->title, 60) }}</strong>
                            <small class="text-muted">{{ $similar->brand->name }} • {{ $similar->created_at->diffForHumans() }}</small>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('likeBtn')?.addEventListener('click', function() {
    fetch('{{ route("complaints.like", $complaint) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    }).then(response => response.json())
      .then(data => {
          if(data.success) {
              this.innerHTML = `<i class="bi bi-hand-thumbs-up-fill"></i> Beğenildi (${data.likes_count})`;
          }
      });
});
</script>
@endpush
