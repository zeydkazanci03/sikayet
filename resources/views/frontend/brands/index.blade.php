@extends('layouts.frontend')

@section('title', 'Tüm Markalar')

@section('content')
<div class="container py-4">
    {{-- Sayfa Başlığı --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3"><i class="bi bi-building"></i> Tüm Markalar</h1>
            <p class="text-muted">{{ $brands->total() }} marka listeleniyor</p>
        </div>
        <div class="col-md-4">
            <x-search-form placeholder="Marka ara..." />
        </div>
    </div>

    {{-- Filtreler --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('frontend.brands.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Kategori</label>
                    <select name="category" class="form-select">
                        <option value="">Tüm Kategoriler</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sıralama</label>
                    <select name="sort" class="form-select">
                        <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>En Popüler</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>En Yeni</option>
                        <option value="most_complaints" {{ request('sort') == 'most_complaints' ? 'selected' : '' }}>En Çok Şikayet</option>
                        <option value="alphabetical" {{ request('sort') == 'alphabetical' ? 'selected' : '' }}>A-Z</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Durum</label>
                    <select name="verified" class="form-select">
                        <option value="">Tümü</option>
                        <option value="1" {{ request('verified') == '1' ? 'selected' : '' }}>Onaylı Markalar</option>
                        <option value="0" {{ request('verified') == '0' ? 'selected' : '' }}>Onaylı Değil</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Filtrele
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Alfabetik Filtre --}}
    <div class="mb-4">
        <div class="btn-group flex-wrap" role="group">
            <a href="{{ route('frontend.brands.index') }}" class="btn btn-sm btn-outline-secondary {{ !request('letter') ? 'active' : '' }}">Tümü</a>
            @foreach(range('A', 'Z') as $letter)
            <a href="{{ route('frontend.brands.index', ['letter' => $letter]) }}"
               class="btn btn-sm btn-outline-secondary {{ request('letter') == $letter ? 'active' : '' }}">
                {{ $letter }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- Marka Listesi --}}
    @if($brands->count() > 0)
    <div class="row g-4">
        @foreach($brands as $brand)
        <div class="col-md-6 col-lg-4 col-xl-3">
            <x-brand-card :brand="$brand" />
        </div>
        @endforeach
    </div>

    {{-- Sayfalama --}}
    <div class="mt-4">
        {{ $brands->links() }}
    </div>
    @else
    <div class="alert alert-info text-center">
        <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
        <h5>Marka Bulunamadı</h5>
        <p class="mb-0">Arama kriterlerinize uygun marka bulunamadı.</p>
    </div>
    @endif

    {{-- Popüler Kategoriler --}}
    <div class="card shadow-sm mt-5">
        <div class="card-header bg-white">
            <h5 class="mb-0">Popüler Kategoriler</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($categories as $category)
                <div class="col-md-4 col-lg-3">
                    <a href="{{ route('frontend.brands.index', ['category' => $category->id]) }}"
                       class="btn btn-outline-primary w-100">
                        <i class="bi bi-tag"></i> {{ $category->name }}
                        <span class="badge bg-primary ms-2">{{ $category->brands_count }}</span>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
