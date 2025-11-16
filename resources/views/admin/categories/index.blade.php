@extends('layouts.admin')

@section('title', 'Kategori Yönetimi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-tag"></i> Kategori Yönetimi</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            <i class="bi bi-plus-circle"></i> Yeni Kategori
        </button>
    </div>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Kategori Ağacı</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Slug</th>
                            <th>Şikayet Sayısı</th>
                            <th>Durum</th>
                            <th>Sıra</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                        <tr>
                            <td>
                                @if($category->parent_id)
                                    <span class="ms-3">└─</span>
                                @endif
                                <strong>{{ $category->name }}</strong>
                            </td>
                            <td><code>{{ $category->slug }}</code></td>
                            <td>{{ $category->complaints_count }}</td>
                            <td>
                                @if($category->is_active)
                                <span class="badge bg-success">Aktif</span>
                                @else
                                <span class="badge bg-danger">Pasif</span>
                                @endif
                            </td>
                            <td>{{ $category->order }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-warning" onclick="editCategory({{ $category->id }})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-danger" onclick="deleteCategory({{ $category->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
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

{{-- Kategori Oluşturma Modal --}}
<x-modal id="createCategoryModal" title="Yeni Kategori Oluştur">
    <form action="{{ route('admin.categories.store') }}" method="POST">
        @csrf
        <div class="modal-body">
            <div class="mb-3">
                <label for="name" class="form-label">Kategori Adı <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="slug" class="form-label">Slug</label>
                <input type="text" name="slug" id="slug" class="form-control">
                <small class="text-muted">Boş bırakılırsa otomatik oluşturulur</small>
            </div>
            <div class="mb-3">
                <label for="parent_id" class="form-label">Üst Kategori</label>
                <select name="parent_id" id="parent_id" class="form-select">
                    <option value="">Ana Kategori</option>
                    @foreach($parentCategories as $parent)
                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Açıklama</label>
                <textarea name="description" id="description" rows="3" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label for="order" class="form-label">Sıra</label>
                <input type="number" name="order" id="order" class="form-control" value="0">
            </div>
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" checked>
                    <label for="is_active" class="form-check-label">Aktif</label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check"></i> Oluştur</button>
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script>
function deleteCategory(id) {
    if (confirm('Bu kategoriyi silmek istediğinizden emin misiniz?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/categories/${id}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
