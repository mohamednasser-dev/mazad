<?php

if (!function_exists('image_cloudinary_url')){
    function image_cloudinary_url(){
        $url = 'https://res.cloudinary.com/dsiwuyzs4/image/upload/w_100,q_100/v1581928924/';
        return  $url ;
    }
}

if (!function_exists('media_image_cloudinary_url')){
    function media_image_cloudinary_url(){
        $url = 'https://res.cloudinary.com/dsiwuyzs4/image/upload/v1581928924/';
        return  $url ;
    }
}

if (!function_exists('settings')){
    function settings(){
        return   App\Setting::where('id',1)->first();
    }
}

