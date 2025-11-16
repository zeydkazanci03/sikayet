@extends('layouts.frontend')

@section('title', $post->title)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- Yazı Başlığı --}}
            <article class="card shadow-sm mb-4">
                <div class="card-body">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Ana Sayfa</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('frontend.blog.index') }}">Blog</a></li>
                            <li class="breadcrumb-item active">{{ $post->title }}</li>
                        </ol>
                    </nav>

                    <h1 class="h2 mb-3">{{ $post->title }}</h1>

                    {{-- Yazar ve Tarih --}}
                    <div class="d-flex align-items-center mb-4">
                        <img src="{{ $post->author->avatar_url }}" class="rounded-circle me-3" width="50" height="50" alt="{{ $post->author->name }}">
                        <div>
                            <strong class="d-block">{{ $post->author->name }}</strong>
                            <small class="text-muted">
                                {{ $post->created_at->format('d F Y') }} • {{ $post->read_time }} dk okuma
                            </small>
                        </div>
                        <div class="ms-auto">
                            <button class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-share"></i> Paylaş
                            </button>
                        </div>
                    </div>

                    {{-- Öne Çıkan Görsel --}}
                    @if($post->featured_image)
                    <img src="{{ $post->featured_image }}" class="img-fluid rounded mb-4" alt="{{ $post->title }}">
                    @endif

                    {{-- İçerik --}}
                    <div class="post-content">
                        {!! $post->content !!}
                    </div>

                    {{-- Etiketler --}}
                    @if($post->tags && $post->tags->count() > 0)
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="text-muted">Etiketler:</h6>
                        @foreach($post->tags as $tag)
                        <a href="{{ route('frontend.blog.index', ['tag' => $tag->slug]) }}" class="badge bg-light text-dark me-1">
                            #{{ $tag->name }}
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Yazar Bilgisi --}}
                <div class="card-footer bg-light">
                    <div class="d-flex align-items-center">
                        <img src="{{ $post->author->avatar_url }}" class="rounded-circle me-3" width="60" height="60" alt="{{ $post->author->name }}">
                        <div>
                            <strong class="d-block">{{ $post->author->name }}</strong>
                            <p class="text-muted mb-0 small">{{ $post->author->bio }}</p>
                        </div>
                    </div>
                </div>
            </article>

            {{-- Yorumlar --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Yorumlar ({{ $post->comments_count }})</h5>
                </div>
                <div class="card-body">
                    @auth
                    <form action="{{ route('frontend.blog.comments.store', $post) }}" method="POST" class="mb-4">
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
                    @forelse($post->comments as $comment)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex">
                            <img src="{{ $comment->user->avatar_url }}" class="rounded-circle me-3" width="40" height="40" alt="{{ $comment->user->name }}">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $comment->user->name }}</strong>
                                    <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-0">{{ $comment->content }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center">Henüz yorum yapılmamış. İlk yorumu siz yapın!</p>
                    @endforelse
                </div>
            </div>

            {{-- İlgili Yazılar --}}
            @if($relatedPosts->count() > 0)
            <div class="mt-5">
                <h4 class="mb-4">İlgili Yazılar</h4>
                <div class="row g-4">
                    @foreach($relatedPosts as $related)
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm">
                            @if($related->featured_image)
                            <img src="{{ $related->featured_image }}" class="card-img-top" alt="{{ $related->title }}" style="height: 150px; object-fit: cover;">
                            @endif
                            <div class="card-body">
                                <h6 class="card-title">
                                    <a href="{{ route('frontend.blog.show', $related) }}" class="text-decoration-none text-dark">
                                        {{ Str::limit($related->title, 60) }}
                                    </a>
                                </h6>
                                <p class="card-text text-muted small">{{ Str::limit($related->excerpt, 80) }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Popüler Yazılar --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Popüler Yazılar</h6>
                </div>
                <div class="card-body">
                    @foreach($popularPosts as $popular)
                    <div class="mb-3">
                        <a href="{{ route('frontend.blog.show', $popular) }}" class="text-decoration-none">
                            <strong class="d-block">{{ Str::limit($popular->title, 50) }}</strong>
                            <small class="text-muted">{{ $popular->created_at->format('d M Y') }}</small>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Kategoriler --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Kategoriler</h6>
                </div>
                <div class="card-body">
                    @foreach($categories as $category)
                    <a href="{{ route('frontend.blog.index', ['category' => $category->slug]) }}"
                       class="d-block mb-2 text-decoration-none">
                        {{ $category->name }}
                        <span class="badge bg-light text-dark float-end">{{ $category->posts_count }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.post-content {
    font-size: 1.1rem;
    line-height: 1.8;
}
.post-content h2, .post-content h3, .post-content h4 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}
.post-content p {
    margin-bottom: 1rem;
}
.post-content img {
    max-width: 100%;
    height: auto;
    margin: 1rem 0;
}
</style>
@endpush
