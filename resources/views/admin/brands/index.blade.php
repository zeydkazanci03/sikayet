@extends('layouts.admin')

@section('title', 'Marka Yönetimi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-building"></i> Marka Yönetimi</h1>
        <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Yeni Marka
        </a>
    </div>

    {{-- Filtreler --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.brands.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Marka ara..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="category_id" class="form-select">
                        <option value="">Tüm Kategoriler</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="verified" class="form-select">
                        <option value="">Tümü</option>
                        <option value="1" {{ request('verified') == '1' ? 'selected' : '' }}>Onaylı</option>
                        <option value="0" {{ request('verified') == '0' ? 'selected' : '' }}>Onaylanmamış</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2"><i class="bi bi-filter"></i> Filtrele</button>
                    <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Temizle</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Marka Listesi --}}
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Marka Listesi ({{ $brands->total() }})</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Logo</th>
                            <th>Marka</th>
                            <th>Kategori</th>
                            <th>Şikayet</th>
                            <th>Puan</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($brands as $brand)
                        <tr>
                            <td><img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" width="40" height="40"></td>
                            <td>
                                <strong>{{ $brand->name }}</strong>
                                @if($brand->is_verified)
                                <i class="bi bi-patch-check-fill text-primary"></i>
                                @endif
                            </td>
                            <td>{{ $brand->category->name }}</td>
                            <td>{{ $brand->complaints_count }}</td>
                            <td>
                                <div class="text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $brand->rating)
                                        <i class="bi bi-star-fill"></i>
                                        @else
                                        <i class="bi bi-star"></i>
                                        @endif
                                    @endfor
                                </div>
                                <small>{{ number_format($brand->rating, 1) }}/5</small>
                            </td>
                            <td>
                                @if($brand->is_verified)
                                <span class="badge bg-success">Onaylı</span>
                                @else
                                <span class="badge bg-warning">Beklemede</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.brands.show', $brand) }}" class="btn btn-info"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-warning"><i class="bi bi-pencil"></i></a>
                                    <button type="button" class="btn btn-danger" onclick="deleteBrand({{ $brand->id }})"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Marka bulunamadı.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($brands->hasPages())
            <div class="mt-3">
                {{ $brands->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<form id="deleteBrandForm" action="" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function deleteBrand(id) {
    if (confirm('Bu markayı silmek istediğinizden emin misiniz?')) {
        const form = document.getElementById('deleteBrandForm');
        form.action = `/admin/brands/${id}`;
        form.submit();
    }
}
</script>
@endpush
