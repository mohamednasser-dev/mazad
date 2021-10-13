<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product_view extends Model
{
    protected $fillable = ['ip', 'product_id','user_id'];

    public function Product() {
        return $this->belongsTo('App\Product', 'product_id')
            ->select('id','title','main_image','price','description','created_at')
            ->where('deleted',0)->where('publish','Y');
    }
}
