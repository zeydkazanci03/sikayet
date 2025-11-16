@extends('layouts.admin')

@section('title', 'Moderasyon Kuyruğu')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-hourglass-split"></i> Moderasyon Kuyruğu</h1>
        <span class="badge bg-warning fs-6">{{ $pendingComplaints->total() }} Bekleyen</span>
    </div>

    {{-- Hızlı Filtre --}}
    <div class="btn-group mb-4" role="group">
        <a href="{{ route('admin.moderation.queue', ['sort' => 'oldest']) }}" class="btn btn-outline-primary {{ request('sort') == 'oldest' ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i> En Eski
        </a>
        <a href="{{ route('admin.moderation.queue', ['sort' => 'newest']) }}" class="btn btn-outline-primary {{ request('sort') == 'newest' || !request('sort') ? 'active' : '' }}">
            <i class="bi bi-clock"></i> En Yeni
        </a>
        <a href="{{ route('admin.moderation.queue', ['sort' => 'most_reported']) }}" class="btn btn-outline-primary {{ request('sort') == 'most_reported' ? 'active' : '' }}">
            <i class="bi bi-flag"></i> En Çok Rapor
        </a>
    </div>

    @forelse($pendingComplaints as $complaint)
    <div class="card shadow mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between mb-2">
                        <h5>{{ $complaint->title }}</h5>
                        <small class="text-muted">{{ $complaint->created_at->diffForHumans() }}</small>
                    </div>

                    <div class="mb-2">
                        <span class="badge bg-secondary">{{ $complaint->brand->name }}</span>
                        <span class="badge bg-info">{{ $complaint->category->name }}</span>
                        <span class="text-muted small">
                            <i class="bi bi-person"></i> {{ $complaint->user->name }}
                        </span>
                    </div>

                    <p class="text-muted">{{ Str::limit($complaint->description, 200) }}</p>

                    @if($complaint->media && $complaint->media->count() > 0)
                    <div class="mb-2">
                        <i class="bi bi-paperclip"></i> {{ $complaint->media->count() }} dosya eklendi
                    </div>
                    @endif

                    @if($complaint->reports_count > 0)
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-flag-fill"></i> <strong>{{ $complaint->reports_count }} rapor alındı!</strong>
                    </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.complaints.show', $complaint) }}" class="btn btn-info">
                            <i class="bi bi-eye"></i> İncele
                        </a>
                        <form action="{{ route('admin.complaints.approve', $complaint) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle"></i> Hızlı Onayla
                            </button>
                        </form>
                        <button class="btn btn-danger" data-bs-toggle="collapse" data-bs-target="#reject{{ $complaint->id }}">
                            <i class="bi bi-x-circle"></i> Reddet
                        </button>
                        <div class="collapse" id="reject{{ $complaint->id }}">
                            <form action="{{ route('admin.complaints.reject', $complaint) }}" method="POST" class="mt-2">
                                @csrf
                                <textarea name="reason" class="form-control mb-2" rows="2" placeholder="Ret nedeni..." required></textarea>
                                <button type="submit" class="btn btn-danger w-100">Reddet</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="alert alert-success text-center">
        <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
        <h4>Harika!</h4>
        <p class="mb-0">Moderasyon bekleyen şikayet bulunmamaktadır.</p>
    </div>
    @endforelse

    @if($pendingComplaints->hasPages())
    <div class="mt-4">
        {{ $pendingComplaints->links() }}
    </div>
    @endif
</div>
@endsection
