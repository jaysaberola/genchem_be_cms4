<?php


namespace App\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class ModelHelper
{
    public static function convert_to_slug($model, $url, $parentPage = 0){
        $url = str_slug($url, '-');

        $parentPage = $model::find($parentPage);
        if($parentPage) {
            $url = $parentPage->slug.'/'.$url;
        }


        if (self::check_if_slug_exists($model, $url)) {
            $counter = 2;
            $tempUrl = $url.'-'.$counter;
            while (self::check_if_slug_exists($model, $tempUrl)) {
                $tempUrl = $url.'-'.$counter;
                $counter += 1;
            }

            $url = $tempUrl;
        }

        return $url;
    }

    private static function check_if_slug_exists($model, $slug){
        return ($model::withTrashed()->where('slug', '=', $slug)->exists());
    }

    public static function date_str($date) {
        return date('M d, Y', strtotime($date));
    }

    public static function date_time_str($date) {
        return date('M d, Y h:i A', strtotime($date));
    }

    /** Common OCR / legacy typos in CMS image paths. */
    private const CMS_IMAGE_ALIASES = [
        '/images/genchem-res-trip-product2.png' => '/images/genchemph/products/trio_product2.png',
        '/images/genchem-res_trip-product2.png' => '/images/genchemph/products/trio_product2.png',
        '/images/genchem-res-trip-product.png' => '/images/genchemph/products/trio_product.png',
        '/images/genchem-res_trip-product.png' => '/images/genchemph/products/trio_product.png',
        '/images/genchemph/products/trip_product2.png' => '/images/genchemph/products/trio_product2.png',
        '/images/genchemph/products/trip_product.png' => '/images/genchemph/products/trio_product.png',
        'genchem-res-trip-product2.png' => 'products/trio_product2.png',
        'genchem-res_trip-product2.png' => 'products/trio_product2.png',
        'trip_product2.png' => 'trio_product2.png',
        'trip_product.png' => 'trio_product.png',
        '/products/AL51.png' => '/images/genchemph/products/ALST.png',
        '/products/B45T.png' => '/images/genchemph/products/BAST.png',
        '/products/C45T.png' => '/images/genchemph/products/CAST.png',
        '/products/COST.png' => '/images/genchemph/products/CDST.png',
        'products/AL51.png' => 'products/ALST.png',
        'products/B45T.png' => 'products/BAST.png',
        'products/C45T.png' => 'products/CAST.png',
        'products/COST.png' => 'products/CDST.png',
        'AL51.png' => 'ALST.png',
        'B45T.png' => 'BAST.png',
        'C45T.png' => 'CAST.png',
        'COST.png' => 'CDST.png',
        'AIST.png' => 'ALST.png',
    ];

    private const CMS_BANNER_FILES = [
        'HOMEPAGE_ABOUT_US.png',
        'home_header.png',
        'about_us.png',
        'our_products.png',
        'contact_us.png',
    ];

    private const CMS_ICON_FILES = [
        'call.png',
        'call_red.png',
        'mobile.png',
        'email.png',
        'globe.png',
    ];

    public static function patchCmsProductImageFilenames(string $html): string
    {
        if ($html === '') {
            return $html;
        }

        $output = $html;
        foreach (self::CMS_IMAGE_ALIASES as $wrong => $right) {
            $output = str_replace($wrong, $right, $output);
            $output = str_replace('/images/'.$wrong, '/images/genchemph/'.$right, $output);
        }

        return $output;
    }

    public static function resolveCmsBareImagePath(string $filename): string
    {
        if (in_array($filename, self::CMS_BANNER_FILES, true)) {
            return '/images/genchemph/banners/'.$filename;
        }

        if (in_array($filename, self::CMS_ICON_FILES, true)) {
            return '/images/genchemph/icons/'.$filename;
        }

        if (stripos($filename, 'logo') !== false || str_ends_with(strtolower($filename), '.ico')) {
            return '/images/genchemph/logos/'.$filename;
        }

        if ($filename === 'video.mp4' || $filename === 'genchem_video.mp4') {
            return '/images/genchemph/genchem_video.mp4';
        }

        return '/images/genchemph/products/'.$filename;
    }

    public static function patchCmsGenchemAssetUrls(string $html): string
    {
        if ($html === '') {
            return $html;
        }

        $output = self::patchCmsProductImageFilenames($html);
        $appUrl = rtrim((string) config('app.url', url('/')), '/');

        $output = preg_replace(
            '#https?://[^/]+/images/genchemph/#i',
            '/images/genchemph/',
            $output
        ) ?? $output;

        $output = preg_replace(
            '#(?<!genchemph)/images/(?!genchemph/)([^"\'\\)\\s]+)#i',
            '/images/genchemph/$1',
            $output
        ) ?? $output;

        $output = preg_replace(
            '#src=(["\'])/images/products/#i',
            'src=$1/images/genchemph/products/',
            $output
        ) ?? $output;

        $output = preg_replace(
            "#src=(['\"])/images/products/#i",
            "src=$1/images/genchemph/products/",
            $output
        ) ?? $output;

        $output = preg_replace(
            '#src=(["\'])images/(?!genchemph/)#i',
            'src=$1/images/genchemph/',
            $output
        ) ?? $output;

        $output = preg_replace_callback(
            '#(?:src|poster)=(["\'])(?!https?://|/|data:)([^"\']+\.(?:png|jpe?g|gif|webp|svg|ico))\\1#i',
            static function (array $matches): string {
                $attribute = str_starts_with($matches[0], 'poster') ? 'poster' : 'src';
                $quote = $matches[1];
                $path = self::resolveCmsBareImagePath($matches[2]);

                return $attribute.'='.$quote.$path.$quote;
            },
            $output
        ) ?? $output;

        $output = preg_replace_callback(
            "#url\\((['\"]?)(?!https?://|/|data:)([^'\"\\)]+\\.(?:png|jpe?g|gif|webp|svg|ico))(['\"]?)\\)#i",
            static function (array $matches): string {
                $quote = $matches[1] !== '' ? $matches[1] : $matches[3];
                $path = self::resolveCmsBareImagePath($matches[2]);

                return 'url('.$quote.$path.$quote.')';
            },
            $output
        ) ?? $output;

        $output = preg_replace_callback(
            '#"(src|poster)"\s*:\s*"((?:\\\\.|[^"\\\\])*)"#i',
            static function (array $matches): string {
                $url = stripcslashes($matches[2]);
                if ($url === '' || str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/') || str_starts_with($url, 'data:')) {
                    return $matches[0];
                }

                return '"'.$matches[1].'":"'.self::resolveCmsBareImagePath($url).'"';
            },
            $output
        ) ?? $output;

        if ($appUrl !== '') {
            $escaped = preg_quote($appUrl, '#');
            $output = preg_replace(
                '#'.$escaped.'/images/genchemph/#i',
                '/images/genchemph/',
                $output
            ) ?? $output;
        }

        $output = preg_replace(
            '#(?<!genchem_)/images/genchemph/video\.mp4#i',
            '/images/genchemph/genchem_video.mp4',
            $output
        ) ?? $output;

        return $output;
    }

    public static function patchCmsHtml(string $html): string
    {
        return self::patchCmsGenchemAssetUrls($html);
    }
}
