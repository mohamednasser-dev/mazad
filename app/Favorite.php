<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = ['user_id', 'product_id','type','category_type'];

    public function User() {
        return $this->belongsTo('App\User', 'user_id');
    }
    public function Product() {
        return $this->belongsTo('App\Product', 'product_id')
            ->select('id','title','main_image','price','description','created_at')
            ->where('deleted',0)->where('status',1)->where('publish','Y');
    }

    public function Category() {
        return $this->belongsTo('App\Category', 'product_id');
    }
}
