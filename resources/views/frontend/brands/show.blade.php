@extends('layouts.frontend')

@section('title', $brand->name)

@section('content')
<div class="container py-4">
    {{-- Marka Başlığı --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="img-fluid rounded" style="max-height: 120px;">
                </div>
                <div class="col-md-7">
                    <h1 class="h3 mb-2">
                        {{ $brand->name }}
                        @if($brand->is_verified)
                        <i class="bi bi-patch-check-fill text-primary" title="Onaylı Marka"></i>
                        @endif
                    </h1>
                    <p class="text-muted mb-2">{{ $brand->description }}</p>
                    <div class="mb-2">
                        <span class="badge bg-secondary me-2">{{ $brand->category->name }}</span>
                        <span class="text-muted">
                            <i class="bi bi-calendar"></i> Üyelik: {{ $brand->created_at->format('Y') }}
                        </span>
                    </div>
                    @if($brand->website)
                    <a href="{{ $brand->website }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-globe"></i> Web Sitesi
                    </a>
                    @endif
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="h2 mb-0 text-{{ $brand->rating >= 4 ? 'success' : ($brand->rating >= 3 ? 'warning' : 'danger') }}">
                            {{ number_format($brand->rating, 1) }}/5
                        </div>
                        <div class="text-muted small">Ortalama Puan</div>
                        <div class="mt-2">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $brand->rating)
                                <i class="bi bi-star-fill text-warning"></i>
                                @else
                                <i class="bi bi-star text-warning"></i>
                                @endif
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- İstatistikler --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-primary mb-0">{{ $brand->complaints_count }}</h3>
                    <div class="text-muted">Toplam Şikayet</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-success mb-0">{{ $brand->resolved_complaints_count }}</h3>
                    <div class="text-muted">Çözülen</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-warning mb-0">{{ $brand->response_rate }}%</h3>
                    <div class="text-muted">Yanıt Oranı</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-info mb-0">{{ $brand->followers_count }}</h3>
                    <div class="text-muted">Takipçi</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Filtre ve Sıralama --}}
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <select class="form-select" id="statusFilter">
                                <option value="">Tüm Durumlar</option>
                                <option value="pending">Beklemede</option>
                                <option value="resolved">Çözüldü</option>
                                <option value="rejected">Reddedildi</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <select class="form-select" id="sortOrder">
                                <option value="newest">En Yeni</option>
                                <option value="oldest">En Eski</option>
                                <option value="most_liked">En Beğenilen</option>
                                <option value="most_commented">En Çok Yorumlanan</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Şikayetler --}}
            <h5 class="mb-3">Şikayetler ({{ $complaints->total() }})</h5>
            @forelse($complaints as $complaint)
                <x-complaint-card :complaint="$complaint" />
            @empty
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
                <p class="mb-0">Bu marka için henüz şikayet bulunmamaktadır.</p>
            </div>
            @endforelse

            {{-- Sayfalama --}}
            @if($complaints->hasPages())
            <div class="mt-4">
                {{ $complaints->links() }}
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Şikayet Yaz Butonu --}}
            @auth
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <a href="{{ route('complaints.create', ['brand' => $brand->id]) }}" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-megaphone"></i> Şikayet Yaz
                    </a>
                </div>
            </div>
            @else
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <p class="text-muted mb-2">Şikayet yazmak için giriş yapın</p>
                    <a href="{{ route('login') }}" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right"></i> Giriş Yap
                    </a>
                </div>
            </div>
            @endauth

            {{-- İletişim Bilgileri --}}
            @if($brand->contact_info)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">İletişim Bilgileri</h6>
                </div>
                <div class="card-body">
                    @if($brand->phone)
                    <div class="mb-2">
                        <i class="bi bi-telephone"></i> {{ $brand->phone }}
                    </div>
                    @endif
                    @if($brand->email)
                    <div class="mb-2">
                        <i class="bi bi-envelope"></i> {{ $brand->email }}
                    </div>
                    @endif
                    @if($brand->address)
                    <div class="mb-2">
                        <i class="bi bi-geo-alt"></i> {{ $brand->address }}
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Sosyal Medya --}}
            @if($brand->social_media)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Sosyal Medya</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2">
                        @if($brand->social_media['facebook'] ?? null)
                        <a href="{{ $brand->social_media['facebook'] }}" target="_blank" class="btn btn-outline-primary">
                            <i class="bi bi-facebook"></i>
                        </a>
                        @endif
                        @if($brand->social_media['twitter'] ?? null)
                        <a href="{{ $brand->social_media['twitter'] }}" target="_blank" class="btn btn-outline-info">
                            <i class="bi bi-twitter"></i>
                        </a>
                        @endif
                        @if($brand->social_media['instagram'] ?? null)
                        <a href="{{ $brand->social_media['instagram'] }}" target="_blank" class="btn btn-outline-danger">
                            <i class="bi bi-instagram"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Benzer Markalar --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Benzer Markalar</h6>
                </div>
                <div class="card-body">
                    @foreach($similarBrands as $similar)
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ $similar->logo_url }}" alt="{{ $similar->name }}" class="me-2" style="width: 40px; height: 40px;">
                        <div class="flex-grow-1">
                            <a href="{{ route('brands.show', $similar) }}" class="text-decoration-none">
                                <strong>{{ $similar->name }}</strong>
                            </a>
                            <div class="text-muted small">{{ $similar->complaints_count }} şikayet</div>
                        </div>
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
// Filtreler için AJAX güncellemesi eklenebilir
document.getElementById('statusFilter')?.addEventListener('change', function() {
    // Filtreleme işlemi
});
</script>
@endpush
