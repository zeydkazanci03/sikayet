@extends('layouts.frontend')

@section('title', 'Yeni Şikayet Oluştur')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-megaphone"></i> Yeni Şikayet Oluştur</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <x-form-errors />

                    <form action="{{ route('complaints.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Marka Seçimi --}}
                        <div class="mb-3">
                            <label for="brand_id" class="form-label">Marka <span class="text-danger">*</span></label>
                            <select name="brand_id" id="brand_id" class="form-select @error('brand_id') is-invalid @enderror" required>
                                <option value="">Marka seçin...</option>
                                @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('brand_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Kategori Seçimi --}}
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">Kategori seçin...</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Başlık --}}
                        <div class="mb-3">
                            <label for="title" class="form-label">Şikayet Başlığı <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}" placeholder="Kısa ve öz bir başlık yazın..." maxlength="255" required>
                            <div class="form-text">Maksimum 255 karakter</div>
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Açıklama --}}
                        <div class="mb-3">
                            <label for="description" class="form-label">Şikayet Detayı <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" rows="6"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Şikayetinizi detaylı bir şekilde açıklayın..." required>{{ old('description') }}</textarea>
                            <div class="form-text">Yaşadığınız sorunu detaylı bir şekilde anlatın.</div>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Medya Yükleme --}}
                        <div class="mb-3">
                            <label for="media" class="form-label">Fotoğraf/Dosya Ekle</label>
                            <input type="file" name="media[]" id="media" class="form-control @error('media') is-invalid @enderror"
                                   multiple accept="image/*,.pdf">
                            <div class="form-text">Maksimum 5 dosya yükleyebilirsiniz (JPG, PNG, PDF - Her biri max 5MB)</div>
                            @error('media')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Etiketler --}}
                        <div class="mb-3">
                            <label for="tags" class="form-label">Etiketler</label>
                            <input type="text" name="tags" id="tags" class="form-control"
                                   value="{{ old('tags') }}" placeholder="etiket1, etiket2, etiket3">
                            <div class="form-text">Virgül ile ayırarak etiket ekleyebilirsiniz</div>
                        </div>

                        {{-- Konum --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="city" class="form-label">Şehir</label>
                                <select name="city" id="city" class="form-select">
                                    <option value="">Şehir seçin...</option>
                                    @foreach($cities as $city)
                                    <option value="{{ $city }}" {{ old('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="district" class="form-label">İlçe</label>
                                <input type="text" name="district" id="district" class="form-control" value="{{ old('district') }}">
                            </div>
                        </div>

                        {{-- Gizlilik --}}
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_anonymous" id="is_anonymous" class="form-check-input" value="1" {{ old('is_anonymous') ? 'checked' : '' }}>
                                <label for="is_anonymous" class="form-check-label">
                                    Anonim olarak paylaş (İsminiz gizlenir)
                                </label>
                            </div>
                        </div>

                        {{-- Kullanım Koşulları --}}
                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" name="terms" id="terms" class="form-check-input" required>
                                <label for="terms" class="form-check-label">
                                    <a href="{{ route('terms') }}" target="_blank">Kullanım şartlarını</a> ve
                                    <a href="{{ route('privacy') }}" target="_blank">gizlilik politikasını</a> kabul ediyorum.
                                    <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>

                        {{-- Gönder Butonu --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send"></i> Şikayeti Yayınla
                            </button>
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> İptal
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Bilgilendirme --}}
            <div class="alert alert-info mt-4">
                <h6><i class="bi bi-info-circle"></i> Şikayet Oluştururken Dikkat Edilmesi Gerekenler</h6>
                <ul class="mb-0">
                    <li>Şikayetiniz yayınlanmadan önce moderatörlerimiz tarafından incelenecektir.</li>
                    <li>Küfür, hakaret ve yasal olmayan içerikler yayınlanmayacaktır.</li>
                    <li>Gerçek deneyimlerinizi paylaşın, doğru bilgiler verin.</li>
                    <li>Mümkünse fotoğraf veya belge ekleyin.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Başlık karakter sayacı
document.getElementById('title')?.addEventListener('input', function() {
    const remaining = 255 - this.value.length;
    const helpText = this.nextElementSibling;
    helpText.textContent = `Kalan: ${remaining} karakter`;
});

// Select2 veya benzeri gelişmiş seçici eklenebilir
</script>
@endpush
