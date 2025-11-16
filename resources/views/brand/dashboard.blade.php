@extends('layouts.brand')

@section('title', 'Marka Paneli')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-speedometer2"></i> Marka Paneli</h1>
        <span class="text-muted">{{ $brand->name }}</span>
    </div>

    {{-- İstatistikler --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Bekleyen Şikayetler</div>
                            <div class="h5 mb-0 font-weight-bold text-warning">{{ $stats['pending_complaints'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-hourglass-split fs-2 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Toplam Şikayet</div>
                            <div class="h5 mb-0 font-weight-bold">{{ $stats['total_complaints'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-megaphone fs-2 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Çözülen</div>
                            <div class="h5 mb-0 font-weight-bold text-success">{{ $stats['resolved_complaints'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fs-2 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Yanıt Oranı</div>
                            <div class="h5 mb-0 font-weight-bold text-info">{{ $stats['response_rate'] }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-reply fs-2 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            {{-- Yanıt Bekleyen Şikayetler --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">Yanıt Bekleyen Şikayetler</h6>
                    <a href="{{ route('brand.complaints.index', ['status' => 'pending']) }}" class="btn btn-sm btn-warning">
                        Tümünü Gör
                    </a>
                </div>
                <div class="card-body">
                    @forelse($pendingComplaints as $complaint)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6>
                                    <a href="{{ route('brand.complaints.show', $complaint) }}">
                                        {{ $complaint->title }}
                                    </a>
                                </h6>
                                <p class="text-muted mb-2">{{ Str::limit($complaint->description, 150) }}</p>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> {{ $complaint->created_at->diffForHumans() }}
                                    <span class="ms-2"><i class="bi bi-eye"></i> {{ $complaint->views_count }}</span>
                                </small>
                            </div>
                            <a href="{{ route('brand.complaints.show', $complaint) }}" class="btn btn-sm btn-primary ms-3">
                                <i class="bi bi-reply"></i> Yanıtla
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
                        <p>Harika! Yanıt bekleyen şikayet yok.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Grafik --}}
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Şikayet Trendleri (Son 30 Gün)</h6>
                </div>
                <div class="card-body">
                    <canvas id="complaintsChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            {{-- Marka Profili --}}
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="img-fluid mb-3" style="max-height: 100px;">
                    <h5>{{ $brand->name }}</h5>
                    <div class="mb-3">
                        <div class="h3 mb-0 text-{{ $brand->rating >= 4 ? 'success' : ($brand->rating >= 3 ? 'warning' : 'danger') }}">
                            {{ number_format($brand->rating, 1) }}/5
                        </div>
                        <small class="text-muted">Ortalama Puan</small>
                    </div>
                    <a href="{{ route('brand.profile.edit') }}" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-pencil"></i> Profili Düzenle
                    </a>
                </div>
            </div>

            {{-- Son Yorumlar --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Son Yorumlar</h6>
                </div>
                <div class="card-body">
                    @foreach($recentComments as $comment)
                    <div class="mb-3">
                        <strong>{{ $comment->user->name }}</strong>
                        <p class="text-muted small mb-1">{{ Str::limit($comment->content, 80) }}</p>
                        <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Hızlı Bağlantılar --}}
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Hızlı Bağlantılar</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('brand.complaints.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-megaphone"></i> Tüm Şikayetler
                        </a>
                        <a href="{{ route('brands.show', $brand) }}" class="btn btn-outline-info btn-sm" target="_blank">
                            <i class="bi bi-box-arrow-up-right"></i> Profili Görüntüle
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
const ctx = document.getElementById('complaintsChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($chartData['labels']),
        datasets: [{
            label: 'Şikayetler',
            data: @json($chartData['complaints']),
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }]
    }
});
</script>
@endpush
