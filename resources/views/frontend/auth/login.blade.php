@extends('layouts.frontend')

@section('title', 'Giriş Yap')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="h3">Giriş Yap</h2>
                        <p class="text-muted">Hesabınıza giriş yapın</p>
                    </div>

                    @if(session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif

                    <x-form-errors />

                    <form action="{{ route('login') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta</label>
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="ornek@email.com"
                                   required autofocus>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Şifre</label>
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
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="remember" class="form-check-input" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    Beni hatırla
                                </label>
                            </div>
                            <a href="{{ route('password.request') }}" class="text-decoration-none small">
                                Şifremi unuttum?
                            </a>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Giriş Yap
                            </button>
                        </div>
                    </form>

                    <div class="text-center">
                        <div class="mb-3">
                            <span class="text-muted">veya</span>
                        </div>

                        {{-- Sosyal Medya Giriş --}}
                        <div class="d-grid gap-2 mb-3">
                            <a href="{{ route('social.login', 'google') }}" class="btn btn-outline-danger">
                                <i class="bi bi-google"></i> Google ile Giriş Yap
                            </a>
                            <a href="{{ route('social.login', 'facebook') }}" class="btn btn-outline-primary">
                                <i class="bi bi-facebook"></i> Facebook ile Giriş Yap
                            </a>
                        </div>

                        <hr>

                        <p class="mb-0">
                            Hesabınız yok mu?
                            <a href="{{ route('register') }}" class="text-decoration-none">
                                <strong>Kayıt Olun</strong>
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Bilgilendirme --}}
            <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle"></i>
                <strong>Şikayet yazmak için giriş yapmalısınız.</strong>
                Hesabınız yoksa hemen kayıt olabilirsiniz.
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
</script>
@endpush
