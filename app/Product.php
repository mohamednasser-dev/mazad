<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
//    protected $dates = ['publication_date'];
    protected $fillable = ['title','description', 'price','category_id','sub_category_id','sub_category_two_id','expire_special_date',
        'sub_category_three_id','sub_category_four_id','user_id', 'type','publication_date','re_post_date','is_special',
        'views', 'offer', 'status', 'expiry_date','main_image','expire_pin_date','created_at','plan_id','publish',
        'sub_category_five_id','choose_it','city_id','area_id','latitude','longitude','share_location','deleted'];
    public function category() {
        return $this->belongsTo('App\Category', 'category_id');
    }

    public function category_name() {
        $api_lang = session('lang');
        return $this->belongsTo('App\Category', 'category_id')->select('id','title_'.$api_lang.' as title');
    }
    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }
    public function Product_user() {
        return $this->belongsTo('App\User', 'user_id')->select('id','image','name','email','phone');
    }
    public function Plan() {
        return $this->belongsTo('App\Plan', 'plan_id');
    }
    public function images() {
        return $this->hasMany('App\ProductImage', 'product_id');
    }
    public function Features() {
        return $this->hasMany('App\Product_feature', 'product_id');
    }
    public function Views() {
        return $this->hasMany('App\Product_view', 'product_id');
    }
    public function City() {
        return $this->belongsTo('App\City', 'city_id');
    }
    public function Area() {
        return $this->belongsTo('App\Area', 'area_id');
    }

    public function Area_name() {
        if(session('lang') == 'ar') {
            return $this->belongsTo('App\Area', 'area_id')->select('id','title_ar as title');
        }else{
            return $this->belongsTo('App\Area', 'area_id')->select('id','title_en as title');
        }
    }

    public function City_api() {
        if(session('local_api') == 'ar'){
            return $this->belongsTo('App\City', 'city_id')->select('id','title_ar as title');
        }else{
            return $this->belongsTo('App\City', 'city_id')->select('id','title_en as title');
        }
    }
    public function Area_api() {
        if(session('local_api') == 'ar'){
            return $this->belongsTo('App\Area', 'area_id')->select('id','title_ar as title');
        }else{
            return $this->belongsTo('App\Area', 'area_id')->select('id','title_en as title');
        }
    }


//    public function getCreatedAtAttribute($value)
//    {
////        2021-07-13 16:24:23
//        return Carbon::createFromFormat('y-m-d h:i:s', $value)->translatedformat('F');
//    }

    public function getCreatedAtAttribute($date)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->translatedformat('F d');
    }
}
