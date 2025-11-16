@extends('layouts.admin')

@section('title', 'Kullanıcı Yönetimi')

@section('content')
<div class="container-fluid">
    {{-- Sayfa Başlığı --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-people"></i> Kullanıcı Yönetimi</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Yeni Kullanıcı
        </a>
    </div>

    {{-- Filtreler --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Ad, email veya kullanıcı adı ara..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="role" class="form-select">
                        <option value="">Tüm Roller</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="moderator" {{ request('role') == 'moderator' ? 'selected' : '' }}>Moderatör</option>
                        <option value="brand" {{ request('role') == 'brand' ? 'selected' : '' }}>Marka</option>
                        <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>Kullanıcı</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Tüm Durumlar</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Pasif</option>
                        <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>Yasaklı</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sort" class="form-select">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>En Yeni</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>En Eski</option>
                        <option value="most_complaints" {{ request('sort') == 'most_complaints' ? 'selected' : '' }}>En Çok Şikayet</option>
                        <option value="alphabetical" {{ request('sort') == 'alphabetical' ? 'selected' : '' }}>A-Z</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-filter"></i> Filtrele
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Temizle
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Kullanıcı Listesi --}}
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Kullanıcı Listesi ({{ $users->total() }})
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kullanıcı</th>
                            <th>E-posta</th>
                            <th>Rol</th>
                            <th>Durum</th>
                            <th>Şikayet</th>
                            <th>Kayıt Tarihi</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $user->avatar_url }}" class="rounded-circle me-2" width="35" height="35" alt="{{ $user->name }}">
                                    <div>
                                        <div class="fw-bold">{{ $user->name }}</div>
                                        <small class="text-muted">@{{ $user->username }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ $user->role_color }}">
                                    {{ $user->role_label }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $user->status_color }}">
                                    {{ $user->status_label }}
                                </span>
                            </td>
                            <td>{{ $user->complaints_count }}</td>
                            <td>{{ $user->created_at->format('d.m.Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info" title="Görüntüle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning" title="Düzenle">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                    <button type="button" class="btn btn-danger" onclick="deleteUser({{ $user->id }})" title="Sil">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Kullanıcı bulunamadı.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Sayfalama --}}
            @if($users->hasPages())
            <div class="mt-3">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Silme Formu --}}
<form id="deleteUserForm" action="" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function deleteUser(id) {
    if (confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')) {
        const form = document.getElementById('deleteUserForm');
        form.action = `/admin/users/${id}`;
        form.submit();
    }
}
</script>
@endpush
