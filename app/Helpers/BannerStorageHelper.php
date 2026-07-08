<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BannerStorageHelper
{
    /** Web-accessible folder (same pattern as /images/genchemph/banners/...). */
    public const PUBLIC_BANNERS_DIR = 'images/banners';

    public const PUBLIC_TEMP_DIR = 'images/temporary_banners';

    public static function temporaryFolderFor(int $userId): string
    {
        return self::PUBLIC_TEMP_DIR.'/'.$userId;
    }

    public static function legacyTemporaryFolderFor(int $userId): string
    {
        return 'temporary_banners/'.$userId;
    }

    public static function legacyStorageTemporaryFolderFor(int $userId): string
    {
        return 'temporary_banners'.$userId;
    }

    public static function sanitizeFileName(string $fileName): string
    {
        $fileName = basename(str_replace('\\', '/', $fileName));
        $parts = explode('.', $fileName);
        $extension = count($parts) > 1 ? strtolower((string) array_pop($parts)) : 'jpg';
        $base = implode('.', $parts);
        $base = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $base) ?? 'banner';
        $base = trim((string) $base, '_');

        if ($base === '') {
            $base = 'banner';
        }

        $extension = preg_replace('/[^a-z0-9]+/', '', $extension) ?: 'jpg';

        return $base.'.'.$extension;
    }

    public static function normalizePublicUrl(?string $url): string
    {
        if (!$url) {
            return '';
        }

        $normalized = str_replace('\\', '/', $url);
        $normalized = preg_replace('#/storage/banners//+#', '/images/banners/', $normalized) ?? $normalized;
        $normalized = preg_replace('#/storage/banners/#', '/images/banners/', $normalized) ?? $normalized;
        $normalized = preg_replace('#/storage/temporary_banners/#', '/'.self::PUBLIC_TEMP_DIR.'/', $normalized) ?? $normalized;
        $normalized = preg_replace('#/storage/temporary_banners(\d+)/#', '/'.self::PUBLIC_TEMP_DIR.'/$1/', $normalized) ?? $normalized;
        $normalized = preg_replace('#(/images/banners)/+#', '$1/', $normalized) ?? $normalized;
        $normalized = preg_replace('#(/images/temporary_banners)/+#', '$1/', $normalized) ?? $normalized;

        return self::encodePublicUrl($normalized);
    }

    public static function encodePublicUrl(string $url): string
    {
        if (!preg_match('#/(images/|storage/)#', $url)) {
            return $url;
        }

        if (str_contains($url, 'images/')) {
            [$prefix, $path] = explode('images/', $url, 2);
            $segments = array_map(
                static fn (string $segment) => rawurlencode(rawurldecode($segment)),
                explode('/', $path),
            );

            return $prefix.'images/'.implode('/', $segments);
        }

        [$prefix, $path] = explode('storage/', $url, 2);
        $segments = array_map(
            static fn (string $segment) => rawurlencode(rawurldecode($segment)),
            explode('/', $path),
        );

        return $prefix.'storage/'.implode('/', $segments);
    }

    public static function pathInStorage(?string $path): string
    {
        if (!$path) {
            return '';
        }

        $path = str_replace('\\', '/', rawurldecode($path));

        if (str_contains($path, 'images/')) {
            return ltrim(explode('images/', $path, 2)[1], '/');
        }

        if (str_contains($path, 'storage/')) {
            $storagePath = ltrim(explode('storage/', $path, 2)[1], '/');
            if (str_starts_with($storagePath, 'banners/')) {
                return 'banners/'.basename($storagePath);
            }

            return $storagePath;
        }

        return ltrim($path, '/');
    }

    public static function relativePublicPath(string $path): string
    {
        $storagePath = self::pathInStorage($path);

        if (str_starts_with($storagePath, 'banners/')) {
            return self::PUBLIC_BANNERS_DIR.'/'.basename($storagePath);
        }

        if (preg_match('#^temporary_banners/?(\d+)/(.*)$#', $storagePath, $matches)) {
            return self::PUBLIC_TEMP_DIR.'/'.$matches[1].'/'.basename($matches[2]);
        }

        if (str_starts_with($storagePath, 'temporary_banners')) {
            return self::PUBLIC_TEMP_DIR.'/'.ltrim(substr($storagePath, strlen('temporary_banners')), '/');
        }

        if (str_starts_with($storagePath, self::PUBLIC_BANNERS_DIR.'/')) {
            return $storagePath;
        }

        if (str_starts_with($storagePath, self::PUBLIC_TEMP_DIR.'/')) {
            return $storagePath;
        }

        return self::PUBLIC_BANNERS_DIR.'/'.basename($storagePath);
    }

    public static function fileNameFromPath(?string $path): string
    {
        return self::sanitizeFileName(basename(self::pathInStorage($path)));
    }

    public static function isTemporaryPath(?string $path, int $userId): bool
    {
        $relative = self::relativePublicPath((string) $path);
        $prefixes = [
            self::temporaryFolderFor($userId).'/',
            self::PUBLIC_TEMP_DIR.'/'.$userId.'/',
            self::legacyTemporaryFolderFor($userId).'/',
            self::legacyStorageTemporaryFolderFor($userId).'/',
        ];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($relative, $prefix) || str_starts_with(self::pathInStorage($path), $prefix)) {
                return true;
            }
        }

        return false;
    }

    public static function publicUrlForRelativePath(string $relativePath): string
    {
        $relativePath = str_replace('\\', '/', ltrim($relativePath, '/'));

        return self::normalizePublicUrl(asset($relativePath));
    }

    public static function absolutePath(string $relativePath): string
    {
        return public_path(str_replace('\\', '/', ltrim($relativePath, '/')));
    }

    public static function ensureDirectory(string $relativePath): void
    {
        $absolute = self::absolutePath($relativePath);
        if (!is_dir($absolute)) {
            File::makeDirectory($absolute, 0755, true);
        }
    }

    public static function existsAtPublicPath(string $relativePath): bool
    {
        return is_file(self::absolutePath($relativePath));
    }

    public static function resolveExistingPublicPath(string $path, ?int $userId = null): string
    {
        $relative = self::relativePublicPath($path);

        if ($userId === null) {
            if (preg_match('#temporary_banners/?(\d+)/#', self::pathInStorage($path), $matches)) {
                $userId = (int) $matches[1];
            } else {
                $userId = (int) auth()->id();
            }
        }

        $fileName = self::fileNameFromPath($path);
        $candidates = array_values(array_unique(array_filter([
            $relative,
            self::PUBLIC_BANNERS_DIR.'/'.$fileName,
            self::temporaryFolderFor($userId).'/'.$fileName,
            self::legacyTemporaryFolderFor($userId).'/'.$fileName,
        ])));

        foreach ($candidates as $candidate) {
            if (self::existsAtPublicPath($candidate)) {
                return $candidate;
            }
        }

        self::migrateLegacyStorageFile($path, self::PUBLIC_BANNERS_DIR.'/'.$fileName);

        if (self::existsAtPublicPath(self::PUBLIC_BANNERS_DIR.'/'.$fileName)) {
            return self::PUBLIC_BANNERS_DIR.'/'.$fileName;
        }

        return $candidates[0] ?? self::PUBLIC_BANNERS_DIR.'/'.$fileName;
    }

    public static function migrateLegacyStorageFile(string $path, string $targetRelative): void
    {
        if (self::existsAtPublicPath($targetRelative)) {
            return;
        }

        $storagePath = self::pathInStorage($path);
        $legacyCandidates = array_values(array_unique(array_filter([
            $storagePath,
            preg_replace('#^banners//+#', 'banners/', $storagePath) ?? $storagePath,
            'banners/'.basename($storagePath),
        ])));

        foreach ($legacyCandidates as $legacyPath) {
            if (!Storage::disk('public')->exists($legacyPath)) {
                continue;
            }

            self::ensureDirectory(dirname($targetRelative));
            File::copy(
                Storage::disk('public')->path($legacyPath),
                self::absolutePath($targetRelative),
            );

            return;
        }
    }

    public static function makeUniqueFileName(string $folder, string $fileName): string
    {
        $fileName = self::sanitizeFileName($fileName);
        $parts = explode('.', $fileName);
        $extension = count($parts) > 1 ? '.'.array_pop($parts) : '.jpg';
        $base = implode('.', $parts);
        $count = 2;
        $newFilename = $base.'_'.$count.$extension;

        while (self::existsAtPublicPath($folder.'/'.$newFilename)) {
            $count += 1;
            $newFilename = $base.'_'.$count.$extension;
        }

        return $newFilename;
    }

    public static function uploadToTemporary(UploadedFile $file, int $userId): array
    {
        $temporaryFolder = self::temporaryFolderFor($userId);
        self::ensureDirectory($temporaryFolder);

        $fileName = self::sanitizeFileName($file->getClientOriginalName());

        if (self::existsAtPublicPath($temporaryFolder.'/'.$fileName)) {
            $fileName = self::makeUniqueFileName($temporaryFolder, $fileName);
        }

        $file->move(self::absolutePath($temporaryFolder), $fileName);
        $relativePath = $temporaryFolder.'/'.$fileName;

        return [
            'path' => $relativePath,
            'name' => $fileName,
            'url' => self::publicUrlForRelativePath($relativePath),
        ];
    }

    public static function moveToBannersFolder(string $sourcePath, string $fileName): string
    {
        $folder = self::PUBLIC_BANNERS_DIR;
        $fileName = self::sanitizeFileName(ltrim($fileName, '/'));
        self::ensureDirectory($folder);

        $sourceRelative = self::resolveExistingPublicPath($sourcePath);
        $destination = $folder.'/'.$fileName;

        if (self::existsAtPublicPath($destination)) {
            $fileName = self::makeUniqueFileName($folder, $fileName);
            $destination = $folder.'/'.$fileName;
        }

        if (self::existsAtPublicPath($sourceRelative)) {
            File::move(self::absolutePath($sourceRelative), self::absolutePath($destination));
        } elseif (Storage::disk('public')->exists(self::pathInStorage($sourcePath))) {
            self::migrateLegacyStorageFile($sourcePath, $destination);
        }

        return self::publicUrlForRelativePath($destination);
    }

    public static function moveBannersToOfficialFolder(array $banners, int $userId): array
    {
        foreach ($banners as $key => $banner) {
            $imagePath = $banners[$key]['image_path'] ?? '';

            if (!self::isTemporaryPath($imagePath, $userId)) {
                $resolved = self::resolveExistingPublicPath($imagePath, $userId);
                $banners[$key]['image_path'] = self::publicUrlForRelativePath($resolved);
                continue;
            }

            $fileName = self::fileNameFromPath($imagePath);
            $banners[$key]['image_path'] = self::moveToBannersFolder($imagePath, $fileName);
        }

        return $banners;
    }

    public static function deleteTemporaryFolder(int $userId): void
    {
        foreach ([
            self::temporaryFolderFor($userId),
            self::legacyTemporaryFolderFor($userId),
        ] as $temporaryFolder) {
            $absolute = self::absolutePath($temporaryFolder);
            if (is_dir($absolute)) {
                File::deleteDirectory($absolute);
            }
        }

        $legacyStorageFolder = self::legacyStorageTemporaryFolderFor($userId);
        if (Storage::disk('public')->exists($legacyStorageFolder)) {
            Storage::disk('public')->deleteDirectory($legacyStorageFolder);
        }
    }
}
