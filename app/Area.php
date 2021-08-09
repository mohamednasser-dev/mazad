<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = ['image','title_ar', 'title_en','city_id','deleted'];
}
