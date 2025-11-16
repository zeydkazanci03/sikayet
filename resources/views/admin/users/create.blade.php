@extends('layouts.admin')

@section('title', 'Yeni Kullanıcı Oluştur')

@section('content')
<div class="container-fluid">
    {{-- Sayfa Başlığı --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-person-plus"></i> Yeni Kullanıcı Oluştur</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Geri
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body">
                    <x-form-errors />

                    <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Temel Bilgiler --}}
                        <h5 class="mb-3">Temel Bilgiler</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="username" class="form-label">Kullanıcı Adı <span class="text-danger">*</span></label>
                                <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror"
                                       value="{{ old('username') }}" required>
                                @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">E-posta <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Telefon</label>
                                <input type="tel" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}">
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Şifre <span class="text-danger">*</span></label>
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Şifre Tekrar <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Rol ve Durum --}}
                        <h5 class="mb-3">Rol ve Durum</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="role" class="form-label">Rol <span class="text-danger">*</span></label>
                                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Kullanıcı</option>
                                    <option value="brand" {{ old('role') == 'brand' ? 'selected' : '' }}>Marka</option>
                                    <option value="moderator" {{ old('role') == 'moderator' ? 'selected' : '' }}>Moderatör</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                                @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Durum <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Pasif</option>
                                    <option value="banned" {{ old('status') == 'banned' ? 'selected' : '' }}>Yasaklı</option>
                                </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Ek Bilgiler --}}
                        <h5 class="mb-3">Ek Bilgiler</h5>

                        <div class="mb-3">
                            <label for="bio" class="form-label">Biyografi</label>
                            <textarea name="bio" id="bio" rows="3" class="form-control">{{ old('bio') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Konum</label>
                            <input type="text" name="location" id="location" class="form-control" value="{{ old('location') }}">
                        </div>

                        <div class="mb-3">
                            <label for="avatar" class="form-label">Profil Fotoğrafı</label>
                            <input type="file" name="avatar" id="avatar" class="form-control" accept="image/*">
                        </div>

                        <hr class="my-4">

                        {{-- Butonlar --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Kullanıcı Oluştur
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
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
