<?php

namespace App\Http\Controllers;

use App\SubCategory;
use App\SubTwoCategory;
use Illuminate\Http\Request;
use App\Helpers\APIHelpers;
use App\Balance_package;
use App\ProductImage;
use App\Plan_details;
use Carbon\Carbon;
use App\Favorite;
use App\Category;
use App\Product;
use App\Main_ad;
use App\Setting;
use App\User;
use App\Ad;
use Illuminate\Support\Facades\Session;


class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['balance_packages', 'gethome', 'getHomeAds', 'check_ad', 'main_ad']]);
    }

    public function gethome(Request $request)
    {
//        --------------------------------------------- begin scheduled functions --------------------------------------------------------

        $expired = Product::where('status', 1)->whereDate('expiry_date', '<', Carbon::now())->get();
        foreach ($expired as $row) {
            $product = Product::find($row->id);
            $product->status = 2;
            $product->re_post = '0';
            $product->save();
        }

        $not_special = Product::where('status', 1)->where('is_special', '1')->whereDate('expire_special_date', '<', Carbon::now())->get();
        foreach ($not_special as $row) {
            $product_special = Product::find($row->id);
            $product_special->is_special = '0';
            $product_special->save();
        }
        $mytime = Carbon::now();
        $today = Carbon::parse($mytime->toDateTimeString())->format('Y-m-d H:i');
        $re_post_ad = Product::where('status', 1)->where('re_post', '1')->whereDate('re_post_date', '<', Carbon::now())->get();
        foreach ($re_post_ad as $row) {

            $product_re_post = Product::find($row->id);
            $product_re_post->created_at = Carbon::now();
            // to generate new next repost date ...
            $re_post = Plan_details::where('plan_id', $row->plan_id)->where('type', 're_post')->first();
            $final_pin_date = Carbon::createFromFormat('Y-m-d H:i', $today);
            $final_expire_re_post_date = $final_pin_date->addDays($re_post->expire_days);

            $product_re_post->re_post_date = $final_expire_re_post_date;
            $product_re_post->save();
        }

        $pin_ad = Product::where('status', 1)->where('pin', '1')->whereDate('expire_pin_date', '<', Carbon::now())->get();
        foreach ($pin_ad as $row) {
            $product_pined = Product::find($row->id);
            $product_pined->pin = '0';
            $product_pined->save();
        }

        $pin_ad = Setting::where('id', 1)->whereDate('free_loop_date', '<', Carbon::now())->first();
        if ($pin_ad != null) {
            if ($pin_ad->is_loop_free_balance == 'y') {
                $all_users = User::where('active', 1)->get();
                foreach ($all_users as $row) {
                    $user = User::find($row->id);
                    $user->my_wallet = $user->my_wallet + $pin_ad->free_loop_balance;
                    $user->free_balance = $user->free_balance + $pin_ad->free_loop_balance;
                    $user->save();
                }
                $final_pin_date = Carbon::createFromFormat('Y-m-d H:i', $today);
                $final_free_loop_date = $final_pin_date->addDays($pin_ad->free_loop_period);
                $pin_ad->free_loop_date = $final_free_loop_date;
                $pin_ad->save();
            }
        }
//        --------------------------------------------- end scheduled functions --------------------------------------------------------

        $data['slider'] = Ad::select('id', 'image', 'type', 'content')->where('place', 1)->get();
        $data['ads'] = Ad::select('id', 'image', 'type', 'content')->where('place', 2)->get();
        $data['categories'] = Category::select('id', 'image', 'title_ar as title')->where('deleted', 0)->get();
        $data['offers'] = Product::where('offer', 1)->where('status', 1)->where('deleted', 0)->where('publish', 'Y')->select('id', 'title', 'price', 'type')->get();
        for ($i = 0; $i < count($data['offers']); $i++) {
            $data['offers'][$i]['image'] = ProductImage::where('product_id', $data['offers'][$i]['id'])->select('image')->first()['image'];
            $user = auth()->user();
            if ($user) {
                $favorite = Favorite::where('user_id', $user->id)->where('product_id', $data['offers'][$i]['id'])->first();
                if ($favorite) {
                    $data['offers'][$i]['favorite'] = true;
                } else {
                    $data['offers'][$i]['favorite'] = false;
                }
            } else {
                $data['offers'][$i]['favorite'] = false;
            }
            // $data['offers'][$i]['favorite'] = false;

        }
        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

    public function getHomeAds(Request $request)
    {
        $user = auth()->user();
        $lang = $request->lang;
        Session::put('lang_api', $request->lang);
        $one = Ad::select('id', 'image', 'type', 'content')->where('place', 1)->get();
        if (count($one) > 0) {
            $data['ads_top'] = $one;
        } else {
            $data['ads_top'] = (object)[];
        }
        $categories = Category::has('Sub_categories')
            ->with('Sub_categories')
            ->with('Category_ads')
            ->where('deleted', 0)
            ->select('id', 'title_'.$lang.' as title')
            ->get()->map(function($data){
                foreach ($data->Sub_categories as $key=> $row){
                    $exists_cats = SubTwoCategory::where(function ($q) {
                        $q->has('SubCategories', '>', 0);
                    })->where('deleted', 0)->where('sub_category_id', $row->id)->get();
                    if(count($exists_cats) > 0){
                        $data['Sub_categories'][$key]->next_level = true ;
                    }else{
                        $data['Sub_categories'][$key]->next_level = false ;
                    }
                }
                return $data;
            });
        $data['categories'] = $categories;
        $favorites = [];
        if ($user != null) {
            $my_favorites = Favorite::select('id', 'product_id', 'user_id')
                ->with('Product')
                ->where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get();

            $inc = 0;
            foreach ($my_favorites as $key => $row) {
                $product = Product::where('id', $row->product_id)->first();
                if ($product != null) {
                    if ($product->status == 1 && $product->deleted == 0 && $product->publish == 'Y') {
                        $favorites[$inc]['id'] = $product->id;
                        $favorites[$inc]['title'] = $product->title;
                        $favorites[$inc]['image'] = $product->main_image;
                        $favorites[$inc]['price']  = number_format((float)( $product->price), 3);
                        $favorites[$inc]['favorite'] = true;
                        $favorites[$inc]['created_at'] = $product->created_at;
                        $favorites[$inc]['views'] = count($product->Views);
                        $inc = $inc + 1;
                    }
                }
            }
        }
        $data['favorites'] = $favorites;

        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

//nasser code
    // main ad page
    public function main_ad(Request $request)
    {
        $data = Main_ad::select('image')->where('deleted', '0')->inRandomOrder()->take(1)->get();
        if (count($data) == 0) {
            $response = APIHelpers::createApiResponse(true, 406, 'no ads available',
                'لا يوجد اعلانات', null, $request->lang);
            return response()->json($response, 406);
        }
        foreach ($data as $image) {
            $image['image'] = $image->image;
        }
        $response = APIHelpers::createApiResponse(false, 200, '', '', $image, $request->lang);
        return response()->json($response, 200);
    }

    public function check_ad(Request $request)
    {
        $ads = Main_ad::select('image')->where('deleted', '0')->get();
        if (count($ads) > 0) {
            $data['show_ad'] = true;
        } else {
            $data['show_ad'] = false;
        }
        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

    public function balance_packages(Request $request)
    {
        if ($request->lang == 'en') {
            $data['packages'] = Balance_package::where('status', 'show')->select('id', 'name_en as title', 'price', 'amount', 'desc_en as desc')->orderBy('title', 'desc')->get();
        } else {
            $data['packages'] = Balance_package::where('status', 'show')->select('id', 'name_ar as title', 'price', 'amount', 'desc_ar as desc')->orderBy('title', 'desc')->get();
        }
        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }
}
