@extends('layouts.app')

@section('title', 'Anasayfa')

@section('content')
<div class="container">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 mb-3">Türkiye'nin Şikayet Platformu</h1>
            <p class="lead mb-4">Markalardan şikayet et, çözüm al, güvenle alışveriş yap</p>
            @auth
                <a href="{{ route('frontend.complaints.create') }}" class="btn btn-primary btn-lg me-2">
                    <i class="fas fa-plus"></i> Şikayet Yaz
                </a>
            @else
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg me-2">
                    <i class="fas fa-user-plus"></i> Kayıt Ol
                </a>
            @endauth
            <a href="{{ route('frontend.complaints.index') }}" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-search"></i> Şikayetleri Gözat
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-5">
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h2 class="card-title text-primary">{{ \App\Models\Complaint::count() }}</h2>
                    <p class="card-text">Toplam Şikayet</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h2 class="card-title text-success">{{ \App\Models\Complaint::where('is_resolved', true)->count() }}</h2>
                    <p class="card-text">Çözülen Şikayet</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h2 class="card-title text-info">{{ \App\Models\Brand::count() }}</h2>
                    <p class="card-text">Marka Sayısı</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h2 class="card-title text-warning">{{ \App\Models\User::where('user_type', 'customer')->count() }}</h2>
                    <p class="card-text">Kullanıcı</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Complaints -->
    <div class="row">
        <div class="col-lg-8">
            <h2 class="mb-4">En Yeni Şikayetler</h2>
            @forelse(\App\Models\Complaint::where('is_published', true)->latest()->take(5)->get() as $complaint)
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">{{ $complaint->title }}</h5>
                        <p class="card-text">{{ truncate_text($complaint->content, 150) }}</p>
                        <small class="text-muted">
                            <strong>{{ $complaint->brand->name }}</strong> •
                            {{ time_ago($complaint->created_at) }}
                        </small>
                        <br>
                        {!! badge_status($complaint->status) !!}
                    </div>
                </div>
            @empty
                <p class="text-muted">Henüz şikayet yok.</p>
            @endforelse
        </div>
        <div class="col-lg-4">
            <h2 class="mb-4">Top Markalar</h2>
            @forelse(\App\Models\Brand::where('is_active', true)->orderByDesc('complaint_count')->take(5)->get() as $brand)
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title">{{ $brand->name }}</h6>
                        <p class="card-text">
                            <i class="fas fa-comments"></i> {{ $brand->complaint_count }} şikayet
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-muted">Marka yok.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
