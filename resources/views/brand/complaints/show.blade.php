@extends('layouts.brand')

@section('title', 'Şikayet Detayı')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-megaphone"></i> Şikayet Detayı</h1>
        <a href="{{ route('brand.complaints.index') }}" class="btn btn-secondary">
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
                            <small class="text-muted">{{ $complaint->created_at->format('d.m.Y H:i') }}</small>
                        </div>
                        <span class="badge bg-{{ $complaint->status_color }} fs-6">{{ $complaint->status_label }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Kullanıcı:</strong> {{ $complaint->is_anonymous ? 'Anonim' : $complaint->user->name }}<br>
                        <strong>Kategori:</strong> {{ $complaint->category->name }}
                    </div>

                    <hr>

                    <div class="mb-3">
                        <h6>Şikayet Detayı:</h6>
                        <p>{!! nl2br(e($complaint->description)) !!}</p>
                    </div>

                    @if($complaint->media && $complaint->media->count() > 0)
                    <div class="mb-3">
                        <h6>Ek Dosyalar:</h6>
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

                    <div class="text-muted">
                        <i class="bi bi-eye"></i> {{ $complaint->views_count }} görüntülenme
                        <span class="ms-2"><i class="bi bi-hand-thumbs-up"></i> {{ $complaint->likes_count }} beğeni</span>
                        <span class="ms-2"><i class="bi bi-chat"></i> {{ $complaint->comments_count }} yorum</span>
                    </div>
                </div>
            </div>

            {{-- Yorumlar --}}
            @if($complaint->comments && $complaint->comments->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Yorumlar ({{ $complaint->comments_count }})</h5>
                </div>
                <div class="card-body">
                    @foreach($complaint->comments as $comment)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $comment->user->name }}</strong>
                            <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-0">{{ $comment->content }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Mevcut Yanıt --}}
            @if($complaint->response)
            <div class="card shadow border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Sizin Yanıtınız</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">{{ $complaint->response->created_at->format('d.m.Y H:i') }}</small>
                    </div>
                    <p class="mb-0">{!! nl2br(e($complaint->response->content)) !!}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Yanıt Formu --}}
        <div class="col-lg-4">
            @if(!$complaint->response)
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-reply"></i> Yanıt Ver</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('brand.complaints.respond', $complaint) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="response" class="form-label">Yanıtınız <span class="text-danger">*</span></label>
                            <textarea name="response" id="response" rows="8" class="form-control @error('response') is-invalid @enderror"
                                      placeholder="Müşterinize profesyonel ve çözüm odaklı bir yanıt yazın..." required>{{ old('response') }}</textarea>
                            @error('response')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Yanıtınız herkese açık olarak görüntülenecektir.</small>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Durum</label>
                            <select name="status" id="status" class="form-select">
                                <option value="responded">Yanıtlandı</option>
                                <option value="resolved">Çözüldü</option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send"></i> Yanıtı Gönder
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Dikkat:</strong>
                <ul class="mb-0 mt-2 small">
                    <li>Yanıtınız herkese açık olarak yayınlanacaktır.</li>
                    <li>Profesyonel ve çözüm odaklı olun.</li>
                    <li>Müşteriye saygılı davranın.</li>
                    <li>Somut çözüm önerileri sunun.</li>
                </ul>
            </div>
            @else
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-check-circle"></i> Yanıt Verildi</h6>
                </div>
                <div class="card-body">
                    <p>Bu şikayete zaten yanıt verdiniz.</p>
                    <a href="{{ route('complaints.show', $complaint) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                        <i class="bi bi-box-arrow-up-right"></i> Önizle
                    </a>
                </div>
            </div>
            @endif

            {{-- İstatistikler --}}
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h6 class="mb-0">İstatistikler</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Oluşturulma:</strong><br>
                        {{ $complaint->created_at->format('d.m.Y H:i') }}
                    </div>
                    <div class="mb-2">
                        <strong>Görüntülenme:</strong><br>
                        {{ $complaint->views_count }} kez
                    </div>
                    <div class="mb-2">
                        <strong>Beğeni:</strong><br>
                        {{ $complaint->likes_count }}
                    </div>
                    <div class="mb-2">
                        <strong>Yorum:</strong><br>
                        {{ $complaint->comments_count }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
