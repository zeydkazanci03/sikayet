@extends('layouts.frontend')

@section('title', 'Şikayetlerim')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-3">
            {{-- Sidebar Menü --}}
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-person-circle"></i> Profilim</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('profile.show', auth()->user()) }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-person"></i> Profilim
                    </a>
                    <a href="{{ route('profile.complaints') }}" class="list-group-item list-group-item-action active">
                        <i class="bi bi-megaphone"></i> Şikayetlerim
                    </a>
                    <a href="{{ route('profile.notifications') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-bell"></i> Bildirimler
                        @if($unreadNotifications > 0)
                        <span class="badge bg-danger float-end">{{ $unreadNotifications }}</span>
                        @endif
                    </a>
                    <a href="{{ route('profile.edit') }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-gear"></i> Ayarlar
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="bi bi-megaphone"></i> Şikayetlerim</h4>
                        <a href="{{ route('complaints.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Yeni Şikayet
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Filtreler --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <select class="form-select" id="statusFilter">
                                <option value="">Tüm Durumlar</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Beklemede</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Onaylandı</option>
                                <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Çözüldü</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Reddedildi</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="sortOrder">
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>En Yeni</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>En Eski</option>
                                <option value="most_views" {{ request('sort') == 'most_views' ? 'selected' : '' }}>En Çok Görüntülenen</option>
                                <option value="most_liked" {{ request('sort') == 'most_liked' ? 'selected' : '' }}>En Beğenilen</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" placeholder="Ara..." id="searchBox">
                        </div>
                    </div>

                    {{-- İstatistikler --}}
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="mb-0 text-primary">{{ $stats['total'] }}</h4>
                                <small class="text-muted">Toplam</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="mb-0 text-warning">{{ $stats['pending'] }}</h4>
                                <small class="text-muted">Beklemede</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="mb-0 text-success">{{ $stats['resolved'] }}</h4>
                                <small class="text-muted">Çözüldü</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="mb-0 text-danger">{{ $stats['rejected'] }}</h4>
                                <small class="text-muted">Reddedildi</small>
                            </div>
                        </div>
                    </div>

                    {{-- Şikayet Listesi --}}
                    @forelse($complaints as $complaint)
                    <div class="card mb-3 border">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="mb-0">
                                    <a href="{{ route('complaints.show', $complaint) }}" class="text-decoration-none text-dark">
                                        {{ $complaint->title }}
                                    </a>
                                </h5>
                                <span class="badge bg-{{ $complaint->status_color }}">
                                    {{ $complaint->status_label }}
                                </span>
                            </div>

                            <div class="text-muted small mb-2">
                                <a href="{{ route('brands.show', $complaint->brand) }}" class="text-decoration-none">
                                    {{ $complaint->brand->name }}
                                </a>
                                • {{ $complaint->category->name }}
                                • {{ $complaint->created_at->format('d.m.Y H:i') }}
                            </div>

                            <p class="text-muted mb-3">{{ Str::limit($complaint->description, 150) }}</p>

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    <i class="bi bi-eye"></i> {{ $complaint->views_count }}
                                    <span class="ms-2"><i class="bi bi-hand-thumbs-up"></i> {{ $complaint->likes_count }}</span>
                                    <span class="ms-2"><i class="bi bi-chat"></i> {{ $complaint->comments_count }}</span>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('complaints.show', $complaint) }}" class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i> Görüntüle
                                    </a>
                                    @if($complaint->status === 'pending')
                                    <a href="{{ route('complaints.edit', $complaint) }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-pencil"></i> Düzenle
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteComplaint({{ $complaint->id }})">
                                        <i class="bi bi-trash"></i> Sil
                                    </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Marka Yanıtı --}}
                            @if($complaint->response)
                            <div class="alert alert-success mt-3 mb-0">
                                <i class="bi bi-building"></i> <strong>Marka Yanıtı:</strong>
                                {{ Str::limit($complaint->response->content, 100) }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
                        <h5>Şikayet Bulunamadı</h5>
                        <p class="mb-3">Henüz hiç şikayet oluşturmadınız.</p>
                        <a href="{{ route('complaints.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> İlk Şikayetinizi Oluşturun
                        </a>
                    </div>
                    @endforelse

                    {{-- Sayfalama --}}
                    @if($complaints->hasPages())
                    <div class="mt-4">
                        {{ $complaints->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Silme Onay Formu --}}
<form id="deleteComplaintForm" action="" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function deleteComplaint(id) {
    if (confirm('Bu şikayeti silmek istediğinizden emin misiniz?')) {
        const form = document.getElementById('deleteComplaintForm');
        form.action = `/complaints/${id}`;
        form.submit();
    }
}

// Filtreler için event listener
document.getElementById('statusFilter')?.addEventListener('change', function() {
    const url = new URL(window.location);
    url.searchParams.set('status', this.value);
    window.location = url;
});

document.getElementById('sortOrder')?.addEventListener('change', function() {
    const url = new URL(window.location);
    url.searchParams.set('sort', this.value);
    window.location = url;
});
</script>
@endpush
