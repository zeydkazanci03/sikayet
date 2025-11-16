@extends('layouts.admin')

@section('title', 'Şikayet Yönetimi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-megaphone"></i> Şikayet Yönetimi</h1>
    </div>

    {{-- Filtreler --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.complaints.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Başlık, açıklama ara..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Tüm Durumlar</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Beklemede</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Onaylandı</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Çözüldü</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Reddedildi</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="brand_id" class="form-select">
                        <option value="">Tüm Markalar</option>
                        @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="category_id" class="form-select">
                        <option value="">Tüm Kategoriler</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2"><i class="bi bi-filter"></i> Filtrele</button>
                    <a href="{{ route('admin.complaints.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Temizle</a>
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
                            <th>ID</th>
                            <th>Başlık</th>
                            <th>Kullanıcı</th>
                            <th>Marka</th>
                            <th>Kategori</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($complaints as $complaint)
                        <tr>
                            <td>{{ $complaint->id }}</td>
                            <td>{{ Str::limit($complaint->title, 50) }}</td>
                            <td>{{ $complaint->user->name }}</td>
                            <td>{{ $complaint->brand->name }}</td>
                            <td>{{ $complaint->category->name }}</td>
                            <td><span class="badge bg-{{ $complaint->status_color }}">{{ $complaint->status_label }}</span></td>
                            <td>{{ $complaint->created_at->format('d.m.Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.complaints.show', $complaint) }}" class="btn btn-info" title="Görüntüle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger" onclick="deleteComplaint({{ $complaint->id }})" title="Sil">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Şikayet bulunamadı.</td>
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
        form.action = `/admin/complaints/${id}`;
        form.submit();
    }
}
</script>
@endpush
