@extends('layouts.frontend')

@section('title', 'Blog')

@section('content')
<div class="container py-4">
    {{-- Sayfa Başlığı --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3"><i class="bi bi-newspaper"></i> Blog</h1>
            <p class="text-muted">Tüketici hakları, şikayet yönetimi ve daha fazlası hakkında yazılar</p>
        </div>
        <div class="col-md-4">
            <x-search-form placeholder="Blog yazılarında ara..." />
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Öne Çıkan Yazı --}}
            @if(isset($featuredPost))
            <div class="card shadow-sm mb-4 border-0">
                <div class="row g-0">
                    <div class="col-md-5">
                        <img src="{{ $featuredPost->featured_image }}" class="img-fluid rounded-start h-100 object-fit-cover" alt="{{ $featuredPost->title }}">
                    </div>
                    <div class="col-md-7">
                        <div class="card-body">
                            <span class="badge bg-primary mb-2">Öne Çıkan</span>
                            <h2 class="h4">
                                <a href="{{ route('blog.show', $featuredPost) }}" class="text-decoration-none text-dark">
                                    {{ $featuredPost->title }}
                                </a>
                            </h2>
                            <p class="text-muted">{{ Str::limit($featuredPost->excerpt, 150) }}</p>
                            <div class="d-flex align-items-center text-muted small">
                                <img src="{{ $featuredPost->author->avatar_url }}" class="rounded-circle me-2" width="30" height="30" alt="{{ $featuredPost->author->name }}">
                                <span>{{ $featuredPost->author->name }}</span>
                                <span class="mx-2">•</span>
                                <span>{{ $featuredPost->created_at->format('d M Y') }}</span>
                                <span class="mx-2">•</span>
                                <span><i class="bi bi-eye"></i> {{ $featuredPost->views_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Blog Yazıları --}}
            <div class="row g-4">
                @forelse($posts as $post)
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm border-0">
                        @if($post->featured_image)
                        <img src="{{ $post->featured_image }}" class="card-img-top" alt="{{ $post->title }}" style="height: 200px; object-fit: cover;">
                        @endif
                        <div class="card-body">
                            <div class="mb-2">
                                <span class="badge bg-light text-dark">{{ $post->category->name }}</span>
                            </div>
                            <h5 class="card-title">
                                <a href="{{ route('blog.show', $post) }}" class="text-decoration-none text-dark">
                                    {{ $post->title }}
                                </a>
                            </h5>
                            <p class="card-text text-muted">{{ Str::limit($post->excerpt, 100) }}</p>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <div class="d-flex align-items-center text-muted small">
                                <img src="{{ $post->author->avatar_url }}" class="rounded-circle me-2" width="25" height="25" alt="{{ $post->author->name }}">
                                <span>{{ $post->author->name }}</span>
                                <span class="ms-auto">{{ $post->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
                        <p class="mb-0">Henüz blog yazısı bulunmamaktadır.</p>
                    </div>
                </div>
                @endforelse
            </div>

            {{-- Sayfalama --}}
            @if($posts->hasPages())
            <div class="mt-4">
                {{ $posts->links() }}
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Kategoriler --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Kategoriler</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($categories as $category)
                        <a href="{{ route('blog.index', ['category' => $category->slug]) }}"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            {{ $category->name }}
                            <span class="badge bg-primary rounded-pill">{{ $category->posts_count }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Popüler Yazılar --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Popüler Yazılar</h6>
                </div>
                <div class="card-body">
                    @foreach($popularPosts as $popular)
                    <div class="mb-3">
                        <a href="{{ route('blog.show', $popular) }}" class="text-decoration-none">
                            <strong class="d-block">{{ Str::limit($popular->title, 50) }}</strong>
                            <small class="text-muted">
                                <i class="bi bi-eye"></i> {{ $popular->views_count }} görüntülenme
                            </small>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Etiketler --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Etiketler</h6>
                </div>
                <div class="card-body">
                    @foreach($tags as $tag)
                    <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" class="badge bg-light text-dark me-1 mb-1">
                        #{{ $tag->name }}
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
