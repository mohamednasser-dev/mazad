<?php

namespace App\Http\Controllers;

use App\Category;
use App\Participant;
use App\Product_view;
use App\SubCategory;
use App\SubFiveCategory;
use App\SubFourCategory;
use App\SubThreeCategory;
use App\SubTwoCategory;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Helpers\APIHelpers;
use App\Favorite;
use App\Product;


class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function addtofavorites(Request $request)
    {
        $user = auth()->user();
        if ($user->active == 0) {
            $response = APIHelpers::createApiResponse(true, 406, 'تم حظر حسابك', 'تم حظر حسابك', null, $request->lang);
            return response()->json($response, 406);
        }
        $input = $request->all();
        $favorite = [];
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id'
            ]);
            unset($input['category_type']);
            $favorite = Favorite::where('product_id', $request->product_id)->where('type', 'product')->where('user_id', $user->id)->first();
        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true, 406, $validator->errors()->first(), $validator->errors()->first(), null, $request->lang);
            return response()->json($response, 406);
        }
        if ($favorite) {
            $response = APIHelpers::createApiResponse(true, 406, 'تم إضافه هذا المزاد للمفضله من قبل', 'تم إضافه هذا المزاد للمفضله من قبل', null, $request->lang);
            return response()->json($response, 406);
        }

        $input['user_id'] = $user->id;
        $input['type'] = 'product';
        Favorite::create($input);
        $response = APIHelpers::createApiResponse(false, 200, '', '', $favorite, $request->lang);
        return response()->json($response, 200);

    }

    public function add_category_to_favorites(Request $request)
    {
        $user = auth()->user();
        if ($user->active == 0) {
            $response = APIHelpers::createApiResponse(true, 406, 'تم حظر حسابك', 'تم حظر حسابك', null, $request->lang);
            return response()->json($response, 406);
        }
        if ($request->product_id == 0) {
            $response = APIHelpers::createApiResponse(true, 406,'you should choose category','يجب اختيار قسم اولا', null, $request->lang);
            return response()->json($response, 406);
        }
        $input = $request->all();
        $favorite = [];
        $favorite_cat = [];
            $validator = Validator::make($request->all(), [
                'product_id' => 'required',
                'category_type' => 'required'
            ]);
        $favorite_cat = Favorite::where('product_id', $request->product_id)->where('type', 'category')
                ->where('category_type', $request->category_type)->where('user_id', $user->id)->first();
        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true, 406, $validator->errors()->first(), $validator->errors()->first(), null, $request->lang);
            return response()->json($response, 406);
        }
        if ($favorite_cat) {
            $response = APIHelpers::createApiResponse(true, 406, 'تم إضافه هذا القسم للمفضله من قبل', 'تم إضافه هذا القسم للمفضله من قبل', null, $request->lang);
            return response()->json($response, 406);
        }
        $input['user_id'] = $user->id;
        $input['type'] = 'category';
        Favorite::create($input);
        $response = APIHelpers::createApiResponse(false, 200, '', '', $favorite, $request->lang);
        return response()->json($response, 200);

    }

    public function removefromfavorites(Request $request)
    {
        $user = auth()->user();
        if ($user->active == 0) {
            $response = APIHelpers::createApiResponse(true, 406, 'تم حظر حسابك', 'تم حظر حسابك', (object)[], $request->lang);
            return response()->json($response, 406);
        }
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id'
        ]);
        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true, 406, $validator->errors()->first(), $validator->errors()->first(), null, $request->lang);
            return response()->json($response, 406);
        }
        $favorite = Favorite::where('product_id', $request->product_id)->where('type', 'product')->where('user_id', $user->id)->first();
        if ($favorite) {
            $favorite->delete();
            $response = APIHelpers::createApiResponse(false, 200, 'Deteted ', 'تم الحذف', (object)[], $request->lang);
            return response()->json($response, 200);
        } else {
            $response = APIHelpers::createApiResponse(true, 406, 'هذا المنتج غير موجود بالمفضله', 'هذا المنتج غير موجود بالمفضله', null, $request->lang);
            return response()->json($response, 406);
        }
    }

    public function remove_category_from_favorites(Request $request)
    {
        $user = auth()->user();
        if ($user->active == 0) {
            $response = APIHelpers::createApiResponse(true, 406, 'تم حظر حسابك', 'تم حظر حسابك', (object)[], $request->lang);
            return response()->json($response, 406);
        }
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'category_type' => 'required'
        ]);
        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true, 406, $validator->errors()->first(), $validator->errors()->first(), null, $request->lang);
            return response()->json($response, 406);
        }
        $favorite = Favorite::where('product_id', $request->product_id)->where('type', 'category')->where('category_type', $request->category_type)->where('user_id', $user->id)->first();
        if ($favorite) {
            $favorite->delete();
            $response = APIHelpers::createApiResponse(false, 200, 'Deteted ', 'تم الحذف', (object)[], $request->lang);
            return response()->json($response, 200);
        } else {
            $response = APIHelpers::createApiResponse(true, 406, 'هذا المنتج غير موجود بالمفضله', 'هذا المنتج غير موجود بالمفضله', null, $request->lang);
            return response()->json($response, 406);
        }
    }

    public function getfavorites(Request $request, $type)
    {
        $user = auth()->user();
        $lang = $request->lang;
        Session::put('lang', $lang);
        if ($user->active == 0) {
            $response = APIHelpers::createApiResponse(true, 406, 'تم حظر حسابك', 'تم حظر حسابك', null, $request->lang);
            return response()->json($response, 406);
        } else {
            if ($type == 'product') {
                $products = Favorite::select('id', 'product_id', 'user_id')->whereHas('Product')
                    ->with('Product')
                    ->where('type', $type)
                    ->where('user_id', $user->id)
                    ->orderBy('id', 'desc')
                    ->simplePaginate(12);

                for ($i = 0; $i < count($products); $i++) {
                    $products[$i]['Product']->price = number_format((float)($products[$i]['Product']->price), 3);
                    if ($user) {
                        $favorite = Favorite::where('user_id', $user->id)->where('type', 'product')->where('product_id', $products[$i]['product_id'])->first();
                        if ($favorite) {
                            $products[$i]['Product']->favorite = true;
                        } else {
                            $products[$i]['Product']->favorite = false;
                        }

                        $conversation = Participant::where('ad_product_id', $products[$i]['product_id'])->where('user_id', $user->id)->first();
                        if ($conversation == null) {
                            $products[$i]['Product']->conversation_id = 0;
                        } else {
                            $products[$i]['Product']->conversation_id = $conversation->conversation_id;
                        }
                    } else {
                        $products[$i]['favorite'] = false;
                        $products[$i]['conversation_id'] = 0;
                    }
                }
            } elseif ($type == 'category') {
                $products = Favorite::select('id', 'product_id', 'user_id', 'type', 'category_type')
                    ->where('type', $type)
                    ->where('user_id', $user->id)
                    ->orderBy('id', 'desc')
                    ->simplePaginate(12);
                $title = 'title_'.$lang;
                for ($i = 0; $i < count($products); $i++) {
                    if ($products[$i]['category_type'] == '0') {
                        $category = Category::where('id', $products[$i]['product_id'])->first();
                        if ($lang == 'ar') {
                            $products[$i]['title'] = $category->title_ar;
                        } else {
                            $products[$i]['title'] = $category->title_en;
                        }

                        $products[$i]['first_level_name'] = $category->$title;
                        $products[$i]['category_id'] = $category->id;
                        $products[$i]['sub_category_level1_id'] = 0 ;
                        $products[$i]['sub_category_level2_id'] = 0 ;
                        $products[$i]['sub_category_level3_id'] = 0 ;
                        $products[$i]['sub_category_level4_id'] = 0 ;
                        $products[$i]['sub_category_level5_id'] = 0 ;
                    } elseif ($products[$i]['category_type'] == '1') {
                        $category = SubCategory::where('id', $products[$i]['product_id'])->first();
                        if ($lang == 'ar') {
                            $products[$i]['title'] = $category->title_ar;
                        } else {
                            $products[$i]['title'] = $category->title_en;
                        }
                        $products[$i]['first_level_name'] = $category->$title;
                        $products[$i]['category_id'] = $category->category_id;
                        $products[$i]['sub_category_level1_id'] = $products[$i]['product_id'] ;
                        $products[$i]['sub_category_level2_id'] = 0 ;
                        $products[$i]['sub_category_level3_id'] = 0 ;
                        $products[$i]['sub_category_level4_id'] = 0 ;
                        $products[$i]['sub_category_level5_id'] = 0 ;
                    } elseif ($products[$i]['category_type'] == '2') {
                        $category = SubTwoCategory::where('id', $products[$i]['product_id'])->first();
                        if ($lang == 'ar') {
                            $products[$i]['title'] = $category->title_ar;
                        } else {
                            $products[$i]['title'] = $category->title_en;
                        }
                        $products[$i]['first_level_name'] = $category->category->$title;
                        $products[$i]['category_id'] = $category->category->category_id;
                        $products[$i]['sub_category_level1_id'] = $category->sub_category_id;
                        $products[$i]['sub_category_level2_id'] = $products[$i]['product_id'] ;
                        $products[$i]['sub_category_level3_id'] = 0 ;
                        $products[$i]['sub_category_level4_id'] = 0 ;
                        $products[$i]['sub_category_level5_id'] = 0 ;
                    } elseif ($products[$i]['category_type'] == '3') {
                        $category = SubThreeCategory::where('id', $products[$i]['product_id'])->first();
                        if ($lang == 'ar') {
                            $products[$i]['title'] = $category->title_ar;
                        } else {
                            $products[$i]['title'] = $category->title_en;
                        }
                        $products[$i]['first_level_name'] = $category->category->category->$title;
                        $products[$i]['category_id'] = $category->category->category->category_id;
                        $products[$i]['sub_category_level1_id'] = $category->category->sub_category_id;
                        $products[$i]['sub_category_level2_id'] = $category->sub_category_id;
                        $products[$i]['sub_category_level3_id'] = $products[$i]['product_id'] ;
                        $products[$i]['sub_category_level4_id'] = 0 ;
                        $products[$i]['sub_category_level5_id'] = 0 ;

                    } elseif ($products[$i]['category_type'] == '4') {
                        $category = SubFourCategory::where('id', $products[$i]['product_id'])->first();
                        if ($lang == 'ar') {
                            $products[$i]['title'] = $category->title_ar;
                        } else {
                            $products[$i]['title'] = $category->title_en;
                        }
                        $products[$i]['first_level_name'] = $category->category->category->category->$title;
                        $products[$i]['category_id'] = $category->category->category->category->category_id;
                        $products[$i]['sub_category_level1_id'] = $category->category->category->sub_category_id;
                        $products[$i]['sub_category_level2_id'] = $category->category->sub_category_id;
                        $products[$i]['sub_category_level3_id'] = $category->sub_category_id;
                        $products[$i]['sub_category_level4_id'] = $products[$i]['product_id'] ;
                        $products[$i]['sub_category_level5_id'] = 0 ;

                    } elseif ($products[$i]['category_type'] == '5') {
                        $category = SubFiveCategory::where('id', $products[$i]['product_id'])->first();
                        if ($lang == 'ar') {
                            $products[$i]['title'] = $category->title_ar;
                        } else {
                            $products[$i]['title'] = $category->title_en;
                        }
                        $products[$i]['first_level_name'] = $category->category->category->category->category->$title;
                        $products[$i]['category_id'] = $category->category->category->category->category->category_id;
                        $products[$i]['sub_category_level1_id'] = $category->category->category->category->sub_category_id;
                        $products[$i]['sub_category_level2_id'] = $category->category->category->sub_category_id;
                        $products[$i]['sub_category_level3_id'] = $category->Category->sub_category_id;
                        $products[$i]['sub_category_level4_id'] = $category->sub_category_id;
                        $products[$i]['sub_category_level5_id'] = $products[$i]['product_id'] ;
                    }
                    $products[$i]['favorite'] = true;

                }
            }

            if (count($products) > 0) {
                $response = APIHelpers::createApiResponse(false, 200, '', '', $products, $request->lang);
            } else {
                $response = APIHelpers::createApiResponse(false, 200, 'no item favorite to show', 'لا يوجد عناصر للعرض', $products, $request->lang);
            }
            return response()->json($response, 200);
        }
    }

    public function get_cat_products(Request $request, $cat_id , $level_num)
    {
        $user = auth()->user();
        $lang = $request->lang;
        Session::put('lang', $lang);
        if ($user->active == 0) {
            $response = APIHelpers::createApiResponse(true, 406, 'تم حظر حسابك', 'تم حظر حسابك', null, $request->lang);
            return response()->json($response, 406);
        } else {
            $products = Product::where('status', 1)->where('deleted', 0)->where('publish', 'Y');
            if ($level_num == 0) {
                $products = $products->where('category_id', $cat_id);
            }elseif($level_num == 1) {
                $products = $products->where('sub_category_id', $cat_id);
            }elseif($level_num == 2) {
                $products = $products->where('sub_category_two_id', $cat_id);
            }elseif($level_num == 3) {
                $products = $products->where('sub_category_three_id', $cat_id);
            }elseif($level_num == 4) {
                $products = $products->where('sub_category_four_id', $cat_id);
            }elseif($level_num == 5) {
                $products = $products->where('sub_category_five_id', $cat_id);
            }
            if($request->orderBy == null){
                $request->orderBy = 'asc';
            }
            $products = $products->select('id', 'title', 'price', 'main_image as image', 'pin', 'created_at')->orderBy('price', $request->orderBy)->simplePaginate(12);
            for ($i = 0; $i < count($products); $i++) {
                $products[$i]['price']= number_format((float)($products[$i]['price']), 3);
                $views = Product_view::where('product_id', $products[$i]['id'])->get()->count();
                $products[$i]['views'] = $views;
                $user = auth()->user();
                if ($user) {
                    $favorite = Favorite::where('user_id', $user->id)->where('type', 'product')->where('product_id', $products[$i]['id'])->first();
                    if ($favorite) {
                        $products[$i]['favorite'] = true;
                    } else {
                        $products[$i]['favorite'] = false;
                    }
                    $conversation = Participant::where('ad_product_id', $products[$i]['id'])->where('user_id', $user->id)->first();
                    if ($conversation == null) {
                        $products[$i]['conversation_id'] = 0;
                    } else {
                        $products[$i]['conversation_id'] = $conversation->conversation_id;
                    }
                } else {
                    $products[$i]['favorite'] = false;
                    $products[$i]['conversation_id'] = 0;
                }
                $products[$i]['time'] = APIHelpers::get_month_day($products[$i]['created_at'], $lang);
            }

            if (count($products) > 0) {
                $response = APIHelpers::createApiResponse(false, 200, '', '', $products, $request->lang);
            } else {
                $response = APIHelpers::createApiResponse(false, 200, 'no item favorite to show', 'لا يوجد عناصر للعرض', $products, $request->lang);
            }
            return response()->json($response, 200);
        }
    }

    public function filter_cat_products(Request $request, $cat_id , $level_num , $order)
    {
        $user = auth()->user();
        $lang = $request->lang;
        Session::put('lang', $lang);
        if ($user->active == 0) {
            $response = APIHelpers::createApiResponse(true, 406, 'تم حظر حسابك', 'تم حظر حسابك', null, $request->lang);
            return response()->json($response, 406);
        } else {
            $products = Product::where('status', 1)->where('deleted', 0)->where('publish', 'Y');
            if ($level_num == 0) {
                $products = $products->where('category_id', $cat_id);
            }elseif($level_num == 1) {
                $products = $products->where('sub_category_id', $cat_id);
            }elseif($level_num == 2) {
                $products = $products->where('sub_category_two_id', $cat_id);
            }elseif($level_num == 3) {
                $products = $products->where('sub_category_three_id', $cat_id);
            }elseif($level_num == 4) {
                $products = $products->where('sub_category_four_id', $cat_id);
            }elseif($level_num == 5) {
                $products = $products->where('sub_category_five_id', $cat_id);
            }
            $products = $products->select('id', 'title', 'price', 'main_image as image', 'pin', 'created_at')
                ->orderBy('price', $order)->simplePaginate(12);
            for ($i = 0; $i < count($products); $i++) {
                $products[$i]['price']= number_format((float)($products[$i]['price']), 3);
                $views = Product_view::where('product_id', $products[$i]['id'])->get()->count();
                $products[$i]['views'] = $views;
                $user = auth()->user();
                if ($user) {
                    $favorite = Favorite::where('user_id', $user->id)->where('type', 'product')->where('product_id', $products[$i]['id'])->first();
                    if ($favorite) {
                        $products[$i]['favorite'] = true;
                    } else {
                        $products[$i]['favorite'] = false;
                    }
                    $conversation = Participant::where('ad_product_id', $products[$i]['id'])->where('user_id', $user->id)->first();
                    if ($conversation == null) {
                        $products[$i]['conversation_id'] = 0;
                    } else {
                        $products[$i]['conversation_id'] = $conversation->conversation_id;
                    }
                } else {
                    $products[$i]['favorite'] = false;
                    $products[$i]['conversation_id'] = 0;
                }
                $products[$i]['time'] = APIHelpers::get_month_day($products[$i]['created_at'], $lang);
            }

            if (count($products) > 0) {
                $response = APIHelpers::createApiResponse(false, 200, '', '', $products, $request->lang);
            } else {
                $response = APIHelpers::createApiResponse(false, 200, 'no item favorite to show', 'لا يوجد عناصر للعرض', $products, $request->lang);
            }
            return response()->json($response, 200);
        }
    }
}
