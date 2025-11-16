@extends('layouts.frontend')

@section('title', 'Profili Düzenle')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-person-gear"></i> Profili Düzenle</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <x-form-errors />

                    <form action="{{ route('frontend.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Profil Fotoğrafı --}}
                        <div class="text-center mb-4">
                            <img src="{{ auth()->user()->avatar_url }}" id="avatarPreview" class="rounded-circle mb-3" width="120" height="120" alt="Avatar">
                            <div>
                                <label for="avatar" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-upload"></i> Fotoğraf Değiştir
                                </label>
                                <input type="file" name="avatar" id="avatar" class="d-none" accept="image/*">
                            </div>
                        </div>

                        {{-- Kişisel Bilgiler --}}
                        <h5 class="mb-3">Kişisel Bilgiler</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', auth()->user()->name) }}" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="username" class="form-label">Kullanıcı Adı <span class="text-danger">*</span></label>
                                <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror"
                                       value="{{ old('username', auth()->user()->username) }}" required>
                                @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', auth()->user()->email) }}" required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Telefon</label>
                            <input type="tel" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', auth()->user()->phone) }}" placeholder="+90 5XX XXX XX XX">
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="bio" class="form-label">Biyografi</label>
                            <textarea name="bio" id="bio" rows="3" class="form-control @error('bio') is-invalid @enderror"
                                      placeholder="Kendinizden kısaca bahsedin...">{{ old('bio', auth()->user()->bio) }}</textarea>
                            <div class="form-text">Maksimum 500 karakter</div>
                            @error('bio')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="location" class="form-label">Konum</label>
                                <input type="text" name="location" id="location" class="form-control"
                                       value="{{ old('location', auth()->user()->location) }}" placeholder="Şehir, Ülke">
                            </div>
                            <div class="col-md-6">
                                <label for="website" class="form-label">Web Sitesi</label>
                                <input type="url" name="website" id="website" class="form-control"
                                       value="{{ old('website', auth()->user()->website) }}" placeholder="https://example.com">
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Gizlilik Ayarları --}}
                        <h5 class="mb-3">Gizlilik Ayarları</h5>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="profile_public" id="profile_public" class="form-check-input"
                                       value="1" {{ old('profile_public', auth()->user()->profile_public) ? 'checked' : '' }}>
                                <label for="profile_public" class="form-check-label">
                                    Profilimi herkese açık yap
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="show_email" id="show_email" class="form-check-input"
                                       value="1" {{ old('show_email', auth()->user()->show_email) ? 'checked' : '' }}>
                                <label for="show_email" class="form-check-label">
                                    E-posta adresimi göster
                                </label>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Bildirim Tercihleri --}}
                        <h5 class="mb-3">Bildirim Tercihleri</h5>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="email_notifications" id="email_notifications" class="form-check-input"
                                       value="1" {{ old('email_notifications', auth()->user()->email_notifications) ? 'checked' : '' }}>
                                <label for="email_notifications" class="form-check-label">
                                    E-posta bildirimleri al
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="push_notifications" id="push_notifications" class="form-check-input"
                                       value="1" {{ old('push_notifications', auth()->user()->push_notifications) ? 'checked' : '' }}>
                                <label for="push_notifications" class="form-check-label">
                                    Push bildirimleri al
                                </label>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Şifre Değiştirme --}}
                        <h5 class="mb-3">Şifre Değiştir</h5>
                        <p class="text-muted small">Şifrenizi değiştirmek istemiyorsanız bu alanları boş bırakın.</p>

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mevcut Şifre</label>
                            <input type="password" name="current_password" id="current_password"
                                   class="form-control @error('current_password') is-invalid @enderror">
                            @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Yeni Şifre</label>
                                <input type="password" name="password" id="password"
                                       class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Yeni Şifre (Tekrar)</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                            </div>
                        </div>

                        {{-- Butonlar --}}
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Değişiklikleri Kaydet
                            </button>
                            <a href="{{ route('frontend.profile.show', auth()->user()) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> İptal
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Hesap Silme --}}
            <div class="card shadow-sm mt-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Tehlikeli Bölge</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Hesabınızı silmek kalıcı bir işlemdir ve geri alınamaz.</p>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        <i class="bi bi-trash"></i> Hesabı Sil
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Hesap Silme Modal --}}
<x-modal id="deleteAccountModal" title="Hesabı Sil">
    <form action="{{ route('frontend.profile.destroy') }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Uyarı:</strong> Bu işlem geri alınamaz!
            </div>
            <p>Hesabınızı silmek istediğinizden emin misiniz? Tüm verileriniz kalıcı olarak silinecektir.</p>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Şifrenizi girin:</label>
                <input type="password" name="password" id="confirm_password" class="form-control" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
            <button type="submit" class="btn btn-danger">Hesabı Sil</button>
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script>
// Avatar önizleme
document.getElementById('avatar')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
