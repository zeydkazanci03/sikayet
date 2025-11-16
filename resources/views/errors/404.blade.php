@extends('layouts.frontend')

@section('title', '404 - Sayfa Bulunamadı')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page">
                <h1 class="display-1 fw-bold text-primary">404</h1>
                <h2 class="mb-4">Sayfa Bulunamadı</h2>
                <p class="lead text-muted mb-4">
                    Aradığınız sayfa mevcut değil veya taşınmış olabilir.
                </p>

                <div class="mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" fill="currentColor" class="bi bi-exclamation-triangle text-warning" viewBox="0 0 16 16">
                        <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
                        <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/>
                    </svg>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-house"></i> Ana Sayfaya Dön
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-arrow-left"></i> Geri Dön
                    </a>
                </div>

                <div class="mt-5">
                    <h5 class="mb-3">Popüler Sayfalar</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="{{ route('complaints.index') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-megaphone"></i> Şikayetler
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('brands.index') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-building"></i> Markalar
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('blog.index') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-newspaper"></i> Blog
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
