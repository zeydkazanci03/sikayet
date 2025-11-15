<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Şikayetvar Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    @stack('styles')
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="logo">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Şikayetvar</span>
                </a>
            </div>
            <ul class="list-unstyled components">
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
                <li>
                    <a href="#userSubmenu" data-bs-toggle="collapse"><i class="fas fa-users"></i> Kullanıcılar</a>
                    <ul class="collapse list-unstyled" id="userSubmenu">
                        <li><a href="{{ route('admin.users.index') }}">Tüm Kullanıcılar</a></li>
                        <li><a href="{{ route('admin.users.create') }}">Yeni Kullanıcı</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#complaintSubmenu" data-bs-toggle="collapse"><i class="fas fa-comments"></i> Şikayetler
                        @php $pending = \App\Models\Complaint::where('status', 'pending')->count(); @endphp
                        @if($pending > 0) <span class="badge bg-danger ms-2">{{ $pending }}</span> @endif
                    </a>
                    <ul class="collapse list-unstyled" id="complaintSubmenu">
                        <li><a href="{{ route('admin.complaints.index') }}">Tüm Şikayetler</a></li>
                        <li><a href="{{ route('admin.moderation.queue') }}">Moderasyon Kuyruğu</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#brandSubmenu" data-bs-toggle="collapse"><i class="fas fa-store"></i> Markalar</a>
                    <ul class="collapse list-unstyled" id="brandSubmenu">
                        <li><a href="{{ route('admin.brands.index') }}">Tüm Markalar</a></li>
                        <li><a href="{{ route('admin.brands.create') }}">Yeni Marka</a></li>
                    </ul>
                </li>
                <li><a href="{{ route('admin.categories.index') }}"><i class="fas fa-list"></i> Kategoriler</a></li>
                <li><a href="{{ route('admin.blog.index') }}"><i class="fas fa-blog"></i> Blog</a></li>
                <li><a href="{{ route('admin.pages.index') }}"><i class="fas fa-file"></i> Sayfalar</a></li>
                <li><a href="{{ route('admin.analytics') }}"><i class="fas fa-chart-bar"></i> Analytics</a></li>
                <li><a href="{{ route('admin.settings.index') }}"><i class="fas fa-cog"></i> Ayarlar</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
                <div class="container-fluid">
                    <button class="btn btn-primary" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                    <span class="navbar-text ms-3">Admin Paneli</span>
                    <div class="ms-auto">
                        <span class="me-3">{{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-light">Çıkış</button>
                        </form>
                    </div>
                </div>
            </nav>

            <!-- Breadcrumb & Title -->
            <div class="container-fluid mb-4">
                @yield('breadcrumb')
            </div>

            <!-- Flash Messages -->
            @if($errors->any())
                <div class="container-fluid">
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
                <div class="container-fluid">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            <!-- Content -->
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="{{ asset('assets/js/admin.js') }}"></script>
    @stack('scripts')
</body>
</html>
