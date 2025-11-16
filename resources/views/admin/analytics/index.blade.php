@extends('layouts.admin')

@section('title', 'Analitik ve Raporlar')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-graph-up"></i> Analitik ve Raporlar</h1>
        <div>
            <button class="btn btn-success" onclick="exportData('excel')">
                <i class="bi bi-file-earmark-excel"></i> Excel İndir
            </button>
            <button class="btn btn-danger" onclick="exportData('pdf')">
                <i class="bi bi-file-earmark-pdf"></i> PDF İndir
            </button>
        </div>
    </div>

    {{-- Tarih Filtresi --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.analytics.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Başlangıç Tarihi</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date', now()->subDays(30)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Bitiş Tarihi</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Filtrele
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Özet İstatistikler --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Toplam Şikayet</div>
                            <div class="h5 mb-0 font-weight-bold">{{ number_format($stats['total_complaints']) }}</div>
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
                            <div class="h5 mb-0 font-weight-bold">{{ number_format($stats['resolved_complaints']) }}</div>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Yeni Kullanıcı</div>
                            <div class="h5 mb-0 font-weight-bold">{{ number_format($stats['new_users']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-plus fs-2 text-info"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Ortalama Yanıt Süresi</div>
                            <div class="h5 mb-0 font-weight-bold">{{ $stats['avg_response_time'] }} saat</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock-history fs-2 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grafikler --}}
    <div class="row mb-4">
        <div class="col-xl-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Şikayet Trendleri</h6>
                </div>
                <div class="card-body">
                    <canvas id="complaintsChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Durum Dağılımı</h6>
                </div>
                <div class="card-body">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Tablolar --}}
    <div class="row">
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">En Çok Şikayet Alan Markalar</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Marka</th>
                                    <th>Şikayet</th>
                                    <th>Çözülen</th>
                                    <th>Oran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topBrands as $brand)
                                <tr>
                                    <td>{{ $brand->name }}</td>
                                    <td>{{ $brand->complaints_count }}</td>
                                    <td>{{ $brand->resolved_count }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: {{ $brand->resolution_rate }}%">
                                                {{ $brand->resolution_rate }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Popüler Kategoriler</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th>Şikayet</th>
                                    <th>Yüzde</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCategories as $category)
                                <tr>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->complaints_count }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-info" style="width: {{ $category->percentage }}%">
                                                {{ $category->percentage }}%
                                            </div>
                                        </div>
                                    </td>
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
// Şikayet Trendleri
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
        }, {
            label: 'Çözülenler',
            data: @json($chartData['resolved']),
            borderColor: 'rgb(54, 162, 235)',
            tension: 0.1
        }]
    }
});

// Durum Dağılımı
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: @json($statusData['labels']),
        datasets: [{
            data: @json($statusData['values']),
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
        }]
    }
});

function exportData(format) {
    window.location.href = `/admin/analytics/export?format=${format}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}`;
}
</script>
@endpush
