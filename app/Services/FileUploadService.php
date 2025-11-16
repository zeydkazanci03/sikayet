<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Exception;

/**
 * Dosya yükleme işlemleri servis sınıfı
 * Service class for file upload operations
 */
class FileUploadService
{
    protected string $disk = 'public';
    protected array $allowedMimes = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
        'all' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
    ];

    /**
     * Dosya yükle
     * Upload file
     */
    public function upload(UploadedFile $file, string $path = 'uploads', ?string $type = 'all'): string
    {
        try {
            // Dosya uzantısını kontrol et
            // Validate file extension
            $this->validateFile($file, $type);

            // Benzersiz dosya adı oluştur
            // Generate unique filename
            $filename = $this->generateUniqueFilename($file);

            // Dosya yolunu oluştur
            // Build file path
            $filePath = $path . '/' . $filename;

            // Resim ise optimize et
            // Optimize if image
            if ($this->isImage($file)) {
                $this->uploadImage($file, $filePath);
            } else {
                // Dosyayı yükle
                // Upload file
                Storage::disk($this->disk)->put($filePath, file_get_contents($file->getRealPath()));
            }

            return $filePath;

        } catch (Exception $e) {
            throw new Exception("Dosya yükleme hatası: " . $e->getMessage());
        }
    }

    /**
     * Resim yükle ve optimize et
     * Upload and optimize image
     */
    protected function uploadImage(UploadedFile $file, string $path): void
    {
        // Resmi yükle ve optimize et
        // Load and optimize image
        $image = Image::make($file->getRealPath());

        // Maksimum boyutları kontrol et
        // Check maximum dimensions
        $maxWidth = 2000;
        $maxHeight = 2000;

        if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
            $image->resize($maxWidth, $maxHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // JPEG kalitesini ayarla
        // Set JPEG quality
        $image->encode($file->getClientOriginalExtension(), 85);

        // Dosyayı kaydet
        // Save file
        Storage::disk($this->disk)->put($path, (string) $image);
    }

    /**
     * Küçük resim oluştur
     * Create thumbnail
     */
    public function createThumbnail(string $path, int $width = 200, int $height = 200): string
    {
        try {
            // Orijinal resmi yükle
            // Load original image
            $fullPath = Storage::disk($this->disk)->path($path);
            $image = Image::make($fullPath);

            // Küçük resim oluştur
            // Create thumbnail
            $image->fit($width, $height);

            // Küçük resim yolunu oluştur
            // Build thumbnail path
            $pathInfo = pathinfo($path);
            $thumbnailPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];

            // Küçük resmi kaydet
            // Save thumbnail
            Storage::disk($this->disk)->put($thumbnailPath, (string) $image);

            return $thumbnailPath;

        } catch (Exception $e) {
            throw new Exception("Küçük resim oluşturma hatası: " . $e->getMessage());
        }
    }

    /**
     * Dosyayı sil
     * Delete file
     */
    public function delete(string $path): bool
    {
        try {
            if (Storage::disk($this->disk)->exists($path)) {
                return Storage::disk($this->disk)->delete($path);
            }
            return false;
        } catch (Exception $e) {
            throw new Exception("Dosya silme hatası: " . $e->getMessage());
        }
    }

    /**
     * Dosya varlığını kontrol et
     * Check if file exists
     */
    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Dosya URL'sini al
     * Get file URL
     */
    public function getUrl(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Dosya boyutunu al
     * Get file size
     */
    public function getSize(string $path): int
    {
        return Storage::disk($this->disk)->size($path);
    }

    /**
     * Dosyayı doğrula
     * Validate file
     */
    protected function validateFile(UploadedFile $file, string $type): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = $this->allowedMimes[$type] ?? $this->allowedMimes['all'];

        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception("Bu dosya tipi yüklenemez. İzin verilen formatlar: " . implode(', ', $allowedExtensions));
        }
    }

    /**
     * Dosyanın resim olup olmadığını kontrol et
     * Check if file is an image
     */
    protected function isImage(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return in_array($extension, $this->allowedMimes['image']);
    }

    /**
     * Benzersiz dosya adı oluştur
     * Generate unique filename
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(40);
        return $filename . '.' . $extension;
    }

    /**
     * Dosyayı base64'ten yükle
     * Upload file from base64
     */
    public function uploadFromBase64(string $base64Data, string $path = 'uploads', string $filename = null): string
    {
        try {
            // Base64 verisini decode et
            // Decode base64 data
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
                $extension = $matches[1];
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            } else {
                $extension = 'png';
            }

            $data = base64_decode($base64Data);

            if ($data === false) {
                throw new Exception("Base64 verisi decode edilemedi.");
            }

            // Dosya adı oluştur
            // Generate filename
            if (!$filename) {
                $filename = Str::random(40) . '.' . $extension;
            }

            // Dosya yolunu oluştur
            // Build file path
            $filePath = $path . '/' . $filename;

            // Dosyayı kaydet
            // Save file
            Storage::disk($this->disk)->put($filePath, $data);

            return $filePath;

        } catch (Exception $e) {
            throw new Exception("Base64 dosya yükleme hatası: " . $e->getMessage());
        }
    }

    /**
     * Klasördeki tüm dosyaları sil
     * Delete all files in directory
     */
    public function deleteDirectory(string $directory): bool
    {
        try {
            return Storage::disk($this->disk)->deleteDirectory($directory);
        } catch (Exception $e) {
            throw new Exception("Klasör silme hatası: " . $e->getMessage());
        }
    }
}
