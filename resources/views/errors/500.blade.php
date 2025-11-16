@extends('layouts.frontend')

@section('title', '500 - Sunucu Hatası')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page">
                <h1 class="display-1 fw-bold text-danger">500</h1>
                <h2 class="mb-4">Sunucu Hatası</h2>
                <p class="lead text-muted mb-4">
                    Üzgünüz, bir şeyler ters gitti. Teknik ekibimiz durumdan haberdar edildi.
                </p>

                <div class="mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" fill="currentColor" class="bi bi-exclamation-octagon text-danger" viewBox="0 0 16 16">
                        <path d="M4.54.146A.5.5 0 0 1 4.893 0h6.214a.5.5 0 0 1 .353.146l4.394 4.394a.5.5 0 0 1 .146.353v6.214a.5.5 0 0 1-.146.353l-4.394 4.394a.5.5 0 0 1-.353.146H4.893a.5.5 0 0 1-.353-.146L.146 11.46A.5.5 0 0 1 0 11.107V4.893a.5.5 0 0 1 .146-.353L4.54.146zM5.1 1 1 5.1v5.8L5.1 15h5.8l4.1-4.1V5.1L10.9 1H5.1z"/>
                        <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                    </svg>
                </div>

                <div class="alert alert-danger d-inline-block">
                    <i class="bi bi-info-circle"></i>
                    <strong>Ne yapabilirsiniz?</strong>
                    <ul class="mb-0 mt-2 text-start">
                        <li>Sayfayı yenilemeyi deneyin</li>
                        <li>Birkaç dakika sonra tekrar deneyin</li>
                        <li>Ana sayfaya dönüp tekrar deneyin</li>
                    </ul>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                    <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-house"></i> Ana Sayfaya Dön
                    </a>
                    <button onclick="location.reload()" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-arrow-clockwise"></i> Sayfayı Yenile
                    </button>
                </div>

                <div class="mt-5">
                    <p class="text-muted">
                        Sorun devam ederse lütfen <a href="{{ route('contact') }}">iletişime geçin</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
