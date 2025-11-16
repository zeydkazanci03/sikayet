@extends('layouts.admin')

@section('title', 'Marka Detayı - ' . $brand->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-building"></i> Marka Detayı</h1>
        <div>
            <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-warning"><i class="bi bi-pencil"></i> Düzenle</a>
            <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Geri</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="img-fluid mb-3" style="max-height: 120px;">
                    <h4>{{ $brand->name }}</h4>
                    @if($brand->is_verified)
                    <span class="badge bg-success mb-3"><i class="bi bi-patch-check-fill"></i> Onaylı</span>
                    @else
                    <span class="badge bg-warning mb-3">Onay Bekliyor</span>
                    @endif

                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <strong class="d-block">{{ $brand->complaints_count }}</strong>
                            <small class="text-muted">Şikayet</small>
                        </div>
                        <div class="col-6">
                            <strong class="d-block">{{ $brand->response_rate }}%</strong>
                            <small class="text-muted">Yanıt Oranı</small>
                        </div>
                    </div>

                    @if(!$brand->is_verified)
                    <form action="{{ route('admin.brands.verify', $brand) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Markayı Onayla
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header"><h6 class="mb-0">Bilgiler</h6></div>
                <div class="card-body">
                    <div class="mb-2"><strong>Kategori:</strong> {{ $brand->category->name }}</div>
                    <div class="mb-2"><strong>Web Sitesi:</strong> <a href="{{ $brand->website }}" target="_blank">{{ $brand->website }}</a></div>
                    @if($brand->email)
                    <div class="mb-2"><strong>E-posta:</strong> {{ $brand->email }}</div>
                    @endif
                    @if($brand->phone)
                    <div class="mb-2"><strong>Telefon:</strong> {{ $brand->phone }}</div>
                    @endif
                    <div class="mb-2"><strong>Kayıt:</strong> {{ $brand->created_at->format('d.m.Y') }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header"><h6 class="mb-0">Son Şikayetler</h6></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Başlık</th>
                                    <th>Kullanıcı</th>
                                    <th>Durum</th>
                                    <th>Tarih</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($brand->complaints()->latest()->take(10)->get() as $complaint)
                                <tr>
                                    <td>{{ Str::limit($complaint->title, 50) }}</td>
                                    <td>{{ $complaint->user->name }}</td>
                                    <td><span class="badge bg-{{ $complaint->status_color }}">{{ $complaint->status_label }}</span></td>
                                    <td>{{ $complaint->created_at->format('d.m.Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.complaints.show', $complaint) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Şikayet bulunamadı.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
