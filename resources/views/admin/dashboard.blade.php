@extends('layouts.admin')

@section('title', 'Yönetim Paneli')

@section('content')
<div class="container-fluid">
    {{-- Sayfa Başlığı --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-speedometer2"></i> Yönetim Paneli</h1>
        <div class="text-muted">
            <i class="bi bi-calendar"></i> {{ now()->format('d F Y, l') }}
        </div>
    </div>

    {{-- İstatistik Kartları --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Toplam Şikayet
                            </div>
                            <div class="h5 mb-0 font-weight-bold">{{ number_format($stats['total_complaints']) }}</div>
                            <small class="text-muted">
                                <i class="bi bi-arrow-up text-success"></i> %{{ $stats['complaints_growth'] }} bu ay
                            </small>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Aktif Kullanıcı
                            </div>
                            <div class="h5 mb-0 font-weight-bold">{{ number_format($stats['total_users']) }}</div>
                            <small class="text-muted">
                                {{ $stats['active_users_today'] }} bugün aktif
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fs-2 text-success"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Toplam Marka
                            </div>
                            <div class="h5 mb-0 font-weight-bold">{{ number_format($stats['total_brands']) }}</div>
                            <small class="text-muted">
                                {{ $stats['verified_brands'] }} onaylı
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-building fs-2 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Moderasyon Bekleyen
                            </div>
                            <div class="h5 mb-0 font-weight-bold">{{ number_format($stats['pending_moderation']) }}</div>
                            <small class="text-muted">
                                İşlem gerekiyor
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-hourglass-split fs-2 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Grafikler --}}
        <div class="col-xl-8">
            {{-- Şikayet Trendleri --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-graph-up"></i> Şikayet Trendleri (Son 30 Gün)
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="complaintsChart" height="100"></canvas>
                </div>
            </div>

            {{-- Kategori Dağılımı --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-pie-chart"></i> Kategori Dağılımı
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="categoriesChart" height="100"></canvas>
                </div>
            </div>
        </div>

        {{-- Sağ Sidebar --}}
        <div class="col-xl-4">
            {{-- Moderasyon Kuyruğu --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="bi bi-hourglass-split"></i> Moderasyon Kuyruğu
                    </h6>
                    <a href="{{ route('admin.moderation.queue') }}" class="btn btn-sm btn-warning">
                        Tümü
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($pendingComplaints as $complaint)
                        <a href="{{ route('admin.complaints.show', $complaint) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ Str::limit($complaint->title, 40) }}</h6>
                                <small>{{ $complaint->created_at->diffForHumans() }}</small>
                            </div>
                            <small class="text-muted">{{ $complaint->brand->name }}</small>
                        </a>
                        @empty
                        <div class="list-group-item text-center text-muted">
                            <i class="bi bi-check-circle"></i> Moderasyon bekleyen şikayet yok
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Son Kullanıcılar --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="bi bi-person-plus"></i> Yeni Kullanıcılar
                    </h6>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-success">
                        Tümü
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($recentUsers as $user)
                        <a href="{{ route('admin.users.show', $user) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex align-items-center">
                                <img src="{{ $user->avatar_url }}" class="rounded-circle me-2" width="35" height="35" alt="{{ $user->name }}">
                                <div>
                                    <div class="fw-bold">{{ $user->name }}</div>
                                    <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Popüler Markalar --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="bi bi-trophy"></i> En Çok Şikayet Alan Markalar
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($topBrands as $brand)
                        <a href="{{ route('admin.brands.show', $brand) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $brand->logo_url }}" class="me-2" width="30" height="30" alt="{{ $brand->name }}">
                                    <span>{{ $brand->name }}</span>
                                </div>
                                <span class="badge bg-danger">{{ $brand->complaints_count }}</span>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sistem Aktiviteleri --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-activity"></i> Son Aktiviteler
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Zaman</th>
                                    <th>Kullanıcı</th>
                                    <th>Aktivite</th>
                                    <th>Detay</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentActivities as $activity)
                                <tr>
                                    <td>{{ $activity->created_at->format('d.m.Y H:i') }}</td>
                                    <td>
                                        <img src="{{ $activity->user->avatar_url }}" class="rounded-circle me-1" width="25" height="25" alt="">
                                        {{ $activity->user->name }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $activity->type_color }}">
                                            {{ $activity->type_label }}
                                        </span>
                                    </td>
                                    <td>{{ $activity->description }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
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
// Şikayet Trendleri Grafiği
const complaintsCtx = document.getElementById('complaintsChart').getContext('2d');
new Chart(complaintsCtx, {
    type: 'line',
    data: {
        labels: @json($chartData['labels']),
        datasets: [{
            label: 'Şikayetler',
            data: @json($chartData['complaints']),
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true
    }
});

// Kategori Dağılımı Grafiği
const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
new Chart(categoriesCtx, {
    type: 'doughnut',
    data: {
        labels: @json($categoryData['labels']),
        datasets: [{
            data: @json($categoryData['values']),
            backgroundColor: [
                'rgb(255, 99, 132)',
                'rgb(54, 162, 235)',
                'rgb(255, 205, 86)',
                'rgb(75, 192, 192)',
                'rgb(153, 102, 255)',
                'rgb(255, 159, 64)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true
    }
});
</script>
@endpush
