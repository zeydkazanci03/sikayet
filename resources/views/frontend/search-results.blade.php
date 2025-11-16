@extends('layouts.frontend')

@section('title', 'Arama Sonuçları - ' . request('q'))

@section('content')
<div class="container py-4">
    {{-- Arama Başlığı --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3"><i class="bi bi-search"></i> Arama Sonuçları</h1>
            <p class="text-muted">
                "<strong>{{ request('q') }}</strong>" için {{ $totalResults }} sonuç bulundu
            </p>
        </div>
        <div class="col-md-4">
            <x-search-form placeholder="Yeni arama..." value="{{ request('q') }}" />
        </div>
    </div>

    {{-- Filtre Sekmeleri --}}
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ !request('type') || request('type') == 'all' ? 'active' : '' }}"
               href="{{ route('search', ['q' => request('q'), 'type' => 'all']) }}">
                Tümü ({{ $totalResults }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('type') == 'complaints' ? 'active' : '' }}"
               href="{{ route('search', ['q' => request('q'), 'type' => 'complaints']) }}">
                Şikayetler ({{ $complaintsCount }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('type') == 'brands' ? 'active' : '' }}"
               href="{{ route('search', ['q' => request('q'), 'type' => 'brands']) }}">
                Markalar ({{ $brandsCount }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('type') == 'blog' ? 'active' : '' }}"
               href="{{ route('search', ['q' => request('q'), 'type' => 'blog']) }}">
                Blog ({{ $blogCount }})
            </a>
        </li>
    </ul>

    <div class="row">
        <div class="col-lg-8">
            {{-- Şikayetler --}}
            @if((!request('type') || request('type') == 'all' || request('type') == 'complaints') && $complaints && $complaints->count() > 0)
            <div class="mb-5">
                <h4 class="mb-3"><i class="bi bi-megaphone"></i> Şikayetler</h4>
                @foreach($complaints as $complaint)
                    <x-complaint-card :complaint="$complaint" />
                @endforeach

                @if($complaints->hasPages() && request('type') == 'complaints')
                <div class="mt-3">
                    {{ $complaints->appends(['q' => request('q')])->links() }}
                </div>
                @endif
            </div>
            @endif

            {{-- Markalar --}}
            @if((!request('type') || request('type') == 'all' || request('type') == 'brands') && $brands && $brands->count() > 0)
            <div class="mb-5">
                <h4 class="mb-3"><i class="bi bi-building"></i> Markalar</h4>
                <div class="row g-4">
                    @foreach($brands as $brand)
                    <div class="col-md-6 col-lg-4">
                        <x-brand-card :brand="$brand" />
                    </div>
                    @endforeach
                </div>

                @if($brands->hasPages() && request('type') == 'brands')
                <div class="mt-3">
                    {{ $brands->appends(['q' => request('q')])->links() }}
                </div>
                @endif
            </div>
            @endif

            {{-- Blog --}}
            @if((!request('type') || request('type') == 'all' || request('type') == 'blog') && $blogPosts && $blogPosts->count() > 0)
            <div class="mb-5">
                <h4 class="mb-3"><i class="bi bi-newspaper"></i> Blog Yazıları</h4>
                @foreach($blogPosts as $post)
                <div class="card mb-3">
                    <div class="row g-0">
                        @if($post->featured_image)
                        <div class="col-md-3">
                            <img src="{{ $post->featured_image }}" class="img-fluid rounded-start h-100 object-fit-cover" alt="{{ $post->title }}">
                        </div>
                        @endif
                        <div class="col-md-{{ $post->featured_image ? '9' : '12' }}">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="{{ route('blog.show', $post) }}" class="text-decoration-none text-dark">
                                        {{ $post->title }}
                                    </a>
                                </h5>
                                <p class="card-text text-muted">{{ Str::limit($post->excerpt, 150) }}</p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        {{ $post->author->name }} • {{ $post->created_at->format('d M Y') }}
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                @if($blogPosts->hasPages() && request('type') == 'blog')
                <div class="mt-3">
                    {{ $blogPosts->appends(['q' => request('q')])->links() }}
                </div>
                @endif
            </div>
            @endif

            {{-- Sonuç Bulunamadı --}}
            @if($totalResults == 0)
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
                <h5>Sonuç Bulunamadı</h5>
                <p class="mb-0">
                    "<strong>{{ request('q') }}</strong>" için hiçbir sonuç bulunamadı.
                    Lütfen farklı anahtar kelimeler deneyin.
                </p>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Popüler Aramalar --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Popüler Aramalar</h6>
                </div>
                <div class="card-body">
                    @foreach($popularSearches as $search)
                    <a href="{{ route('search', ['q' => $search]) }}" class="badge bg-light text-dark me-1 mb-2">
                        {{ $search }}
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Popüler Kategoriler --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Popüler Kategoriler</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($popularCategories as $category)
                        <a href="{{ route('categories.show', $category) }}" class="list-group-item list-group-item-action">
                            {{ $category->name }}
                            <span class="badge bg-primary float-end">{{ $category->complaints_count }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
