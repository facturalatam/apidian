<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class StorageService
{
    // Per-request company storage mode ('local', 's3', 'dual', or null = use global config)
    protected static $currentCompanyStorageMode = null;

    /**
     * Set company context for subsequent storage operations.
     * Pass null to clear and fall back to global config.
     */
    public static function setCompany($company): void
    {
        // Community Edition: el almacenamiento por empresa (S3/dual) es una
        // funcionalidad Enterprise. Aqui el almacenamiento es siempre local.
        static::$currentCompanyStorageMode = null;
    }

    public static function clearCompany(): void
    {
        static::$currentCompanyStorageMode = null;
    }

    /**
     * Resolve the effective storage mode.
     * Returns 'local', 's3', or 'dual'.
     */
    protected static function resolveMode(): string
    {
        // Community Edition: almacenamiento siempre local (S3/dual es Enterprise).
        return 'local';
    }

    /**
     * Get the configured storage disk instance.
     */
    public static function disk()
    {
        $mode = static::resolveMode();
        if ($mode === 's3') {
            return Storage::disk('s3');
        }
        return Storage::disk('local');
    }

    /**
     * Check if the current storage is S3 only (not dual).
     */
    public static function isS3(): bool
    {
        return static::resolveMode() === 's3';
    }

    /**
     * Check if dual storage mode is active.
     */
    public static function isDual(): bool
    {
        return static::resolveMode() === 'dual';
    }

    /**
     * Put content to the configured storage disk.
     * In dual mode, writes to both local and S3.
     */
    public static function put(string $path, $content): bool
    {
        $mode = static::resolveMode();

        if ($mode === 'dual') {
            $result = Storage::disk('local')->put($path, $content);
            try {
                Storage::disk('s3')->put($path, $content);
            } catch (\Exception $e) {
                // S3 write failed; local copy is preserved
            }
            return $result;
        }

        if ($mode === 's3') {
            return Storage::disk('s3')->put($path, $content);
        }

        return Storage::disk('local')->put($path, $content);
    }

    /**
     * Get file content from the configured storage disk.
     * In dual mode, tries local first, then S3.
     */
    public static function get(string $path): ?string
    {
        $mode = static::resolveMode();

        if ($mode === 'dual') {
            $localPath = storage_path('app/' . $path);
            if (file_exists($localPath)) {
                return file_get_contents($localPath);
            }
            try {
                return Storage::disk('s3')->get($path);
            } catch (\Exception $e) {
                return null;
            }
        }

        return static::disk()->get($path);
    }

    /**
     * Check if a file exists on the configured storage disk.
     * In dual mode, checks local first, then S3.
     */
    public static function exists(string $path): bool
    {
        $mode = static::resolveMode();

        if ($mode === 'dual') {
            if (file_exists(storage_path('app/' . $path))) {
                return true;
            }
            try {
                return Storage::disk('s3')->exists($path);
            } catch (\Exception $e) {
                return false;
            }
        }

        return static::disk()->exists($path);
    }

    /**
     * Delete a file from the configured storage disk.
     * In dual mode, deletes from both local and S3.
     */
    public static function delete(string $path): bool
    {
        $mode = static::resolveMode();

        if ($mode === 'dual') {
            $localPath = storage_path('app/' . $path);
            if (file_exists($localPath)) {
                @unlink($localPath);
            }
            try {
                Storage::disk('s3')->delete($path);
            } catch (\Exception $e) {}
            return true;
        }

        return static::disk()->delete($path);
    }

    /**
     * Create a directory on the configured storage disk (no-op for S3).
     */
    public static function makeDirectory(string $path): bool
    {
        $mode = static::resolveMode();
        if ($mode === 's3') {
            return true;
        }
        // local and dual: create local directory
        return Storage::disk('local')->makeDirectory($path);
    }

    /**
     * Check if a directory/path exists on the configured storage disk.
     */
    public static function has(string $path): bool
    {
        $mode = static::resolveMode();
        if ($mode === 's3' || $mode === 'dual') {
            return true;
        }
        return Storage::has($path);
    }

    /**
     * Upload a locally generated file (PDF, ZIP, etc.) to the configured storage disk.
     * In dual mode, copies to permanent local storage AND uploads to S3.
     */
    public static function putLocalFile(string $storagePath, string $localFilePath, bool $deleteLocal = true): bool
    {
        if (!file_exists($localFilePath)) {
            return false;
        }

        $mode = static::resolveMode();

        if ($mode === 'dual') {
            $targetPath = storage_path('app/' . $storagePath);

            // Copy to permanent local storage (skip if already there)
            if (realpath($localFilePath) !== realpath($targetPath)) {
                $dir = dirname($targetPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                copy($localFilePath, $targetPath);
            }

            // Upload to S3
            try {
                $content = file_get_contents($targetPath);
                Storage::disk('s3')->put($storagePath, $content);
            } catch (\Exception $e) {
                // S3 upload failed; local copy is preserved
            }

            if ($deleteLocal && realpath($localFilePath) !== realpath($targetPath)) {
                @unlink($localFilePath);
            }

            return true;
        }

        if ($mode === 's3') {
            $content = file_get_contents($localFilePath);
            $result = Storage::disk('s3')->put($storagePath, $content);
            if ($deleteLocal) {
                @unlink($localFilePath);
            }
            return $result;
        }

        // local mode
        $targetPath = storage_path('app/' . $storagePath);
        if (realpath($localFilePath) !== realpath($targetPath)) {
            $dir = dirname($targetPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            copy($localFilePath, $targetPath);
            if ($deleteLocal) {
                @unlink($localFilePath);
            }
        }

        return true;
    }

    /**
     * Get a local temp path for generating files (PDFs, ZIPs).
     * In S3 mode: uses sys_get_temp_dir (files are not in local storage).
     * In local and dual mode: returns storage_path directly (files live there permanently).
     */
    public static function tempPath(string $relativePath): string
    {
        if (static::isS3()) {
            $tempDir = sys_get_temp_dir() . '/apidian';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $subDir = dirname($relativePath);
            if ($subDir && $subDir !== '.') {
                $fullDir = $tempDir . '/' . $subDir;
                if (!is_dir($fullDir)) {
                    mkdir($fullDir, 0755, true);
                }
            }
            return $tempDir . '/' . $relativePath;
        }

        // local and dual: use permanent storage_path
        return storage_path('app/' . $relativePath);
    }

    /**
     * Get the real local storage path (for backwards compatibility during migration).
     */
    public static function localPath(string $storagePath): string
    {
        $localFile = storage_path('app/' . $storagePath);
        if (file_exists($localFile)) {
            return $localFile;
        }

        if (static::isS3() || static::isDual()) {
            $tempFile = static::tempPath($storagePath);
            try {
                if (Storage::disk('s3')->exists($storagePath)) {
                    $dir = dirname($tempFile);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    file_put_contents($tempFile, Storage::disk('s3')->get($storagePath));
                    return $tempFile;
                }
            } catch (\Exception $e) {}
            return $tempFile;
        }

        return $localFile;
    }

    /**
     * Download response from the configured storage disk.
     */
    public static function download(string $path, string $name = null)
    {
        $mode = static::resolveMode();

        if ($mode === 's3' || $mode === 'dual') {
            $content = static::get($path);
            if ($content === null) {
                abort(404, 'File not found');
            }
            $filename = $name ?? basename($path);
            $mime = static::guessMimeType($filename);
            return response($content, 200, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        return static::disk()->download($path, $name);
    }

    /**
     * Inline response (view in browser) from the configured storage disk.
     */
    public static function inline(string $path, string $name = null)
    {
        $content = static::get($path);
        if ($content === null) {
            abort(404, 'File not found');
        }
        $filename = $name ?? basename($path);
        $mime = static::guessMimeType($filename);
        return response($content, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Get file content as base64 encoded string.
     */
    public static function getBase64(string $path): ?string
    {
        $content = static::get($path);
        if ($content === null) {
            return null;
        }
        return base64_encode($content);
    }

    /**
     * Guess MIME type based on file extension.
     */
    protected static function guessMimeType(string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimes = [
            'pdf' => 'application/pdf',
            'xml' => 'application/xml',
            'zip' => 'application/zip',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ];

        return $mimes[$ext] ?? 'application/octet-stream';
    }

    /**
     * Ensure a directory exists for the given relative path.
     */
    public static function ensureDirectory(string $relativePath): void
    {
        $mode = static::resolveMode();
        if ($mode === 's3') {
            $tempDir = static::tempPath($relativePath);
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
        } else {
            // local and dual
            $dir = storage_path('app/' . $relativePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Get base64 encoded content, trying local temp first, then configured disk.
     */
    public static function getBase64Auto(string $relativePath): ?string
    {
        if (static::isS3()) {
            $tempFile = static::tempPath($relativePath);
            if (file_exists($tempFile)) {
                return base64_encode(file_get_contents($tempFile));
            }
        }
        return static::getBase64($relativePath);
    }

    /**
     * Upload a locally generated file to S3 if in S3 or dual mode.
     */
    public static function uploadIfS3(string $relativePath, ?string $localPath = null): void
    {
        $mode = static::resolveMode();
        if ($mode !== 's3' && $mode !== 'dual') {
            return;
        }
        $localPath = $localPath ?? static::tempPath($relativePath);
        if (file_exists($localPath)) {
            static::putLocalFile($relativePath, $localPath, false);
        }
    }

    /**
     * Upload multiple local files to S3/dual after DIAN processing.
     */
    public static function uploadBatchIfS3(array $relativePaths): void
    {
        $mode = static::resolveMode();
        if ($mode !== 's3' && $mode !== 'dual') {
            return;
        }
        foreach ($relativePaths as $relativePath) {
            static::uploadIfS3($relativePath);
        }
    }

    /**
     * Check if a file exists in the local temp storage.
     */
    public static function existsLocal(string $relativePath): bool
    {
        return file_exists(static::tempPath($relativePath));
    }

    /**
     * Get file contents from local temp or configured storage.
     */
    public static function getAutoLocal(string $relativePath): ?string
    {
        $localPath = static::tempPath($relativePath);
        if (file_exists($localPath)) {
            return file_get_contents($localPath);
        }
        $mode = static::resolveMode();
        if (($mode === 's3' || $mode === 'dual') && static::exists($relativePath)) {
            return static::get($relativePath);
        }
        return null;
    }

    // =========================================================================
    // MÉTODOS "AUTO" — buscan primero en local storage, luego en S3
    // =========================================================================

    public static function existsAuto(string $relativePath): bool
    {
        $localPath = storage_path('app/' . $relativePath);
        if (file_exists($localPath)) {
            return true;
        }
        $mode = static::resolveMode();
        if ($mode === 's3' || $mode === 'dual') {
            try {
                return Storage::disk('s3')->exists($relativePath);
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }

    public static function getAuto(string $relativePath): ?string
    {
        $localPath = storage_path('app/' . $relativePath);
        if (file_exists($localPath)) {
            return file_get_contents($localPath);
        }
        $mode = static::resolveMode();
        if ($mode === 's3' || $mode === 'dual') {
            $tempPath = static::tempPath($relativePath);
            if (file_exists($tempPath)) {
                return file_get_contents($tempPath);
            }
            try {
                if (Storage::disk('s3')->exists($relativePath)) {
                    return Storage::disk('s3')->get($relativePath);
                }
            } catch (\Exception $e) {}
        }
        return null;
    }

    public static function getBase64AutoFallback(string $relativePath): ?string
    {
        $content = static::getAuto($relativePath);
        if ($content === null) {
            return null;
        }
        return base64_encode($content);
    }

    public static function downloadAuto(string $relativePath, string $name = null)
    {
        $content = static::getAuto($relativePath);
        if ($content === null) {
            abort(404, 'File not found');
        }
        $filename = $name ?? basename($relativePath);
        $mime = static::guessMimeType($filename);
        return response($content, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public static function inlineAuto(string $relativePath, string $name = null)
    {
        $content = static::getAuto($relativePath);
        if ($content === null) {
            abort(404, 'File not found');
        }
        $filename = $name ?? basename($relativePath);
        $mime = static::guessMimeType($filename);
        return response($content, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    // =========================================================================
    // MÉTODOS DE ALMACENAMIENTO LOCAL FORZADO
    // Para archivos que SIEMPRE deben estar localmente (certificados, logos)
    // =========================================================================

    public static function putLocal(string $path, $content): bool
    {
        $fullPath = storage_path('app/' . $path);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return file_put_contents($fullPath, $content) !== false;
    }

    public static function getLocal(string $path): ?string
    {
        $fullPath = storage_path('app/' . $path);
        if (!file_exists($fullPath)) {
            return null;
        }
        return file_get_contents($fullPath);
    }

    public static function existsLocalStorage(string $path): bool
    {
        return file_exists(storage_path('app/' . $path));
    }

    public static function deleteLocal(string $path): bool
    {
        $fullPath = storage_path('app/' . $path);
        if (file_exists($fullPath)) {
            return @unlink($fullPath);
        }
        return true;
    }

    public static function localStoragePath(string $path): string
    {
        return storage_path('app/' . $path);
    }
}
