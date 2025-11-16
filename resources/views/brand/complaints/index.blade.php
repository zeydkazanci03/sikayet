@extends('layouts.brand')

@section('title', 'Şikayetlerim')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-megaphone"></i> Şikayetler</h1>
    </div>

    {{-- Filtreler --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('brand.complaints.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Şikayet ara..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tüm Durumlar</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Yanıt Bekliyor</option>
                        <option value="responded" {{ request('status') == 'responded' ? 'selected' : '' }}>Yanıtlandı</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Çözüldü</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sort" class="form-select">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>En Yeni</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>En Eski</option>
                        <option value="most_viewed" {{ request('sort') == 'most_viewed' ? 'selected' : '' }}>En Çok Görüntülenen</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2"><i class="bi bi-filter"></i> Filtrele</button>
                    <a href="{{ route('brand.complaints.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Temizle</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Şikayet Listesi --}}
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Şikayet Listesi ({{ $complaints->total() }})</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Başlık</th>
                            <th>Kullanıcı</th>
                            <th>Durum</th>
                            <th>Görüntülenme</th>
                            <th>Tarih</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($complaints as $complaint)
                        <tr class="{{ !$complaint->response ? 'table-warning' : '' }}">
                            <td>
                                <strong>{{ Str::limit($complaint->title, 60) }}</strong>
                                @if(!$complaint->response)
                                <span class="badge bg-warning ms-2">Yanıt Bekleniyor</span>
                                @endif
                            </td>
                            <td>{{ $complaint->is_anonymous ? 'Anonim' : $complaint->user->name }}</td>
                            <td>
                                <span class="badge bg-{{ $complaint->status_color }}">
                                    {{ $complaint->status_label }}
                                </span>
                            </td>
                            <td>{{ $complaint->views_count }}</td>
                            <td>{{ $complaint->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <a href="{{ route('brand.complaints.show', $complaint) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Görüntüle
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Şikayet bulunamadı.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($complaints->hasPages())
            <div class="mt-3">
                {{ $complaints->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
