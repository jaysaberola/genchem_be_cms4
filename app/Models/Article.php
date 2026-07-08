<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\ActivityLog;

class Article extends Model
{
    use SoftDeletes;

    protected $table = 'articles';
    protected $fillable = ['date', 'teaser', 'is_featured', 'slug', 'name', 'contents', 'status', 'image_url', 'thumbnail_url', 'meta_title', 'meta_keyword', 'meta_description', 'user_id', 'category_id', 'json', 'styles'];

    public static function base_front_url()
    {
        return env('APP_URL')."/news/";
    }

    public static function totalArticles()
    {
        $total = Article::withTrashed()->count();

        return $total;
    }

    public static function totalPublishedArticles()
    {
        $total = Article::where('status','Published')->count();

        return $total;
    }

    public static function totalDraftArticles()
    {
        $total = Article::where('status','Private')->count();

        return $total;
    }

    public static function totalDeletedArticles()
    {
        $withTrashed = Article::withTrashed()->get()->count();
        $total = $withTrashed - Article::count();

        return $total;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class)->withDefault([
            'name' => 'Uncategorized',
            'id' => '0',
        ]);
    }

    public function get_url()
    {

        return env('APP_URL')."/news/".$this->slug;
    }

    public function date_posted()
    {
        return Carbon::parse($this->date)->toFormattedDateString();
    }

    public function get_created_at_date_only()
    {
        return Carbon::parse($this->created_at)->toFormattedDateString();
    }

    public function get_image_url_storage_path()
    {
        $delimiter = 'storage/';
        $rawImageUrl = $this->attributes['image_url'] ?? '';
        if (strpos($rawImageUrl, $delimiter) !== false) {
            $paths = explode($delimiter, $rawImageUrl);
            return $paths[1];
        }

        return '';
    }

    public function get_thumbnail_url_storage_path()
    {
        $delimiter = 'storage/';
        $rawThumbnailUrl = $this->attributes['thumbnail_url'] ?? '';
        if (strpos($rawThumbnailUrl, $delimiter) !== false) {
            $paths = explode($delimiter, $rawThumbnailUrl);
            return $paths[1];
        }

        return '';
    }

    public function get_image_file_name()
    {
        $rawImageUrl = (string) ($this->attributes['image_url'] ?? '');
        $path = explode('/', $rawImageUrl);
        $nameIndex = count($path) - 1;
        if ($nameIndex < 0)
            return '';

        return $path[$nameIndex];
    }

    public function getImageUrlAttribute($value)
    {
        return $this->normalizeMediaUrl($value);
    }

    public function getThumbnailUrlAttribute($value)
    {
        return $this->normalizeMediaUrl($value);
    }

    private function normalizeMediaUrl($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return $value;
        }

        if (preg_match('#^data:#i', $value)) {
            return $value;
        }

        if (preg_match('#^https?://#i', $value)) {
            $parsedPath = (string) parse_url($value, PHP_URL_PATH);
            if ($parsedPath !== '') {
                $value = $parsedPath;
            }
        }

        $value = str_replace('\\', '/', $value);
        $storagePos = stripos($value, '/storage/');
        if ($storagePos !== false) {
            $value = substr($value, $storagePos);
        } elseif (stripos($value, 'storage/') === 0) {
            $value = '/'.$value;
        } elseif (stripos($value, '/news_image/') !== false || stripos($value, 'news_image/') === 0) {
            $newsPos = stripos($value, '/news_image/');
            $value = $newsPos !== false ? substr($value, $newsPos) : '/'.ltrim($value, '/');
        }

        if (!str_starts_with($value, '/')) {
            $value = '/'.$value;
        }

        $appBasePath = parse_url((string) config('app.url', ''), PHP_URL_PATH) ?: '';
        $appBasePath = rtrim($appBasePath, '/');
        if ($appBasePath !== '' && str_starts_with($value, $appBasePath.'/')) {
            $value = substr($value, strlen($appBasePath));
            if (!str_starts_with($value, '/')) {
                $value = '/'.$value;
            }
        }

        $baseUrl = '';
        try {
            if (app()->bound('request')) {
                $request = request();
                $baseUrl = rtrim($request->getSchemeAndHttpHost().$request->getBaseUrl(), '/');
            }
        } catch (\Throwable $e) {
            $baseUrl = '';
        }

        if ($baseUrl !== '') {
            return $baseUrl.$value;
        }

        return url($value);
    }

    public function featured_news_limit()
    {
        return env('FEATURED_NEWS_LIMIT', 0);
    }

    public static function has_featured_limit()
    {
        return env('FEATURED_NEWS_LIMIT', 0);
    }

    public static function can_set_featured()
    {
        $limit = env('FEATURED_NEWS_LIMIT', 0);

        if ($limit == 0) {
            return true;
        }

        $featuredCount = Article::where('is_featured', 1)->get()->count();

        if ($featuredCount >= $limit) {
            return false;
        }

        return true;
    }

    public static function cannot_create_featured_news()
    {
        $limit = env('FEATURED_NEWS_LIMIT', 0);

        if ($limit == 0) {
            return false;
        }

        $featuredCount = Article::where('is_featured', 1)->get()->count();

        if ($featuredCount >= $limit) {
            return true;
        }

        return false;
    }














    // ******** AUDIT LOG ******** //
    // Need to change every model
    static $oldModel;
    static $tableTitle = 'article';
    static $name = 'name';
    static $unrelatedFields = ['id', 'slug', 'created_at', 'updated_at', 'deleted_at', 'json', 'styles'];
    static $logName = [
        'category_id' => 'category',
        'date' => 'date',
        'name' => 'name',
        'contents' => 'contents',
        'teaser' => 'teaser',
        'status' => 'status',
        'is_featured' => 'featured',
        'image_url' => 'banner',
        'thumbnail_url' => 'thumbnail',
        'meta_title' => 'meta title',
        'meta_keyword' => 'meta keyword',
        'meta_description' => 'meta description'
    ];
    // END Need to change every model

    public static function boot()
    {
        parent::boot();

        self::created(function($model) {
            $name = $model[self::$name];

            ActivityLog::create([
                'log_by' => auth()->id(),
                'activity_type' => 'insert',
                'dashboard_activity' => 'created a new '. self::$tableTitle,
                'activity_desc' => 'created the '. self::$tableTitle .' '. $name,
                'activity_date' => date("Y-m-d H:i:s"),
                'db_table' => $model->getTable(),
                'old_value' => '',
                'new_value' => $name,
                'reference' => $model->id
            ]);
        });

        self::updating(function($model) {
            self::$oldModel = $model->fresh();
        });

        self::updated(function($model) {
            $name = $model[self::$name];
            $oldModel = self::$oldModel->toArray();
            foreach ($oldModel as $fieldName => $value) {
                if (in_array($fieldName, self::$unrelatedFields)) {
                    continue;
                }

                $oldValue = $model[$fieldName];
                if ($oldValue != $value) {
                    ActivityLog::create([
                        'log_by' => auth()->id(),
                        'activity_type' => 'update',
                        'dashboard_activity' => 'updated the '. self::$tableTitle .' '. self::$logName[$fieldName],
                        'activity_desc' => 'updated the '. self::$tableTitle .' '. self::$logName[$fieldName] .' of '. $name .' from '. $oldValue .' to '. $value,
                        'activity_date' => date("Y-m-d H:i:s"),
                        'db_table' => $model->getTable(),
                        'old_value' => $oldValue,
                        'new_value' => $value,
                        'reference' => $model->id
                    ]);
                }
            }
        });

        self::deleted(function($model){
            $name = $model[self::$name];
            ActivityLog::create([
                'log_by' => auth()->id(),
                'activity_type' => 'delete',
                'dashboard_activity' => 'deleted a '. self::$tableTitle,
                'activity_desc' => 'deleted the '. self::$tableTitle .' '. $name,
                'activity_date' => date("Y-m-d H:i:s"),
                'db_table' => $model->getTable(),
                'old_value' => '',
                'new_value' => '',
                'reference' => $model->id
            ]);
        });

        // self::restored(function($model){
        //     $name = $model[self::$name];
        //     ActivityLog::create([
        //         'log_by' => auth()->id(),
        //         'activity_type' => 'restore',
        //         'dashboard_activity' => 'restore a '. self::$tableTitle,
        //         'activity_desc' => 'restore the '. self::$tableTitle .' '. $name,
        //         'activity_date' => date("Y-m-d H:i:s"),
        //         'db_table' => $model->getTable(),
        //         'old_value' => '',
        //         'new_value' => '',
        //         'reference' => $model->id
        //     ]);
        // });
    }
}
