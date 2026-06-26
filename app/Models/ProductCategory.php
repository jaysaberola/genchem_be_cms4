<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'user_id',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
