@extends('layouts.admin')

@section('title', 'Veri Dışa Aktarma')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-download"></i> Veri Dışa Aktarma</h1>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        {{-- Şikayetler --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-megaphone"></i> Şikayetler</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Şikayet verilerini dışa aktarın</p>
                    <form action="{{ route('admin.export.complaints') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="complaints_status" class="form-label">Durum</label>
                            <select name="status" id="complaints_status" class="form-select">
                                <option value="">Tümü</option>
                                <option value="pending">Beklemede</option>
                                <option value="approved">Onaylandı</option>
                                <option value="resolved">Çözüldü</option>
                                <option value="rejected">Reddedildi</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="complaints_start_date" class="form-label">Başlangıç Tarihi</label>
                            <input type="date" name="start_date" id="complaints_start_date" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="complaints_end_date" class="form-label">Bitiş Tarihi</label>
                            <input type="date" name="end_date" id="complaints_end_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Format</label>
                            <div class="btn-group w-100" role="group">
                                <button type="submit" name="format" value="excel" class="btn btn-success">
                                    <i class="bi bi-file-earmark-excel"></i> Excel
                                </button>
                                <button type="submit" name="format" value="csv" class="btn btn-info">
                                    <i class="bi bi-filetype-csv"></i> CSV
                                </button>
                                <button type="submit" name="format" value="pdf" class="btn btn-danger">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Kullanıcılar --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Kullanıcılar</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Kullanıcı verilerini dışa aktarın</p>
                    <form action="{{ route('admin.export.users') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="users_role" class="form-label">Rol</label>
                            <select name="role" id="users_role" class="form-select">
                                <option value="">Tümü</option>
                                <option value="admin">Admin</option>
                                <option value="moderator">Moderatör</option>
                                <option value="brand">Marka</option>
                                <option value="user">Kullanıcı</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="users_start_date" class="form-label">Kayıt Başlangıç</label>
                            <input type="date" name="start_date" id="users_start_date" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="users_end_date" class="form-label">Kayıt Bitiş</label>
                            <input type="date" name="end_date" id="users_end_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Format</label>
                            <div class="btn-group w-100" role="group">
                                <button type="submit" name="format" value="excel" class="btn btn-success">
                                    <i class="bi bi-file-earmark-excel"></i> Excel
                                </button>
                                <button type="submit" name="format" value="csv" class="btn btn-info">
                                    <i class="bi bi-filetype-csv"></i> CSV
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Markalar --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Markalar</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Marka verilerini dışa aktarın</p>
                    <form action="{{ route('admin.export.brands') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="brands_category" class="form-label">Kategori</label>
                            <select name="category_id" id="brands_category" class="form-select">
                                <option value="">Tümü</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Durum</label>
                            <div class="form-check">
                                <input type="checkbox" name="verified_only" class="form-check-input" id="verified_only">
                                <label class="form-check-label" for="verified_only">
                                    Sadece Onaylı Markalar
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Format</label>
                            <div class="btn-group w-100" role="group">
                                <button type="submit" name="format" value="excel" class="btn btn-success">
                                    <i class="bi bi-file-earmark-excel"></i> Excel
                                </button>
                                <button type="submit" name="format" value="csv" class="btn btn-info">
                                    <i class="bi bi-filetype-csv"></i> CSV
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Raporlar --}}
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Raporlar</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Özel raporlar oluşturun</p>
                    <form action="{{ route('admin.export.report') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="report_type" class="form-label">Rapor Tipi</label>
                            <select name="report_type" id="report_type" class="form-select" required>
                                <option value="">Seçin...</option>
                                <option value="monthly_summary">Aylık Özet</option>
                                <option value="brand_performance">Marka Performansı</option>
                                <option value="user_activity">Kullanıcı Aktiviteleri</option>
                                <option value="category_stats">Kategori İstatistikleri</option>
                                <option value="moderation_log">Moderasyon Geçmişi</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="report_start_date" class="form-label">Başlangıç Tarihi</label>
                            <input type="date" name="start_date" id="report_start_date" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="report_end_date" class="form-label">Bitiş Tarihi</label>
                            <input type="date" name="end_date" id="report_end_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Format</label>
                            <div class="btn-group w-100" role="group">
                                <button type="submit" name="format" value="pdf" class="btn btn-danger">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </button>
                                <button type="submit" name="format" value="excel" class="btn btn-success">
                                    <i class="bi bi-file-earmark-excel"></i> Excel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Bilgilendirme --}}
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        <strong>Bilgi:</strong>
        <ul class="mb-0 mt-2">
            <li>Büyük veri setleri için işlem birkaç dakika sürebilir</li>
            <li>İndirilen dosyalar hassas veri içerir, güvenli bir şekilde saklayın</li>
            <li>Excel formatı en fazla 1.000.000 satır destekler</li>
            <li>PDF raporları özet bilgiler içerir, detaylı veri için Excel kullanın</li>
        </ul>
    </div>
</div>
@endsection
