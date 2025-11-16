@extends('layouts.admin')

@section('title', 'Şikayet Detayı')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-megaphone"></i> Şikayet Moderasyonu</h1>
        <a href="{{ route('admin.complaints.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Geri
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Şikayet İçeriği --}}
            <div class="card shadow mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4>{{ $complaint->title }}</h4>
                            <div class="text-muted small">
                                {{ $complaint->created_at->format('d.m.Y H:i') }}
                            </div>
                        </div>
                        <span class="badge bg-{{ $complaint->status_color }} fs-6">{{ $complaint->status_label }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Marka:</strong> {{ $complaint->brand->name }}<br>
                        <strong>Kategori:</strong> {{ $complaint->category->name }}<br>
                        <strong>Kullanıcı:</strong> {{ $complaint->user->name }} ({{ $complaint->user->email }})
                    </div>
                    <hr>
                    <div class="mb-3">
                        <h6>Açıklama:</h6>
                        <p>{!! nl2br(e($complaint->description)) !!}</p>
                    </div>

                    @if($complaint->media && $complaint->media->count() > 0)
                    <div class="mb-3">
                        <h6>Ekler:</h6>
                        <div class="row g-2">
                            @foreach($complaint->media as $media)
                            <div class="col-md-3">
                                <img src="{{ $media->url }}" class="img-fluid rounded" alt="Ek">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <hr>
                    <div class="d-flex justify-content-between">
                        <div class="text-muted">
                            <i class="bi bi-eye"></i> {{ $complaint->views_count }} görüntülenme
                            <span class="ms-2"><i class="bi bi-hand-thumbs-up"></i> {{ $complaint->likes_count }} beğeni</span>
                            <span class="ms-2"><i class="bi bi-chat"></i> {{ $complaint->comments_count }} yorum</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Yorumlar --}}
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Yorumlar ({{ $complaint->comments_count }})</h5>
                </div>
                <div class="card-body">
                    @forelse($complaint->comments as $comment)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>{{ $comment->user->name }}</strong>
                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                            </div>
                            <form action="{{ route('admin.comments.destroy', $comment) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Silmek istediğinizden emin misiniz?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                        <p class="mb-0">{{ $comment->content }}</p>
                    </div>
                    @empty
                    <p class="text-center text-muted">Henüz yorum yok.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Moderasyon Paneli --}}
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">Moderasyon İşlemleri</h6>
                </div>
                <div class="card-body">
                    @if($complaint->status === 'pending')
                    <form action="{{ route('admin.complaints.approve', $complaint) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Onayla
                        </button>
                    </form>
                    <form action="{{ route('admin.complaints.reject', $complaint) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <textarea name="reason" class="form-control" rows="2" placeholder="Ret nedeni..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-x-circle"></i> Reddet
                        </button>
                    </form>
                    @else
                    <div class="alert alert-info">
                        Bu şikayet zaten işleme alınmış.
                    </div>
                    @endif

                    <hr>

                    <div class="d-grid gap-2">
                        <a href="{{ route('complaints.show', $complaint) }}" class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-box-arrow-up-right"></i> Önizle
                        </a>
                        <form action="{{ route('admin.complaints.destroy', $complaint) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Silmek istediğinizden emin misiniz?')">
                                <i class="bi bi-trash"></i> Sil
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- İstatistikler --}}
            <div class="card shadow mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">İstatistikler</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Oluşturulma:</strong><br>
                        {{ $complaint->created_at->format('d.m.Y H:i') }}
                    </div>
                    <div class="mb-2">
                        <strong>Son Güncelleme:</strong><br>
                        {{ $complaint->updated_at->format('d.m.Y H:i') }}
                    </div>
                    <div class="mb-2">
                        <strong>IP Adresi:</strong><br>
                        {{ $complaint->ip_address }}
                    </div>
                </div>
            </div>

            {{-- Admin Notları --}}
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Admin Notları</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.complaints.add-note', $complaint) }}" method="POST" class="mb-3">
                        @csrf
                        <textarea name="note" class="form-control mb-2" rows="2" placeholder="Not ekle..." required></textarea>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus"></i> Ekle
                        </button>
                    </form>

                    @forelse($complaint->admin_notes as $note)
                    <div class="border p-2 mb-2 rounded">
                        <small class="text-muted">{{ $note->admin->name }} - {{ $note->created_at->format('d.m.Y H:i') }}</small>
                        <p class="mb-0">{{ $note->content }}</p>
                    </div>
                    @empty
                    <p class="text-muted small">Not yok.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
