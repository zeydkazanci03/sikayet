@extends('layouts.admin')

@section('title', 'Sistem Ayarları')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-gear"></i> Sistem Ayarları</h1>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-md-3">
            {{-- Ayar Menüsü --}}
            <div class="list-group">
                <a href="#general" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                    <i class="bi bi-gear"></i> Genel Ayarlar
                </a>
                <a href="#email" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="bi bi-envelope"></i> E-posta Ayarları
                </a>
                <a href="#seo" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="bi bi-search"></i> SEO Ayarları
                </a>
                <a href="#social" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="bi bi-share"></i> Sosyal Medya
                </a>
                <a href="#moderation" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="bi bi-shield-check"></i> Moderasyon
                </a>
                <a href="#maintenance" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="bi bi-tools"></i> Bakım Modu
                </a>
            </div>
        </div>

        <div class="col-md-9">
            <div class="tab-content">
                {{-- Genel Ayarlar --}}
                <div class="tab-pane fade show active" id="general">
                    <div class="card shadow">
                        <div class="card-header"><h5 class="mb-0">Genel Ayarlar</h5></div>
                        <div class="card-body">
                            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="section" value="general">

                                <div class="mb-3">
                                    <label for="site_name" class="form-label">Site Adı</label>
                                    <input type="text" name="site_name" id="site_name" class="form-control" value="{{ $settings['site_name'] ?? '' }}">
                                </div>

                                <div class="mb-3">
                                    <label for="site_description" class="form-label">Site Açıklaması</label>
                                    <textarea name="site_description" id="site_description" rows="3" class="form-control">{{ $settings['site_description'] ?? '' }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="logo" class="form-label">Logo</label>
                                    <input type="file" name="logo" id="logo" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label for="favicon" class="form-label">Favicon</label>
                                    <input type="file" name="favicon" id="favicon" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label for="contact_email" class="form-label">İletişim E-posta</label>
                                    <input type="email" name="contact_email" id="contact_email" class="form-control" value="{{ $settings['contact_email'] ?? '' }}">
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check"></i> Kaydet
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- E-posta Ayarları --}}
                <div class="tab-pane fade" id="email">
                    <div class="card shadow">
                        <div class="card-header"><h5 class="mb-0">E-posta Ayarları</h5></div>
                        <div class="card-body">
                            <form action="{{ route('admin.settings.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="section" value="email">

                                <div class="mb-3">
                                    <label for="mail_driver" class="form-label">Mail Driver</label>
                                    <select name="mail_driver" id="mail_driver" class="form-select">
                                        <option value="smtp">SMTP</option>
                                        <option value="sendmail">Sendmail</option>
                                        <option value="mailgun">Mailgun</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="mail_host" class="form-label">SMTP Host</label>
                                    <input type="text" name="mail_host" id="mail_host" class="form-control" value="{{ $settings['mail_host'] ?? '' }}">
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="mail_port" class="form-label">SMTP Port</label>
                                        <input type="text" name="mail_port" id="mail_port" class="form-control" value="{{ $settings['mail_port'] ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mail_encryption" class="form-label">Encryption</label>
                                        <select name="mail_encryption" id="mail_encryption" class="form-select">
                                            <option value="tls">TLS</option>
                                            <option value="ssl">SSL</option>
                                        </select>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check"></i> Kaydet
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- SEO Ayarları --}}
                <div class="tab-pane fade" id="seo">
                    <div class="card shadow">
                        <div class="card-header"><h5 class="mb-0">SEO Ayarları</h5></div>
                        <div class="card-body">
                            <form action="{{ route('admin.settings.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="section" value="seo">

                                <div class="mb-3">
                                    <label for="meta_title" class="form-label">Meta Title</label>
                                    <input type="text" name="meta_title" id="meta_title" class="form-control" value="{{ $settings['meta_title'] ?? '' }}">
                                </div>

                                <div class="mb-3">
                                    <label for="meta_description" class="form-label">Meta Description</label>
                                    <textarea name="meta_description" id="meta_description" rows="3" class="form-control">{{ $settings['meta_description'] ?? '' }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                    <input type="text" name="meta_keywords" id="meta_keywords" class="form-control" value="{{ $settings['meta_keywords'] ?? '' }}">
                                </div>

                                <div class="mb-3">
                                    <label for="google_analytics" class="form-label">Google Analytics ID</label>
                                    <input type="text" name="google_analytics" id="google_analytics" class="form-control" value="{{ $settings['google_analytics'] ?? '' }}">
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check"></i> Kaydet
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Sosyal Medya --}}
                <div class="tab-pane fade" id="social">
                    <div class="card shadow">
                        <div class="card-header"><h5 class="mb-0">Sosyal Medya</h5></div>
                        <div class="card-body">
                            <form action="{{ route('admin.settings.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="section" value="social">

                                <div class="mb-3">
                                    <label for="facebook_url" class="form-label">Facebook URL</label>
                                    <input type="url" name="facebook_url" id="facebook_url" class="form-control" value="{{ $settings['facebook_url'] ?? '' }}">
                                </div>

                                <div class="mb-3">
                                    <label for="twitter_url" class="form-label">Twitter URL</label>
                                    <input type="url" name="twitter_url" id="twitter_url" class="form-control" value="{{ $settings['twitter_url'] ?? '' }}">
                                </div>

                                <div class="mb-3">
                                    <label for="instagram_url" class="form-label">Instagram URL</label>
                                    <input type="url" name="instagram_url" id="instagram_url" class="form-control" value="{{ $settings['instagram_url'] ?? '' }}">
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check"></i> Kaydet
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Moderasyon --}}
                <div class="tab-pane fade" id="moderation">
                    <div class="card shadow">
                        <div class="card-header"><h5 class="mb-0">Moderasyon Ayarları</h5></div>
                        <div class="card-body">
                            <form action="{{ route('admin.settings.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="section" value="moderation">

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="auto_approve" id="auto_approve" class="form-check-input" {{ ($settings['auto_approve'] ?? false) ? 'checked' : '' }}>
                                        <label for="auto_approve" class="form-check-label">Şikayetleri otomatik onayla</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="require_verification" id="require_verification" class="form-check-input" {{ ($settings['require_verification'] ?? false) ? 'checked' : '' }}>
                                        <label for="require_verification" class="form-check-label">E-posta doğrulaması gerekli</label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check"></i> Kaydet
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Bakım Modu --}}
                <div class="tab-pane fade" id="maintenance">
                    <div class="card shadow">
                        <div class="card-header"><h5 class="mb-0">Bakım Modu</h5></div>
                        <div class="card-body">
                            <form action="{{ route('admin.settings.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="section" value="maintenance">

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="maintenance_mode" id="maintenance_mode" class="form-check-input" {{ ($settings['maintenance_mode'] ?? false) ? 'checked' : '' }}>
                                        <label for="maintenance_mode" class="form-check-label">Bakım modunu aktifleştir</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="maintenance_message" class="form-label">Bakım Modu Mesajı</label>
                                    <textarea name="maintenance_message" id="maintenance_message" rows="3" class="form-control">{{ $settings['maintenance_message'] ?? '' }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check"></i> Kaydet
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
