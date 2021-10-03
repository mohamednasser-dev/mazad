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
            ->where('deleted',0)->where('publish','Y');
    }

    public function Category() {
        return $this->belongsTo('App\Category', 'product_id')->where('deleted',0)->select('id','title_'.session('lang').' as title');
    }

    public function Sub_category() {
        return $this->belongsTo('App\SubCategory', 'product_id')->where('deleted',0)->select('id','title_'.session('lang').' as title');
    }

    public function Sub_twoCategory() {
        return $this->belongsTo('App\SubTwoCategory', 'product_id')->where('deleted',0)->select('id','title_'.session('lang').' as title');
    }
    public function Sub_threeCategory() {
        return $this->belongsTo('App\SubThreeCategory', 'product_id')->where('deleted',0)->select('id','title_'.session('lang').' as title');
    }
    public function Sub_fourCategory() {
        return $this->belongsTo('App\SubFourCategory', 'product_id')->where('deleted',0)->select('id','title_'.session('lang').' as title');
    }
    public function Sub_fiveCategory() {
        return $this->belongsTo('App\SubFiveCategory', 'product_id')->where('deleted','0')->select('id','title_'.session('lang').' as title');
    }

}
