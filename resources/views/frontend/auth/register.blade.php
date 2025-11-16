@extends('layouts.frontend')

@section('title', 'Kayıt Ol')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="h3">Kayıt Ol</h2>
                        <p class="text-muted">Yeni hesap oluşturun</p>
                    </div>

                    <x-form-errors />

                    <form action="{{ route('register') }}" method="POST">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}"
                                       placeholder="Adınız ve soyadınız"
                                       required autofocus>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="username" class="form-label">Kullanıcı Adı <span class="text-danger">*</span></label>
                                <input type="text" name="username" id="username"
                                       class="form-control @error('username') is-invalid @enderror"
                                       value="{{ old('username') }}"
                                       placeholder="kullaniciadi"
                                       required>
                                @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="ornek@email.com"
                                   required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Telefon</label>
                            <input type="tel" name="phone" id="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone') }}"
                                   placeholder="+90 5XX XXX XX XX">
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Şifre <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="••••••••"
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">En az 8 karakter</small>
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Şifre Tekrar <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="form-control"
                                       placeholder="••••••••"
                                       required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kullanıcı Tipi <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input type="radio" name="user_type" class="form-check-input" id="typeUser"
                                       value="user" {{ old('user_type', 'user') == 'user' ? 'checked' : '' }}>
                                <label class="form-check-label" for="typeUser">
                                    <strong>Bireysel Kullanıcı</strong>
                                    <br><small class="text-muted">Şikayet yazmak ve takip etmek için</small>
                                </label>
                            </div>
                            <div class="form-check mt-2">
                                <input type="radio" name="user_type" class="form-check-input" id="typeBrand"
                                       value="brand" {{ old('user_type') == 'brand' ? 'checked' : '' }}>
                                <label class="form-check-label" for="typeBrand">
                                    <strong>Marka/Firma</strong>
                                    <br><small class="text-muted">Şikayetleri yanıtlamak için</small>
                                </label>
                            </div>
                        </div>

                        {{-- Marka için ek alanlar --}}
                        <div id="brandFields" class="d-none">
                            <div class="mb-3">
                                <label for="company_name" class="form-label">Firma Adı</label>
                                <input type="text" name="company_name" id="company_name"
                                       class="form-control"
                                       value="{{ old('company_name') }}"
                                       placeholder="Firma adınız">
                            </div>
                            <div class="mb-3">
                                <label for="tax_number" class="form-label">Vergi Numarası</label>
                                <input type="text" name="tax_number" id="tax_number"
                                       class="form-control"
                                       value="{{ old('tax_number') }}"
                                       placeholder="XXXXXXXXXX">
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="terms" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    <a href="{{ route('terms') }}" target="_blank">Kullanım Koşullarını</a> ve
                                    <a href="{{ route('privacy') }}" target="_blank">Gizlilik Politikasını</a> kabul ediyorum.
                                    <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="marketing" class="form-check-input" id="marketing">
                                <label class="form-check-label" for="marketing">
                                    E-posta ile bildirim ve kampanya almak istiyorum.
                                </label>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-person-plus"></i> Kayıt Ol
                            </button>
                        </div>
                    </form>

                    <div class="text-center">
                        <div class="mb-3">
                            <span class="text-muted">veya</span>
                        </div>

                        {{-- Sosyal Medya Kayıt --}}
                        <div class="d-grid gap-2 mb-3">
                            <a href="{{ route('social.login', 'google') }}" class="btn btn-outline-danger">
                                <i class="bi bi-google"></i> Google ile Kayıt Ol
                            </a>
                            <a href="{{ route('social.login', 'facebook') }}" class="btn btn-outline-primary">
                                <i class="bi bi-facebook"></i> Facebook ile Kayıt Ol
                            </a>
                        </div>

                        <hr>

                        <p class="mb-0">
                            Zaten hesabınız var mı?
                            <a href="{{ route('login') }}" class="text-decoration-none">
                                <strong>Giriş Yapın</strong>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Şifre göster/gizle
document.getElementById('togglePassword')?.addEventListener('click', function() {
    const password = document.getElementById('password');
    const icon = this.querySelector('i');

    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
});

// Marka alanlarını göster/gizle
document.querySelectorAll('input[name="user_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const brandFields = document.getElementById('brandFields');
        if (this.value === 'brand') {
            brandFields.classList.remove('d-none');
        } else {
            brandFields.classList.add('d-none');
        }
    });
});

// Sayfa yüklendiğinde kontrol et
if (document.querySelector('input[name="user_type"]:checked')?.value === 'brand') {
    document.getElementById('brandFields').classList.remove('d-none');
}
</script>
@endpush
