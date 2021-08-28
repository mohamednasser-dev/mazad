<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Forum_category extends Model
{
    protected $fillable = ['image','title_ar','title_en','parent_cat_id','type','deleted'];
}
