@extends('layouts.admin')

@section('title', 'Blog Yönetimi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-newspaper"></i> Blog Yönetimi</h1>
        <a href="{{ route('admin.blog.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Yeni Yazı
        </a>
    </div>

    {{-- Filtreler --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.blog.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Başlık ara..." value="{{ request('search') }}">
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
                    <select name="status" class="form-select">
                        <option value="">Tümü</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Yayında</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Taslak</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2"><i class="bi bi-filter"></i> Filtrele</button>
                    <a href="{{ route('admin.blog.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Temizle</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Blog Yazıları --}}
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Blog Yazıları ({{ $posts->total() }})</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Görsel</th>
                            <th>Başlık</th>
                            <th>Yazar</th>
                            <th>Kategori</th>
                            <th>Durum</th>
                            <th>Görüntülenme</th>
                            <th>Tarih</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $post)
                        <tr>
                            <td>
                                @if($post->featured_image)
                                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" width="60" height="40" class="rounded">
                                @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 40px;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ Str::limit($post->title, 50) }}</strong>
                                @if($post->is_featured)
                                <span class="badge bg-warning">Öne Çıkan</span>
                                @endif
                            </td>
                            <td>{{ $post->author->name }}</td>
                            <td>{{ $post->category->name }}</td>
                            <td>
                                @if($post->status === 'published')
                                <span class="badge bg-success">Yayında</span>
                                @else
                                <span class="badge bg-secondary">Taslak</span>
                                @endif
                            </td>
                            <td>{{ $post->views_count }}</td>
                            <td>{{ $post->created_at->format('d.m.Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('blog.show', $post) }}" class="btn btn-info" target="_blank" title="Önizle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.blog.edit', $post) }}" class="btn btn-warning" title="Düzenle">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger" onclick="deletePost({{ $post->id }})" title="Sil">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Blog yazısı bulunamadı.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($posts->hasPages())
            <div class="mt-3">
                {{ $posts->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<form id="deletePostForm" action="" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function deletePost(id) {
    if (confirm('Bu blog yazısını silmek istediğinizden emin misiniz?')) {
        const form = document.getElementById('deletePostForm');
        form.action = `/admin/blog/${id}`;
        form.submit();
    }
}
</script>
@endpush
