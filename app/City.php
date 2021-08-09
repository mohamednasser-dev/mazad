<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['image','title_ar', 'title_en','deleted'];

    public function Areas()
    {
        if (session('api_lang') == 'en') {
            return $this->hasMany('App\Area','city_id', 'id')->where('deleted','0')
            ->select('id' , 'title_en as title','city_id');
        }else{
            return $this->hasMany('App\Area','city_id', 'id')->where('deleted','0')
            ->select('id' , 'title_ar as title','city_id');
        }
        
    }
}
