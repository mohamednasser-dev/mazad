<?php

use Illuminate\Http\Request;

    /*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
    */

    Route::group([
        'middleware' => 'api',
        'prefix' => 'auth'
    ], function ($router) {
        Route::post('login/{lang}/{v}', [ 'as' => 'login', 'uses' => 'AuthController@login'])->middleware('checkguest');
        Route::post('logout/{lang}/{v}', 'AuthController@logout');
        Route::post('refresh/{lang}/{v}', 'AuthController@refresh');
        Route::post('me/{lang}/{v}', 'AuthController@me');
        Route::post('register/{lang}/{v}' , [ 'as' => 'register', 'uses' => 'AuthController@register'])->middleware('checkguest');
    });

    Route::get('/invalid/{lang}/{v}', [ 'as' => 'invalid', 'uses' => 'AuthController@invalid']);


    // users apis group
    Route::group([
        'middleware' => 'api',
        'prefix' => 'user'
    ], function($router) {
        Route::get('profile/{lang}/{v}' , 'UserController@getprofile');
        Route::post('profile/{lang}/{v}' , 'UserController@updateprofile');
        Route::put('resetpassword/{lang}/{v}' , 'UserController@resetpassword');
        Route::put('resetforgettenpassword/{lang}/{v}' , 'UserController@resetforgettenpassword')->middleware('checkguest');
        Route::post('checkphoneexistance/{lang}/{v}' , 'UserController@checkphoneexistance')->middleware('checkguest');
        Route::post('checkphoneexistanceandroid/{lang}/{v}' , 'UserController@checkphoneexistanceandroid')->middleware('checkguest');
        Route::get('notifications/{lang}/{v}' , 'UserController@notifications');
        Route::get('adscount/{lang}/{v}' , 'UserController@getadscount');
        Route::get('current_ads/{lang}/{v}' , 'UserController@getcurrentads');
        Route::get('expired_ads/{lang}/{v}' , 'UserController@getexpiredads');
        Route::post('renew_ad/{lang}/{v}' , 'UserController@renewad');
        Route::delete('delete_ad/{lang}/{v}' , 'UserController@deletead');
        Route::put('edit_ad/{lang}/{v}' , 'UserController@editad');
        Route::delete('delete_ad_image/{lang}/{v}' , 'UserController@delteadimage');
        Route::get('ad_details/{id}/{lang}/{v}' , 'UserController@getaddetails');
    });

    Route::get('ad_owner_profile/{id}/{lang}/{v}' , 'UserController@getownerprofile')->middleware('checkguest');
    Route::get('ad_owner_info/{id}/{lang}/{v}' , 'ProductController@ad_owner_info')->middleware('checkguest');

    Route::get('products/{lang}/{v}' , 'ProductController@getproducts')->middleware('checkguest');
    Route::get('products/search/{lang}/{v}' , 'ProductController@getsearch')->middleware('checkguest');
    Route::post('products/make_mazad/{lang}/{v}' , 'ProductController@make_mazad');

    //  plans apis
    Route::group([
        'middleware' => 'api',
        'prefix' => 'plans'
    ],function($router){
        Route::get('pricing/{lang}/{v}' , 'PlanController@getpricing')->middleware('checkguest');
        Route::post('buy/{lang}/{v}' , 'PlanController@buyplan');
    });

    Route::get('/excute_pay' , 'PlanController@excute_pay');
    Route::get('/pay/success' , 'PlanController@pay_sucess');
    Route::get('/pay/error' , 'PlanController@pay_error');


    Route::group([
        'middleware' => 'api',
        'prefix' => 'products'
    ] , function($router){
        Route::post('create/{lang}/{v}' , 'ProductController@create');
        Route::post('uploadimages/{lang}/{v}' , 'ProductController@uploadimages');
        Route::get('details/{id}/{lang}/{v}' , 'ProductController@getdetails')->middleware('checkguest');
    });

    // offers
    Route::get('/offers/{lang}/{v}' , 'ProductController@getoffers')->middleware('checkguest');

    // feature offers
    Route::get('/feature-offers/{lang}/{v}' , 'ProductController@getFeatureOffers')->middleware('checkguest');

    Route::group([
        'middleware' => 'api',
        'prefix' => 'categories'
    ], function($router){
        Route::get('/{lang}/{v}' , 'CategoryController@get_categories')->middleware('checkguest');
    });

    // sub category level 1
    Route::get('/sub_categories/level1/{category_id}/{lang}/{v}' , 'CategoryController@getAdSubCategories')->middleware('checkguest');
    // sub category level 2
    Route::get('/sub_categories/level2/{sub_category_id}/{lang}/{v}' , 'CategoryController@get_sub_categories_level2')->middleware('checkguest');
    // sub category level 3
    Route::get('/sub_categories/level3/{sub_category_id}/{lang}/{v}' , 'CategoryController@get_sub_categories_level3')->middleware('checkguest');
    // sub category level 4
    Route::get('/sub_categories/level4/{sub_category_id}/{lang}/{v}' , 'CategoryController@get_sub_categories_level4')->middleware('checkguest');
    // sub category level 5
    Route::get('/sub_categories/level5/{sub_category_id}/{lang}/{v}' , 'CategoryController@get_sub_categories_level5')->middleware('checkguest');
    // products last level
    Route::get('/products/last-level/{sub_category_id}/{lang}/{v}' , 'CategoryController@getproducts')->middleware('checkguest');

    // get home data
    Route::get('/home/{lang}/{v}' , 'HomeController@gethome')->middleware('checkguest');

    // get home data
    Route::get('/home-ads/{lang}/{v}' , 'HomeController@getHomeAds')->middleware('checkguest');

    //forum ...
    Route::get('/all_forum/{lang}/{v}' , 'ForumController@all_forum')->middleware('checkguest');
    Route::get('/forum_by_cat/{cat_id}/{lang}/{v}' , 'ForumController@forum_by_cat')->middleware('checkguest');
    Route::get('/Forum_details/{forum_id}/{lang}/{v}' , 'ForumController@Forum_details')->middleware('checkguest');

    // send contact us message
    Route::post('/contactus/{lang}/{v}' , 'ContactUsController@SendMessage')->middleware('checkguest');

    // get app number
    Route::get('/getappnumber/{lang}/{v}' , 'SettingController@getappnumber')->middleware('checkguest');

    // get whats app number
    Route::get('/getwhatsappnumber/{lang}/{v}' , 'SettingController@getwhatsapp')->middleware('checkguest');

    Route::get('/showbuybutton/{lang}/{v}' , 'SettingController@showbuybutton')->middleware('checkguest');


    //nasser code
    //for get cat toads/search create ad
    Route::get('/ad/sub_categories/level0/{lang}/{v}' , 'CategoryController@show_first_cat');
    Route::get('/ad/sub_categories/level1/{cat_id}/{lang}/{v}' , 'CategoryController@show_second_cat');

    Route::get('/ad/sub_categories/level2/{sub_category_id}/{lang}/{v}' , 'CategoryController@show_third_cat');

    Route::get('/ad/sub_categories/level3/{sub_category_id}/{lang}/{v}' , 'CategoryController@show_four_cat');
    Route::get('/ad/sub_categories/level4/{sub_category_id}/{lang}/{v}' , 'CategoryController@show_five_cat');
    Route::get('/ad/sub_categories/level5/{sub_category_id}/{lang}/{v}' , 'CategoryController@show_six_cat');

    //search ads
    Route::post('/ads/search/{lang}/{v}' , 'ProductController@getsearch');
    Route::post('/ads/filter/{lang}/{v}' , 'ProductController@filter');
    Route::get('/ad/max_min_price/{lang}/{v}' , 'ProductController@max_min_price');

    //delete my ad
    Route::get('/ad/delete/{id}/{lang}/{v}' , 'ProductController@delete_my_ad');

    //re new my ad   (re publish)
    Route::get('ad/republish_ad/{product_id}/{plan_id}/{lang}/{v}' , 'ProductController@republish_ad');

    //to edit ad
    Route::get('/ad/select_ad_data/{id}/{lang}/{v}' , 'ProductController@select_ad_data');
    Route::get('/ad/remove_main_image/{id}/{lang}/{v}' , 'ProductController@remove_main_image');
    Route::get('/ad/remove_product_image/{image_id}/{lang}/{v}' , 'ProductController@remove_product_image');
    Route::post('/ad/update/{id}/{lang}/{v}' , 'ProductController@update_ad');

    //marka select
    Route::get('/ad/get_marka/{lang}/{v}' , 'MarkaController@get_marka');
    Route::get('/ad/get_marka_types/{marka_id}/{lang}/{v}' , 'MarkaController@get_marka_types');
    Route::get('/ad/get_type_model/{marka_type_id}/{lang}/{v}' , 'MarkaController@get_type_model');
    Route::get('/ad/category_options/{category}/{lang}/{v}' , 'CategoryController@getCategoryOptions');

    //store ad with steps
    Route::post('/ad/save_new_ad/{lang}/{v}' , 'ProductController@save_first_step');
    Route::post('/ad/save_second_step/{lang}/{v}' , 'ProductController@save_second_step');

    Route::get('/ad/select_all_plans/{cat_id}/{lang}/{v}' , 'PlanController@select_all_plans');
    Route::get('/ad/save_third_step/{ad_id}/{plan_id}/{lang}/{v}' , 'ProductController@save_third_step');

    Route::get('/ad/save_third_step_with_money/{ad_id}/{plan_id}/{lang}/{v}' , 'ProductController@save_third_step_with_money');
    Route::get('/ad/save_third_step/excute_pay' , 'ProductController@third_step_excute_pay');


    Route::get('/ad/select_my_ads/{lang}/{v}' , 'ProductController@select_ended_ads');
    Route::get('/ad/ended_ads/{lang}/{v}' , 'ProductController@ended_ads');
    Route::get('/ad/end_mazad/{id}/{lang}/{v}' , 'ProductController@end_mazad');
    Route::get('/ad/current_ads/{lang}/{v}' , 'ProductController@current_ads');
    Route::get('/ad/select_current_ads/{lang}/{v}' , 'ProductController@select_current_ads');
    Route::get('/ad/select_all_ads/{lang}/{v}' , 'ProductController@select_all_ads');
    Route::get('/select_all_mndobeen/{lang}/{v}' , 'MndobController@index');

    //notifications
    Route::get('/sellect_notofications/{lang}/{v}' , 'UserController@notifications');

    //favorite
    Route::get('/favorites/{type}/{lang}/{v}' , 'FavoriteController@getfavorites');
    Route::post('/favorite/create/{lang}/{v}' , 'FavoriteController@addtofavorites');
    Route::post('/favorite/create/category/{lang}/{v}' , 'FavoriteController@add_category_to_favorites');
    Route::post('/favorite/destroy/{lang}/{v}' , 'FavoriteController@removefromfavorites');
    Route::post('/favorite/destroy/category/{lang}/{v}' , 'FavoriteController@remove_category_from_favorites');
    Route::get('/favorite/get_cat_products/{cat_id}/{level_num}/{lang}/{v}' , 'FavoriteController@get_cat_products');
    Route::get('/favorite/filter_cat_products/{cat_id}/{level_num}/{order}/{lang}/{v}' , 'FavoriteController@filter_cat_products');


