<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];
    protected $with = 'category';

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }
}
