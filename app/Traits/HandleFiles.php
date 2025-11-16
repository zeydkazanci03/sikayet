<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Dosya işlemleri için trait
 * Trait for file handling utilities
 */
trait HandleFiles
{
    /**
     * Dosya yükle
     * Upload file
     */
    public function uploadFile(UploadedFile $file, string $path = 'uploads', string $disk = 'public'): string
    {
        $filename = $this->generateUniqueFilename($file);
        $filePath = $path . '/' . $filename;

        Storage::disk($disk)->put($filePath, file_get_contents($file->getRealPath()));

        return $filePath;
    }

    /**
     * Birden fazla dosya yükle
     * Upload multiple files
     */
    public function uploadFiles(array $files, string $path = 'uploads', string $disk = 'public'): array
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploadedFiles[] = $this->uploadFile($file, $path, $disk);
            }
        }

        return $uploadedFiles;
    }

    /**
     * Dosyayı sil
     * Delete file
     */
    public function deleteFile(string $path, string $disk = 'public'): bool
    {
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Birden fazla dosyayı sil
     * Delete multiple files
     */
    public function deleteFiles(array $paths, string $disk = 'public'): void
    {
        foreach ($paths as $path) {
            $this->deleteFile($path, $disk);
        }
    }

    /**
     * Dosya var mı kontrol et
     * Check if file exists
     */
    public function fileExists(string $path, string $disk = 'public'): bool
    {
        return Storage::disk($disk)->exists($path);
    }

    /**
     * Dosya URL'sini al
     * Get file URL
     */
    public function getFileUrl(string $path, string $disk = 'public'): string
    {
        return Storage::disk($disk)->url($path);
    }

    /**
     * Dosya boyutunu al
     * Get file size
     */
    public function getFileSize(string $path, string $disk = 'public'): int
    {
        return Storage::disk($disk)->exists($path)
            ? Storage::disk($disk)->size($path)
            : 0;
    }

    /**
     * Dosya boyutunu insan okunabilir formata çevir
     * Convert file size to human readable format
     */
    public function getFileSizeHuman(string $path, string $disk = 'public'): string
    {
        $bytes = $this->getFileSize($path, $disk);

        return $this->formatBytes($bytes);
    }

    /**
     * Byte'ı insan okunabilir formata çevir
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
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
     * Dosya uzantısını al
     * Get file extension
     */
    public function getFileExtension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Dosya adını al
     * Get filename
     */
    public function getFileName(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Dosya MIME tipini al
     * Get file MIME type
     */
    public function getFileMimeType(string $path, string $disk = 'public'): string
    {
        return Storage::disk($disk)->exists($path)
            ? Storage::disk($disk)->mimeType($path)
            : '';
    }

    /**
     * Dosya resim mi kontrol et
     * Check if file is an image
     */
    public function isImage(string $path, string $disk = 'public'): bool
    {
        $mimeType = $this->getFileMimeType($path, $disk);

        return str_starts_with($mimeType, 'image/');
    }

    /**
     * Dosya PDF mi kontrol et
     * Check if file is a PDF
     */
    public function isPdf(string $path, string $disk = 'public'): bool
    {
        $mimeType = $this->getFileMimeType($path, $disk);

        return $mimeType === 'application/pdf';
    }

    /**
     * Dosya doküman mı kontrol et
     * Check if file is a document
     */
    public function isDocument(string $path, string $disk = 'public'): bool
    {
        $documentMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        $mimeType = $this->getFileMimeType($path, $disk);

        return in_array($mimeType, $documentMimes);
    }

    /**
     * Dosyayı taşı
     * Move file
     */
    public function moveFile(string $from, string $to, string $disk = 'public'): bool
    {
        if (!Storage::disk($disk)->exists($from)) {
            return false;
        }

        return Storage::disk($disk)->move($from, $to);
    }

    /**
     * Dosyayı kopyala
     * Copy file
     */
    public function copyFile(string $from, string $to, string $disk = 'public'): bool
    {
        if (!Storage::disk($disk)->exists($from)) {
            return false;
        }

        return Storage::disk($disk)->copy($from, $to);
    }

    /**
     * Klasörü temizle
     * Clear directory
     */
    public function clearDirectory(string $path, string $disk = 'public'): bool
    {
        return Storage::disk($disk)->deleteDirectory($path);
    }

    /**
     * Klasördeki dosya sayısını al
     * Get file count in directory
     */
    public function getFileCount(string $path, string $disk = 'public'): int
    {
        return count(Storage::disk($disk)->files($path));
    }

    /**
     * Klasördeki tüm dosyaları al
     * Get all files in directory
     */
    public function getFiles(string $path, string $disk = 'public'): array
    {
        return Storage::disk($disk)->files($path);
    }

    /**
     * Dosya son değiştirilme zamanını al
     * Get file last modified time
     */
    public function getLastModified(string $path, string $disk = 'public'): int
    {
        return Storage::disk($disk)->exists($path)
            ? Storage::disk($disk)->lastModified($path)
            : 0;
    }

    /**
     * Base64'ten dosya oluştur
     * Create file from base64
     */
    public function createFileFromBase64(string $base64Data, string $path, string $filename, string $disk = 'public'): string
    {
        // Base64 verisini decode et
        // Decode base64 data
        $fileData = base64_decode($base64Data);

        // Dosya yolunu oluştur
        // Build file path
        $filePath = $path . '/' . $filename;

        // Dosyayı kaydet
        // Save file
        Storage::disk($disk)->put($filePath, $fileData);

        return $filePath;
    }

    /**
     * Dosyayı base64'e çevir
     * Convert file to base64
     */
    public function convertFileToBase64(string $path, string $disk = 'public'): string
    {
        if (!Storage::disk($disk)->exists($path)) {
            return '';
        }

        $fileContent = Storage::disk($disk)->get($path);
        return base64_encode($fileContent);
    }
}
