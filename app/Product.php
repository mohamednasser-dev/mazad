<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
//    protected $dates = ['publication_date'];
    protected $appends = array('mazad_count','remain_hours','remain_seconds');
    protected $fillable = ['title','description', 'price','category_id','sub_category_id','sub_category_two_id','expire_special_date',
        'sub_category_three_id','sub_category_four_id','user_id', 'type','publication_date','re_post_date','is_special',
        'views', 'offer', 'status', 'expiry_date','main_image','expire_pin_date','created_at','plan_id','publish',
        'sub_category_five_id','choose_it','city_id','area_id','latitude','longitude','share_location',
        'deleted','day_count_id','min_price','show_whatsapp'];
    public function category() {
        return $this->belongsTo('App\Category', 'category_id');
    }

    public function category_name() {
        $api_lang = session('lang');
        return $this->belongsTo('App\Category', 'category_id')->select('id','title_'.$api_lang.' as title');
    }
    public function user() {
        return $this->belongsTo('App\User', 'user_id')->select('id','name','phone','email','image');
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

    public function Product_Mazads() {
        return $this->hasMany('App\Product_mazad', 'product_id');
    }

    public function getMazadCountAttribute()
    {
        $number =  Product_mazad::where('product_id',$this->id)->get()->count();
        return $number;
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

    public function getPriceAttribute($price)
    {
        if(session('price_float') == 'true' ){
            $sum_price = Product_mazad::where('product_id',$this->id)->orderBy('price','desc')->first();
            if($sum_price){
                return  $sum_price->price;
            }else{
                return  $price;
            }
        }else{
            $sum_price = Product_mazad::where('product_id',$this->id)->orderBy('price','desc')->first();
            if($sum_price){
                return number_format((float)( $sum_price->price), 3);
            }else{
                return number_format((float)( $price), 3);
            }
        }

    }

    public function getMinPriceAttribute($min_price)
    {
        if(session('price_float') == 'true' ){
            return  $min_price;
        }else {
            $min_price = number_format((float)($min_price), 3);
            return $min_price;
        }
    }

    public function getRemainHoursAttribute()
    {
        $product = Product::where('id',$this->id)->first();
        $remaining_hours = Carbon::now()->diffInHours($product->expiry_date, false);
        return $remaining_hours;
    }

    public function getRemainSecondsAttribute()
    {
        $product = Product::where('id',$this->id)->first();
        $remaining_hours = Carbon::now()->diffInSeconds($product->expiry_date, false);
        return $remaining_hours;
    }

//    public function winner_data() {
//        return  $this->hasOne(Product_mazad::class, 'product_id', 'id')
//            ->where('status','winner')->with('user');
//
//    }
}
