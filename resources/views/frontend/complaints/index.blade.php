@extends('layouts.app')
@section('title', 'Şikayetler')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-3 mb-4">
            <h5>Filtreler</h5>
            <form method="GET" class="card p-3">
                <div class="mb-3">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        <option value="pending">Beklemede</option>
                        <option value="approved">Onaylandı</option>
                        <option value="resolved">Çözüldü</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        @foreach(\App\Models\Category::active()->parents()->get() as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm w-100">Filtrele</button>
            </form>
        </div>
        <div class="col-md-9">
            <h2 class="mb-4">Şikayetler</h2>
            @forelse($complaints ?? [] as $complaint)
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">{{ $complaint->title }}</h5>
                        <p class="card-text">{{ truncate_text($complaint->content, 200) }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    <strong>{{ $complaint->brand->name }}</strong> •
                                    {{ $complaint->category->name }} •
                                    {{ time_ago($complaint->created_at) }}
                                </small>
                            </div>
                            <div>
                                {!! badge_status($complaint->status) !!}
                            </div>
                        </div>
                        <a href="{{ route('frontend.complaints.show', [$complaint->brand->slug, $complaint->complaint_number]) }}"
                           class="stretched-link"></a>
                    </div>
                </div>
            @empty
                <p class="text-muted">Şikayet bulunamadı.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
