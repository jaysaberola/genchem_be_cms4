<?php

namespace App\Helpers;

/**
 * @deprecated Use ModelHelper::patchCmsHtml() — kept for backward compatibility.
 */
class GenchemCmsContentHelper
{
    public static function patchProductImageFilenames(string $html): string
    {
        return ModelHelper::patchCmsProductImageFilenames($html);
    }

    public static function patchGenchemAssetUrls(string $html): string
    {
        return ModelHelper::patchCmsGenchemAssetUrls($html);
    }

    public static function patchHtml(string $html): string
    {
        return ModelHelper::patchCmsHtml($html);
    }

    public static function resolveEditorAssetUrl(string $url, ?string $appUrl = null): string
    {
        $appUrl = rtrim($appUrl ?? (string) config('app.url', url('/')), '/');
        $url = ModelHelper::patchCmsProductImageFilenames($url);

        if ($url === '') {
            return $url;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            if (str_contains($url, '/images/genchemph/') && !str_starts_with($url, $appUrl)) {
                $path = parse_url($url, PHP_URL_PATH) ?: '';

                return $appUrl.$path;
            }

            return $url;
        }

        if (str_starts_with($url, '/images/genchemph/')) {
            return $appUrl.$url;
        }

        if (str_starts_with($url, 'images/genchemph/')) {
            return $appUrl.'/'.$url;
        }

        return $url;
    }
}
