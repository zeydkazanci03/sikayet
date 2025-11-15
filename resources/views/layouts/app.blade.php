<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Şikayetvar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/frontend.css') }}">
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-exclamation-triangle"></i> Şikayetvar
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('frontend.complaints.index') }}">Şikayetler</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('frontend.brands.index') }}">Markalar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('frontend.blog.index') }}">Blog</a>
                    </li>
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> {{ auth()->user()->name }}
                                @if(auth()->user()->getUnreadNotificationsCountAttribute() > 0)
                                    <span class="badge bg-danger">{{ auth()->user()->getUnreadNotificationsCountAttribute() }}</span>
                                @endif
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('frontend.profile.show') }}">Profilim</a></li>
                                <li><a class="dropdown-item" href="{{ route('frontend.profile.complaints') }}">Şikayetlerim</a></li>
                                <li><a class="dropdown-item" href="{{ route('frontend.profile.notifications') }}">Bildirimler</a></li>
                                @if(auth()->user()->isAdmin())
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Admin Paneli</a></li>
                                @endif
                                @if(auth()->user()->isBrand())
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('brand.dashboard') }}">Marka Paneli</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Çıkış Yap</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Giriş</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-light btn-sm ms-2" href="{{ route('register') }}">Kayıt</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if($errors->any())
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Hata!</strong>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="py-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Şikayetvar</h5>
                    <p>Türkiye'nin en güvenilir şikayet ve çözüm platformu</p>
                </div>
                <div class="col-md-4">
                    <h5>Linkler</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white text-decoration-none">Hakkında</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Gizlilik Politikası</a></li>
                        <li><a href="#" class="text-white text-decoration-none">İletişim</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Sosyal Medya</h5>
                    <a href="#" class="text-white me-2"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <hr>
            <p class="text-center mb-0">&copy; 2025 Şikayetvar. Tüm hakları saklıdır.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/js/frontend.js') }}"></script>
    @stack('scripts')
</body>
</html>
