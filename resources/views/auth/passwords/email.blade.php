@extends('layouts.app')

@section('title', 'Şifremi Unuttum')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">Şifremi Unuttum</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <p class="text-muted">E-posta adresinizi girin. Şifrenizi sıfırlamanız için bir bağlantı göndereceğiz.</p>

                    <form action="{{ route('password.email') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">E-Posta Adresi</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-warning w-100">Şifre Sıfırlama Linki Gönder</button>
                    </form>

                    <hr>

                    <p class="text-center mb-0">
                        <a href="{{ route('login') }}">Giriş Sayfasına Dön</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