//terms and condition
    Route::get('/terms/{lang}/{v}' , 'SettingController@terms');
    Route::get('/social_media/{lang}/{v}' , 'SettingController@social_media');
    Route::get('/about_app/{lang}/{v}' , 'SettingController@about_app');
    Route::get('/app_address/{lang}/{v}' , 'SettingController@app_address');

    //auth routes
    Route::get('/my_account/{lang}/{v}' , 'UserController@my_account');
    Route::get('/my_bids/{type}/{lang}/{v}' , 'UserController@my_bids');
    Route::get('/my_balance/{lang}/{v}' , 'UserController@my_balance');

    Route::group([
        'middleware' => 'api'
    ],function($router) {
        Route::post('/addbalance/{lang}/{v}', 'UserController@addbalance');
    });

    Route::get('/wallet/excute_pay' , 'UserController@excute_pay');
    Route::get('/pay/error' , 'UserController@pay_error');
    Route::get('/pay/success' , 'UserController@pay_sucess');

    Route::get('/check_ad/{lang}/{v}' , 'HomeController@check_ad')->middleware('checkguest');
    Route::get('/main_ad/{lang}/{v}' , 'HomeController@main_ad')->middleware('checkguest');

    //balance package
    Route::get('/balance_packages/{lang}/{v}' , 'HomeController@balance_packages');

    //visitor
    Route::post('/visitor/create/{lang}/{v}' , 'VisitorController@create')->middleware('checkguest');


    Route::get('/ad/cities/{lang}/{v}' , 'ProductController@cities');
    Route::get('/ad/mazad_times/{lang}/{v}' , 'ProductController@get_mazad_times');
    Route::get('/ad/areas/{city_id}/{lang}/{v}' , 'ProductController@areas');
    Route::get('/ad/last_seen/{lang}/{v}' , 'ProductController@last_seen');
    Route::get('/ad/offer_ads/{lang}/{v}' , 'ProductController@offer_ads')->middleware('checkguest');
    Route::get('/payments_date/{lang}/{v}' , 'UserController@payments_date');

    //chat api
    Route::get('/chat/test_exists_conversation/{id}/{lang}/{v}' , 'ChatController@test_exists_conversation');
    Route::post('/chat/send_message/{lang}/{v}' , 'ChatController@store');
    Route::get('/chat/my_messages/{lang}/{v}' , 'ChatController@my_messages');
    Route::get('/chat/get_ad_message/{id}/{conversation_id}/{lang}/{v}' , 'ChatController@get_ad_message');
    Route::get('/chat/search_conversation/{search}/{lang}/{v}' , 'ChatController@search_conversation');
    Route::get('/chat/make_read/{message_id}/{lang}/{v}' , 'ChatController@make_read');



