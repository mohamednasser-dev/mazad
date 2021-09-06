<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product_mazad extends Model
{
    protected $fillable = ['ip', 'product_id','user_id','price','status'];

    public function Product() {
        return $this->belongsTo('App\Product', 'product_id')
            ->select('id','main_image','title','user_id','price','created_at')->with('user');
    }

    public function current_ads() {
        return $this->belongsTo('App\Product', 'product_id')
            ->select('id','title','main_image','price','description','created_at')
            ->where('deleted',0)->where('status',1)->where('publish','Y');
    }

    public function ended_ads() {
        return $this->belongsTo('App\Product', 'product_id')
            ->select('id','title','main_image','price','description','created_at')
            ->where('deleted',0)->where('status',2)->where('publish','Y');
    }

    public function user() {
        return $this->belongsTo('App\User', 'user_id')->select('id','name','phone','email','image');
    }

}
