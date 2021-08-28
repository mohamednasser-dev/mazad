<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Forum extends Model
{
    protected $fillable = ['image','title_ar','title_en','deleted','desc_ar','desc_en','cat_id','city_id','status'];

    public function City() {
        return $this->belongsTo('App\City', 'city_id');
    }
    public function Category() {
        return $this->belongsTo('App\Forum_category', 'cat_id');
    }
}