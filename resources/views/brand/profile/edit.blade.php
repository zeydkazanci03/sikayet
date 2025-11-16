@extends('layouts.brand')

@section('title', 'Profil Ayarları')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-building-gear"></i> Profil Ayarları</h1>
        <a href="{{ route('brand.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Geri
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <x-form-errors />

                    <form action="{{ route('brand.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Logo --}}
                        <div class="text-center mb-4">
                            <img src="{{ $brand->logo_url }}" id="logoPreview" class="img-fluid mb-3" style="max-height: 120px;" alt="Logo">
                            <div>
                                <label for="logo" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-upload"></i> Logo Değiştir
                                </label>
                                <input type="file" name="logo" id="logo" class="d-none" accept="image/*">
                            </div>
                        </div>

                        {{-- Temel Bilgiler --}}
                        <h5 class="mb-3">Temel Bilgiler</h5>

                        <div class="mb-3">
                            <label for="name" class="form-label">Marka Adı <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $brand->name) }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Açıklama</label>
                            <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $brand->description) }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $brand->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        {{-- İletişim Bilgileri --}}
                        <h5 class="mb-3">İletişim Bilgileri</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">E-posta</label>
                                <input type="email" name="email" id="email" class="form-control"
                                       value="{{ old('email', $brand->email) }}">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Telefon</label>
                                <input type="tel" name="phone" id="phone" class="form-control"
                                       value="{{ old('phone', $brand->phone) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="website" class="form-label">Web Sitesi</label>
                            <input type="url" name="website" id="website" class="form-control"
                                   value="{{ old('website', $brand->website) }}" placeholder="https://example.com">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Adres</label>
                            <textarea name="address" id="address" rows="2" class="form-control">{{ old('address', $brand->address) }}</textarea>
                        </div>

                        <hr class="my-4">

                        {{-- Sosyal Medya --}}
                        <h5 class="mb-3">Sosyal Medya</h5>

                        <div class="mb-3">
                            <label for="facebook" class="form-label">Facebook</label>
                            <input type="url" name="social_media[facebook]" id="facebook" class="form-control"
                                   value="{{ old('social_media.facebook', $brand->social_media['facebook'] ?? '') }}" placeholder="https://facebook.com/...">
                        </div>

                        <div class="mb-3">
                            <label for="twitter" class="form-label">Twitter</label>
                            <input type="url" name="social_media[twitter]" id="twitter" class="form-control"
                                   value="{{ old('social_media.twitter', $brand->social_media['twitter'] ?? '') }}" placeholder="https://twitter.com/...">
                        </div>

                        <div class="mb-3">
                            <label for="instagram" class="form-label">Instagram</label>
                            <input type="url" name="social_media[instagram]" id="instagram" class="form-control"
                                   value="{{ old('social_media.instagram', $brand->social_media['instagram'] ?? '') }}" placeholder="https://instagram.com/...">
                        </div>

                        <hr class="my-4">

                        {{-- Bildirim Tercihleri --}}
                        <h5 class="mb-3">Bildirim Tercihleri</h5>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="email_notifications" id="email_notifications" class="form-check-input"
                                       value="1" {{ old('email_notifications', $brand->email_notifications) ? 'checked' : '' }}>
                                <label for="email_notifications" class="form-check-label">
                                    Yeni şikayet geldiğinde e-posta bildirimi al
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="comment_notifications" id="comment_notifications" class="form-check-input"
                                       value="1" {{ old('comment_notifications', $brand->comment_notifications) ? 'checked' : '' }}>
                                <label for="comment_notifications" class="form-check-label">
                                    Yeni yorum geldiğinde bildirim al
                                </label>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Butonlar --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Değişiklikleri Kaydet
                            </button>
                            <a href="{{ route('brand.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> İptal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('logo')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
