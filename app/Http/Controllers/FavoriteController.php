<?php

namespace App\Http\Controllers;

use App\Category;
use App\Participant;
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
        $input = $request->all();
        $favorite = [];
        $favorite_cat = [];
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:categories,id',
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
            'product_id' => 'required|exists:categories,id',
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
                $products = Favorite::select('id', 'product_id', 'user_id')->has('Product')
                    ->with('Product')
                    ->where('type', $type)
                    ->where('user_id', $user->id)
                    ->orderBy('id', 'desc')
                    ->simplePaginate(12);

                for ($i = 0; $i < count($products); $i++) {
                    $products[$i]['Product']->price = number_format((float)($products[$i]['Product']->price), 3);
                    if ($user) {
                        $favorite = Favorite::where('user_id', $user->id)->where('product_id', $products[$i]['product_id'])->first();
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

                for ($i = 0; $i < count($products); $i++) {
                    if ($products[$i]['category_type'] == '0') {
                        $category = Category::where('id', $products[$i]['product_id'])->first();
                        if ($lang == 'ar') {
                            $products[$i]['title'] = $category->title_ar;
                        } else {
                            $products[$i]['title'] = $category->title_en;
                        }
                    } elseif ($products[$i]['category_type'] == '1') {
                        $category = SubCategory::where('id', $products[$i]['product_id'])->first();
                        if ($lang == 'ar') {
                            $products[$i]['title'] = $category->title_ar;
                        } else {
                            $products[$i]['title'] = $category->title_en;
                        }
                    } elseif ($products[$i]['category_type'] == '2') {
                        $category = SubTwoCategory::where('id', $products[$i]['product_id'])->first();
                        if ($lang == 'ar') {
                            $products[$i]['title'] = $category->title_ar;
                        } else {
                            $products[$i]['title'] = $category->title_en;
                        }
                    } elseif ($products[$i]['category_type'] == '3') {
                        $category = SubThreeCategory::where('id', $products[$i]['product_id'])->first();
                        if ($lang == 'ar') {
                            $products[$i]['title'] = $category->title_ar;
                        } else {
                            $products[$i]['title'] = $category->title_en;
                        }
                    } elseif ($products[$i]['category_type'] == '4') {
                        $category = SubFourCategory::where('id', $products[$i]['product_id'])->first();
                        if ($lang == 'ar') {
                            $products[$i]['title'] = $category->title_ar;
                        } else {
                            $products[$i]['title'] = $category->title_en;
                        }
                    } elseif ($products[$i]['category_type'] == '5') {
                        $category = SubFiveCategory::where('id', $products[$i]['product_id'])->first();
                        if ($lang == 'ar') {
                            $products[$i]['title'] = $category->title_ar;
                        } else {
                            $products[$i]['title'] = $category->title_en;
                        }
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
}
