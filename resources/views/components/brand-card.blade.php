{{-- Marka Kartı Komponenti --}}
@props(['brand'])

<div class="card h-100 shadow-sm brand-card">
    <div class="card-body text-center">
        {{-- Logo --}}
        <a href="{{ route('brands.show', $brand) }}">
            <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}"
                 class="img-fluid mb-3" style="max-height: 80px;">
        </a>

        {{-- Marka Adı --}}
        <h5 class="card-title">
            <a href="{{ route('brands.show', $brand) }}" class="text-decoration-none text-dark">
                {{ $brand->name }}
                @if($brand->is_verified)
                <i class="bi bi-patch-check-fill text-primary" title="Onaylı Marka"></i>
                @endif
            </a>
        </h5>

        {{-- Kategori --}}
        <p class="text-muted small mb-2">
            <a href="{{ route('categories.show', $brand->category) }}" class="text-muted text-decoration-none">
                {{ $brand->category->name }}
            </a>
        </p>

        {{-- Puan --}}
        <div class="mb-3">
            <div class="d-inline-block">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= $brand->rating)
                    <i class="bi bi-star-fill text-warning"></i>
                    @else
                    <i class="bi bi-star text-warning"></i>
                    @endif
                @endfor
            </div>
            <div class="text-muted small">
                {{ number_format($brand->rating, 1) }}/5
            </div>
        </div>

        {{-- İstatistikler --}}
        <div class="row text-center g-0 mb-3">
            <div class="col-4">
                <div class="small text-muted">Şikayet</div>
                <strong>{{ $brand->complaints_count }}</strong>
            </div>
            <div class="col-4">
                <div class="small text-muted">Çözülen</div>
                <strong class="text-success">{{ $brand->resolved_complaints_count }}</strong>
            </div>
            <div class="col-4">
                <div class="small text-muted">Yanıt</div>
                <strong class="text-primary">{{ $brand->response_rate }}%</strong>
            </div>
        </div>

        {{-- Açıklama --}}
        @if($brand->description)
        <p class="card-text text-muted small">
            {{ Str::limit($brand->description, 80) }}
        </p>
        @endif

        {{-- Butonlar --}}
        <div class="d-grid gap-2">
            <a href="{{ route('brands.show', $brand) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-eye"></i> Profili Görüntüle
            </a>
            @auth
            <a href="{{ route('complaints.create', ['brand' => $brand->id]) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-megaphone"></i> Şikayet Yaz
            </a>
            @endauth
        </div>
    </div>

    @if($brand->is_verified)
    <div class="card-footer bg-success text-white text-center small">
        <i class="bi bi-patch-check-fill"></i> Onaylı Marka
    </div>
    @endif
</div>
