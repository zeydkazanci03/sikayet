@extends('layouts.admin')

@section('title', 'Kullanıcı Detayı - ' . $user->name)

@section('content')
<div class="container-fluid">
    {{-- Sayfa Başlığı --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-person"></i> Kullanıcı Detayı</h1>
        <div>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Düzenle
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Geri
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Kullanıcı Bilgileri --}}
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <img src="{{ $user->avatar_url }}" class="rounded-circle mb-3" width="120" height="120" alt="{{ $user->name }}">
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted">@{{ $user->username }}</p>

                    <div class="mb-3">
                        <span class="badge bg-{{ $user->role_color }} fs-6">{{ $user->role_label }}</span>
                        <span class="badge bg-{{ $user->status_color }} fs-6">{{ $user->status_label }}</span>
                    </div>

                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <strong class="d-block">{{ $user->complaints_count }}</strong>
                            <small class="text-muted">Şikayet</small>
                        </div>
                        <div class="col-4">
                            <strong class="d-block">{{ $user->comments_count }}</strong>
                            <small class="text-muted">Yorum</small>
                        </div>
                        <div class="col-4">
                            <strong class="d-block">{{ $user->followers_count }}</strong>
                            <small class="text-muted">Takipçi</small>
                        </div>
                    </div>

                    {{-- Hızlı İşlemler --}}
                    <div class="d-grid gap-2">
                        @if($user->status === 'active')
                        <form action="{{ route('admin.users.ban', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm w-100" onclick="return confirm('Bu kullanıcıyı engellemek istediğinizden emin misiniz?')">
                                <i class="bi bi-ban"></i> Engelle
                            </button>
                        </form>
                        @else
                        <form action="{{ route('admin.users.unban', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                <i class="bi bi-check-circle"></i> Engeli Kaldır
                            </button>
                        </form>
                        @endif

                        <a href="{{ route('profile.show', $user) }}" class="btn btn-info btn-sm" target="_blank">
                            <i class="bi bi-box-arrow-up-right"></i> Profili Görüntüle
                        </a>
                    </div>
                </div>
            </div>

            {{-- İletişim Bilgileri --}}
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="mb-0">İletişim Bilgileri</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <i class="bi bi-envelope"></i>
                        <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                    </div>
                    @if($user->phone)
                    <div class="mb-2">
                        <i class="bi bi-telephone"></i> {{ $user->phone }}
                    </div>
                    @endif
                    @if($user->location)
                    <div class="mb-2">
                        <i class="bi bi-geo-alt"></i> {{ $user->location }}
                    </div>
                    @endif
                    <div class="mb-2">
                        <i class="bi bi-calendar"></i> Katıldı: {{ $user->created_at->format('d.m.Y') }}
                    </div>
                    <div class="mb-2">
                        <i class="bi bi-clock"></i> Son Giriş: {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Henüz giriş yapmadı' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Ana İçerik --}}
        <div class="col-md-8">
            {{-- Sekmeler --}}
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#complaints">Şikayetleri</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#comments">Yorumları</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#activities">Aktiviteler</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#notes">Notlar</a>
                </li>
            </ul>

            <div class="tab-content">
                {{-- Şikayetler --}}
                <div class="tab-pane fade show active" id="complaints">
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Başlık</th>
                                            <th>Marka</th>
                                            <th>Durum</th>
                                            <th>Tarih</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($user->complaints()->latest()->take(10)->get() as $complaint)
                                        <tr>
                                            <td>{{ Str::limit($complaint->title, 50) }}</td>
                                            <td>{{ $complaint->brand->name }}</td>
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

                {{-- Yorumlar --}}
                <div class="tab-pane fade" id="comments">
                    <div class="card shadow">
                        <div class="card-body">
                            @forelse($user->comments()->latest()->take(10)->get() as $comment)
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('complaints.show', $comment->complaint) }}" target="_blank">
                                        {{ Str::limit($comment->complaint->title, 60) }}
                                    </a>
                                    <small class="text-muted">{{ $comment->created_at->format('d.m.Y H:i') }}</small>
                                </div>
                                <p class="mb-0">{{ $comment->content }}</p>
                            </div>
                            @empty
                            <p class="text-center text-muted">Yorum bulunamadı.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Aktiviteler --}}
                <div class="tab-pane fade" id="activities">
                    <div class="card shadow">
                        <div class="card-body">
                            @forelse($activities as $activity)
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <i class="bi bi-{{ $activity->icon }} text-{{ $activity->color }}"></i>
                                        {{ $activity->description }}
                                    </div>
                                    <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                            @empty
                            <p class="text-center text-muted">Aktivite bulunamadı.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Notlar --}}
                <div class="tab-pane fade" id="notes">
                    <div class="card shadow">
                        <div class="card-body">
                            <form action="{{ route('admin.users.add-note', $user) }}" method="POST" class="mb-3">
                                @csrf
                                <div class="mb-2">
                                    <textarea name="note" class="form-control" rows="3" placeholder="Not ekle..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus"></i> Not Ekle
                                </button>
                            </form>

                            @forelse($user->notes as $note)
                            <div class="border p-3 mb-2 rounded">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>{{ $note->admin->name }}</strong>
                                    <small class="text-muted">{{ $note->created_at->format('d.m.Y H:i') }}</small>
                                </div>
                                <p class="mb-0">{{ $note->content }}</p>
                            </div>
                            @empty
                            <p class="text-center text-muted">Not bulunamadı.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
