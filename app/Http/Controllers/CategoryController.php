<?php

namespace App\Http\Controllers;

use App\Categories_ad;
use App\Notification;
use App\Participant;
use App\Product_mazad;
use App\User;
use App\UserNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Category_option_value;
use Illuminate\Http\Request;
use App\Helpers\APIHelpers;
use App\SubThreeCategory;
use App\SubFiveCategory;
use App\Category_option;
use App\SubFourCategory;
use App\SubTwoCategory;
use App\Product_view;
use App\SubCategory;
use App\Favorite;
use App\Category;
use App\Product;
use JD\Cloudder\Facades\Cloudder;


class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['getSubTwoCategoryOptions', 'getSubCategoryOptions', 'show_six_cat', 'getCategoryOptions', 'show_five_cat', 'show_four_cat', 'show_third_cat', 'show_second_cat', 'show_first_cat', 'get_categories', 'getAdSubCategories', 'get_sub_categories_level2', 'get_sub_categories_level3', 'get_sub_categories_level4', 'get_sub_categories_level5', 'getproducts']]);
        $expired = Product::where('status', 1)->whereDate('expiry_date', '<', Carbon::now())->get();
        foreach ($expired as $row) {
            $product = Product::find($row->id);
            $product->status = 2;
            $product->re_post = '0';
            $product->save();

            $max_price = Product_mazad::where('product_id', $row->id)->orderBy('created_at', 'desc')->first();
            if ($max_price) {
                $max_price->status = 'winner';
                $max_price->save();
                //winner user notify
                $user = User::find($max_price->user_id);
                $fcm_token = $user->fcm_token;
                $insert_notification = new Notification();
                $insert_notification->image = null;
                $insert_notification->title = 'تم انتهاء المزاد';
                $insert_notification->body = 'انت الفائز بالمزاد ' . $product->title;
                $insert_notification->save();
                $user_notification = new UserNotification();
                $user_notification->notification_id = $insert_notification->id;
                $user_notification->user_id = $max_price->user_id;
                $user_notification->save();
                APIHelpers::send_notification('تم انتهاء المزاد', 'انت الفائز بالمزاد ' . $product->title, null, null, [$fcm_token]);

            }
            //mazad user owner notify
            $owner_user = User::find($product->user_id);
            $owner_fcm_token = $owner_user->fcm_token;
            $insert_owner_notify = new Notification();
            $insert_owner_notify->image = null;
            $insert_owner_notify->title = 'تم انتهاء المزاد';
            $insert_owner_notify->body = 'تم انتهاء المزاد الخاص بك - ' . $product->title;
            $insert_owner_notify->save();
            $user_owner_notification = new UserNotification();
            $user_owner_notification->notification_id = $insert_owner_notify->id;
            $user_owner_notification->user_id = $product->user_id;
            $user_owner_notification->save();
            APIHelpers::send_notification('تم انتهاء المزاد', 'تم انتهاء المزاد الخاص بك - ' . $product->title, null, null, [$owner_fcm_token]);

        }
    }

    public function getCatsSubCats($model, $lang, $show = true, $cat_id = 0, $all = false, $whereIn = [])
    {
        $categories = $model::has('Products', '>', 0)
            ->where('deleted', 0);
        if ($model == '\App\SubCategory' && $cat_id != 0) {
            $categories = $categories->where('category_id', $cat_id);
        } elseif ($model != '\App\Category' && $cat_id != 0) {
            $categories = $categories->where('sub_category_id', $cat_id);
        }
        if (count($whereIn) > 0 && $model != '\App\SubCategory') {
            $categories = $categories->whereIn('sub_category_id', $whereIn);
        }

        if (count($whereIn) > 0 && $model == '\App\SubCategory') {
            $categories = $categories->whereIn('category_id', $whereIn);
        }

        $categories = $categories->select('id', 'title_' . $lang . ' as title', 'image')->orderBy('sort', 'asc')->get()->makeHidden(['ViewSubCategories', 'products'])
            ->map(function ($row) use ($show, $model) {
                if ($show) {
                    $row->products_count = count($row->products);
                }
                $row->next_level = false;
                $subCategories = $row->ViewSubCategories;

                if ($subCategories && count($subCategories) > 0) {
                    $hasProducts = false;
                    for ($n = 0; $n < count($subCategories); $n++) {

                        if ($model != '\App\SubFiveCategory') {
                            if ($subCategories[$n]->products != null && count($subCategories[$n]->products) > 0) {
                                $hasProducts = true;
                            }
                        }
                    }
                    if ($hasProducts) {
                        $row->next_level = true;
                    }
                }
                $row->selected = false;
                return $row;
            })->toArray();

        if ($all) {
            $title = 'All';
            if ($lang == 'ar') {
                $title = 'الكل';
            }
            $all = [
                'id' => 0,
                'image' => "",
                'title' => $title,
                'next_level' => false,
                'selected' => false
            ];

            array_unshift($categories, $all);
        }

        return $categories;
    }

    public function getcategories(Request $request)
    {
        $lang = $request->lang;
        $categories = Category::where('deleted', 0)->select('id', 'title_' . $lang . ' as title', 'image')->orderBy('sort', 'asc')->get();

        for ($i = 0; $i < count($categories); $i++) {
            //text next level
            $subTwoCats = SubCategory::where('category_id', $categories[$i]['id'])->where('deleted', 0)->select('id')->first();
            $categories[$i]['next_level'] = false;
            if (isset($subTwoCats['id'])) {
                $categories[$i]['next_level'] = true;
            }

            /*
                        if ($categories[$i]['next_level'] == true) {
                            // check after this level layers
                            $data_ids = SubCategory::where('deleted', '0')->where('category_id', $categories[$i]['id'])->select('id')->get()->toArray();
                            $subFiveCats = SubTwoCategory::whereIn('sub_category_id', $data_ids)->where('deleted', '0')->select('id', 'deleted')->get();
                            if (count($subFiveCats) == 0) {
                                $have_next_level = false;
                            } else {
                                $have_next_level = true;
                            }
                            if ($have_next_level == false) {
                                $categories[$i]['next_level'] = false;
                            } else {
                                $categories[$i]['next_level'] = true;
                                break;
                            }
                            //End check
                        }
                        */
        }

        // $data = Categories_ad::select('image','ad_type','content as link')->where('type','category')->inRandomOrder()->take(1)->get();
        $response = APIHelpers::createApiResponse(false, 200, '', '', array('categories' => $categories), $request->lang);
        return response()->json($response, 200);
    }

    public function get_categories(Request $request)
    {
        $lang = $request->lang;
        $categories = Category::where('deleted', 0)->select('id', 'title_' . $lang . ' as title', 'image')->orderBy('sort', 'asc')->get();

        for ($i = 0; $i < count($categories); $i++) {
            //text next level
            $subTwoCats = SubCategory::where('category_id', $categories[$i]['id'])->where('deleted', 0)->select('id')->first();
            $categories[$i]['next_level'] = false;
            if (isset($subTwoCats['id'])) {
                $categories[$i]['next_level'] = true;
            }


            if ($categories[$i]['next_level'] == true) {
                // check after this level layers
                $data_ids = SubCategory::where('deleted', '0')->where('category_id', $categories[$i]['id'])->select('id')->get()->toArray();
                $subFiveCats = SubTwoCategory::whereIn('sub_category_id', $data_ids)->where('deleted', '0')->select('id', 'deleted')->get();
                if (count($subFiveCats) == 0) {
                    $have_next_level = false;
                } else {
                    $have_next_level = true;
                }
                if ($have_next_level == false) {
                    $categories[$i]['next_level'] = false;
                } else {
                    $categories[$i]['next_level'] = true;
                    break;
                }
                //End check
            }
        }

        // $data = Categories_ad::select('image','ad_type','content as link')->where('type','category')->inRandomOrder()->take(1)->get();
        $response = APIHelpers::createApiResponse(false, 200, '', '', array('categories' => $categories), $request->lang);
        return response()->json($response, 200);
    }

    // get ad subcategories
    public function getAdSubCategories(Request $request)
    {
        $lang = $request->lang;
        $data['sub_categories'] = SubCategory::where('deleted', 0)->where('category_id', $request->category_id)->select('id', 'image', 'title_' . $lang . ' as title')->orderBy('sort', 'asc')->get()->toArray();

        $data['sub_category_array'] = SubCategory::where('category_id', $request->category_id)->select('id', 'title_' . $lang . ' as title')->where('deleted', 0)->orderBy('sort', 'asc')->get();
        $data['category'] = Category::select('id', 'title_en as title')->find($request->category_id);

        for ($i = 0; $i < count($data['sub_category_array']); $i++) {
            $data['sub_category_array'][$i]['selected'] = false;
        }

        for ($i = 0; $i < count($data['sub_categories']); $i++) {
            $subTwoCats = SubTwoCategory::where('sub_category_id', $data['sub_categories'][$i]['id'])->where('deleted', 0)->select('id')->first();

            if ($subTwoCats != null) {
                $data['sub_categories'][$i]['next_level'] = true;
            } else {
                $data['sub_categories'][$i]['next_level'] = false;
            }


            if ($data['sub_categories'][$i]['next_level'] == true) {
                // check after this level layers
                $data['sub_next_categories'] = SubTwoCategory::where('deleted', 0)->where('sub_category_id', $data['sub_categories'][$i]['id'])->select('id', 'image', 'title_' . $lang . ' as title')->orderBy('sort', 'asc')->get();
                $data_ids = SubTwoCategory::where('deleted', 0)->where('sub_category_id', $data['sub_categories'][$i]['id'])->select('id')->get()->toArray();
                $subFiveCats = SubThreeCategory::whereIn('sub_category_id', $data_ids)->where('deleted', 0)->select('id', 'deleted')->get();
                if (count($subFiveCats) == 0) {
                    $data['sub_categories'][$i]['next_level'] = false;
                } else {
                    $data['sub_categories'][$i]['next_level'] = true;
                    break;
                }
                //End check
            }
        }
        array_unshift($data['sub_categories']);

        $lang = $request->lang;
        $products = Product::where('status', 1)->where('publish', 'Y')->where('deleted', 0)->where('category_id', $request->category_id)->select('id', 'title', 'price', 'main_image as image', 'created_at', 'pin')->orderBy('pin', 'DESC')->orderBy('created_at', 'desc')->simplePaginate(12);
        for ($i = 0; $i < count($products); $i++) {
            $products[$i]['price'] = number_format((float)($products[$i]['price']), 3);
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

        $data['products'] = $products;
        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

    public function get_sub_categories_level2(Request $request)
    {
        $lang = $request->lang;
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'category_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true, 406, 'بعض الحقول مفقودة', 'بعض الحقول مفقودة', null, $request->lang);
            return response()->json($response, 406);
        }
        if ($request->sub_category_id != 0) {
            $data['sub_categories'] = SubTwoCategory::where('deleted', 0)->where('sub_category_id', $request->sub_category_id)
                ->select('id', 'image', 'title_' . $lang . ' as title', 'sub_category_id')
                ->orderBy('sort', 'asc')->get()->toArray();
            $data['sub_category_level1'] = SubCategory::where('id', $request->sub_category_id)->select('id', 'title_' . $lang . ' as title', 'category_id')->first();
            $data['sub_category_array'] = SubTwoCategory::where('sub_category_id', $request->sub_category_id)
                ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')
                ->where('deleted', 0)->orderBy('sort', 'asc')->get()->makeHidden('category_id')->toArray();

            if (count($data['sub_category_array']) == 0) {
                $data['sub_category_array'] = SubCategory::where('category_id', $request->category_id)
                    ->where('deleted', 0)->select('id', 'title_' . $lang . ' as title', 'category_id')->orderBy('sort', 'asc')->get()->makeHidden('category_id')->toArray();
            }
            $data['category'] = Category::where('id', $data['sub_category_level1']['category_id'])->select('id', 'title_' . $lang . ' as title')->first();
        } else {
            $subCategories = SubCategory::where('category_id', $request->category_id)->where('deleted', 0)->pluck('id')->toArray();
            $data['sub_categories'] = SubTwoCategory::where('deleted', 0)->whereIn('sub_category_id', $subCategories)->select('id', 'image', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
            $data['sub_category_level1'] = (object)[
                "id" => 0,
                "title" => "All",
                "category_id" => $request->category_id
            ];
            $data['sub_category_array'] = SubTwoCategory::where('sub_category_id', $request->sub_category_id)
                ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')
                ->where('deleted', 0)->orderBy('sort', 'asc')->get()->makeHidden('category_id')->toArray();
            if (count($data['sub_category_array']) == 0) {
                $data['sub_category_array'] = SubCategory::where('category_id', $request->category_id)
                    ->select('id', 'title_' . $lang . ' as title', 'category_id')
                    ->where('deleted', 0)->orderBy('sort', 'asc')->get()->makeHidden('category_id')->toArray();

            }
            $data['category'] = Category::where('id', $request->category_id)->select('id', 'title_' . $lang . ' as title')->first();
        }

        for ($n = 0; $n < count($data['sub_category_array']); $n++) {
            $data['sub_category_array'][$n]['selected'] = false;
        }
        if (count($data['sub_categories']) > 0) {
            for ($i = 0; $i < count($data['sub_categories']); $i++) {
                //make favorite
                if ($user) {
                    $favorite = Favorite::where('user_id', $user->id)->where('type', 'category')
                        ->where('category_type', '2')->where('product_id', $data['sub_categories'][$i]['id'])->first();
                    if ($favorite) {
                        $data['sub_categories'][$i]['favorite'] = true;
                    } else {
                        $data['sub_categories'][$i]['favorite'] = false;
                    }
                } else {
                    $data['sub_categories'][$i]['favorite'] = false;
                }
                $subThreeCats = SubThreeCategory::where('sub_category_id', $data['sub_categories'][$i]['id'])
                    ->where('deleted', 0)->select('id')->first();
                if ($subThreeCats != null) {
                    $data['sub_categories'][$i]['next_level'] = true;
                } else {
                    $data['sub_categories'][$i]['next_level'] = false;
                }
                if ($data['sub_categories'][$i]['next_level'] == true) {
                    // check after this level layers
                    $data['sub_next_categories'] = SubThreeCategory::where('deleted', 0)->where('sub_category_id', $data['sub_categories'][$i]['id'])->select('id', 'image', 'title_' . $lang . ' as title')->orderBy('sort', 'asc')->get()->toArray();
                    if (count($data['sub_next_categories']) > 0) {
                        for ($i = 0; $i < count($data['sub_next_categories']); $i++) {
                            $subFiveCats = SubFourCategory::where('sub_category_id', $data['sub_next_categories'][$i]['id'])->where('deleted', 0)->select('id', 'deleted')->get();
                            if (count($subFiveCats) == 0) {
                                $have_next_level = false;
                            } else {
                                $have_next_level = true;
                            }
                            if ($have_next_level == false) {
                                $data['sub_categories'][$i]['next_level'] = false;
                                break;
                            } else {
                                $data['sub_categories'][$i]['next_level'] = true;
                                break;
                            }
                        }
                    }
                    //End check
                }
            }
        }
        $lang = $request->lang;
        //to add all button
        $title = 'All';
        if ($request->lang == 'ar') {
            $title = 'الكل';
        }
        $all = new \StdClass;
        $all->id = 0;
        $all->title = $title;
        $all->sub_category_id = $request->sub_category_id;
        $all->selected = false;
        array_unshift($data['sub_category_array'], $all);

        $sub_categories = SubTwoCategory::where('deleted', 0)->where('sub_category_id', $request->sub_category_id)
            ->select('id')
            ->orderBy('sort', 'asc')->get()->toArray();


        $subThreeCats = SubThreeCategory::whereIn('sub_category_id', $sub_categories)
            ->where('deleted', 0)->select('id')->get();
        if (count($subThreeCats) == 0) {
            $data['sub_category_array'] = [$all];
        }

        array_unshift($data['sub_categories']);
        if ($request->orderBy == null) {
            $request->orderBy = 'desc';
        }
        if ($request->sub_category_id == 0) {
            $products = Product::where('status', 1)->where('publish', 'Y')->where('deleted', 0)
                ->where('category_id', $request->category_id)
                ->select('id', 'title', 'price', 'main_image as image', 'created_at', 'pin')->orderBy('price', $request->orderBy)->simplePaginate(12);
        } else {
            $products = Product::where('status', 1)->where('publish', 'Y')->where('deleted', 0)
                ->where('sub_category_id', $request->sub_category_id)
                ->select('id', 'title', 'price', 'main_image as image', 'created_at', 'pin')->orderBy('price', $request->orderBy)->simplePaginate(12);
        }
        for ($i = 0; $i < count($products); $i++) {
//            $products[$i]['created_at']= Carbon::createFromFormat('Y-m-d H:i:s', $products[$i]['created_at'])->translatedformat('F');

            $products[$i]['price'] = number_format((float)($products[$i]['price']), 3);
            $views = Product_view::where('product_id', $products[$i]['id'])->get()->count();
            $products[$i]['views'] = $views;

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

        $cat_ids[] = null;
        for ($i = 0; $i < count($data['sub_categories']); $i++) {
            $cat_ids[$i] = $data['sub_categories'][$i]['id'];
        }
        $data['ad_image'] = Categories_ad::select('image', 'ad_type as type', 'content')
            ->where('deleted', '0')->wherein('cat_id', $cat_ids)
            ->where('deleted', '0')->where('type', 'sub_two_category')->inRandomOrder()->take(1)->get();

        $data['products'] = $products;
        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

    public function get_sub_categories_level3(Request $request)
    {
        $lang = $request->lang;
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'category_id' => 'required'
        ]);

        if ($validator->fails() && !isset($request->sub_category_level1_id)) {
            $response = APIHelpers::createApiResponse(true, 406, 'بعض الحقول مفقودة', 'بعض الحقول مفقودة', null, $request->lang);
            return response()->json($response, 406);
        }

        $subCategories = SubCategory::where('category_id', $request->category_id)->pluck('id')->toArray();
        $subCategoriesTwo = SubTwoCategory::where('deleted', 0)->whereIn('sub_category_id', $subCategories)->pluck('id')->toArray();

        if ($request->sub_category_id != 0) {
            $data['sub_categories'] = SubThreeCategory::where('deleted', 0)->where('sub_category_id', $request->sub_category_id)->select('id', 'image', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();

            $data['sub_category_level2'] = SubTwoCategory::where('id', $request->sub_category_id)->select('id', 'title_' . $lang . ' as title', 'sub_category_id')->first();
            if ($request->sub_category_level1_id != 0) {
                $data['sub_category_array'] = SubThreeCategory::where('deleted', 0)->where('sub_category_id', $request->sub_category_id)
                    ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
            } else {
                $data['sub_category_array'] = SubThreeCategory::where('deleted', 0)->whereIn('sub_category_id', $request->sub_category_id)
                    ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
            }
            $data['category'] = Category::where('id', $request->category_id)->select('id', 'title_' . $lang . ' as title')->first();
        } else {
            $data['sub_category_level2'] = (object)[
                "id" => 0,
                "title" => "All",
                "sub_category_id" => $request->sub_category_level1_id
            ];
            if ($request->sub_category_level1_id != 0) {
                $data['sub_category_array'] = SubThreeCategory::where('sub_category_id', $request->sub_category_id)
                    ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
            } else {
                $data['sub_category_array'] = SubThreeCategory::whereIn('sub_category_id', $request->sub_category_id)
                    ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
            }
            $data['sub_categories'] = SubThreeCategory::where('deleted', 0)->whereIn('sub_category_id', $subCategoriesTwo)->select('id', 'image', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();

            $data['category'] = Category::where('id', $request->category_id)->select('id', 'title_' . $lang . ' as title')->first();


        }


        for ($i = 0; $i < count($data['sub_categories']); $i++) {
            $cat_ids[$i] = $data['sub_categories'][$i]['id'];
        }
        // $data['ad_image'] = Categories_ad::select('image','ad_type','content as link')->wherein('cat_id',$cat_ids)->where('type','sub_three_category')->inRandomOrder()->take(1)->get();


        $All_sub_cat = false;

        if (count($data['sub_categories']) > 0) {

            for ($i = 0; $i < count($data['sub_categories']); $i++) {
                //make favorite
                if ($user) {
                    $favorite = Favorite::where('user_id', $user->id)->where('type', 'category')->where('category_type', '3')->where('product_id', $data['sub_categories'][$i]['id'])->first();
                    if ($favorite) {
                        $data['sub_categories'][$i]['favorite'] = true;
                    } else {
                        $data['sub_categories'][$i]['favorite'] = false;
                    }
                } else {
                    $data['sub_categories'][$i]['favorite'] = false;
                }
                $subThreeCats = SubFourCategory::where('deleted', 0)->where('sub_category_id', $data['sub_categories'][$i]['id'])->get();
                if (count($subThreeCats) > 0) {
                    $data['sub_categories'][$i]['next_level'] = true;
                } else {
                    $data['sub_categories'][$i]['next_level'] = false;
                }

                if ($data['sub_categories'][$i]['next_level'] == true) {

                    // check after this level layers
                    $data['sub_next_categories'] = SubFourCategory::where('deleted', 0)->where('sub_category_id', $data['sub_categories'][$i]['id'])->pluck('id')->toArray();


                    if (count($data['sub_next_categories']) > 0) {


                        $subFiveCats = SubFiveCategory::whereIn('sub_category_id', $data['sub_next_categories'])
                            ->where('deleted', '0')->get();

                        if (count($subFiveCats) == 0) {
                            $data['sub_categories'][$i]['next_level'] = false;
                        } else {
                            $data['sub_categories'][$i]['next_level'] = true;
                            break;
                        }

                    }
                    //End check
                }
//                if ($All_sub_cat == false) {
//                    if ($data['sub_categories'][$i]['next_level'] == false) {
//                        $All_sub_cat = false;
//                    } else {
//                        $All_sub_cat = true;
//                    }
//                }

            }
        }

        if ($All_sub_cat == false) {
            if ($request->sub_category_id != 0) {
                if ($request->sub_category_id != 0) {
                    $data['sub_category_array'] = SubThreeCategory::where('sub_category_id', $request->sub_category_id)
                        ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
                } else {
                    $data['sub_category_array'] = SubThreeCategory::whereIn('sub_category_id', $subCategoriesTwo)
                        ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
                }
            } else {
                if ($request->sub_category_id != 0) {
                    $data['sub_category_array'] = SubThreeCategory::where('sub_category_id', $request->sub_category_id)
                        ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
                } else {
                    $data['sub_category_array'] = SubThreeCategory::whereIn('sub_category_id', $subCategoriesTwo)
                        ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
                }
            }

            for ($n = 0; $n < count($data['sub_category_array']); $n++) {
                if ($n == 0) {
                    $data['sub_category_array'][$n]['selected'] = true;
                } else {
                    $data['sub_category_array'][$n]['selected'] = false;
                }
            }
        }
        if (count($data['sub_category_array']) == 0) {
            $data['sub_category_array'] = SubTwoCategory::where('sub_category_id', $request->sub_category_level1_id)
                ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')
                ->orderBy('sort', 'asc')->get()->makeHidden('category_id')->toArray();
        }

        for ($n = 0; $n < count($data['sub_category_array']); $n++) {
            if ($data['sub_category_array'][$n]['id'] == $request->sub_category_id) {
                $data['sub_category_array'][$n]['selected'] = true;
            } else {
                $data['sub_category_array'][$n]['selected'] = false;
            }
        }
        //to add all button
        $title = 'All';
        if ($request->lang == 'ar') {
            $title = 'الكل';
        }
        $all = new \StdClass;
        $all->id = 0;
        $all->title = $title;
        $all->sub_category_id = $request->sub_category_id;
        $all->selected = false;

        array_unshift($data['sub_category_array'], $all);
        array_unshift($data['sub_categories']);
        $products = Product::where('status', 1)->where('deleted', 0)->where('publish', 'Y')
            ->where('category_id', $request->category_id)->select('id', 'title', 'price', 'main_image as image', 'pin', 'created_at');
        if ($request->sub_category_id != 0) {
            $products = $products->where('sub_category_two_id', $request->sub_category_id);
        }

        if ($request->sub_category_level1_id != 0) {
            $products = $products->where('sub_category_id', $request->sub_category_level1_id);
        }
        if ($request->orderBy == null) {
            $request->orderBy = 'desc';
        }
        $products = $products->orderBy('price', $request->orderBy)->simplePaginate(12);

        for ($i = 0; $i < count($products); $i++) {
            $products[$i]['price'] = number_format((float)($products[$i]['price']), 3);
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

        $cat_ids[] = null;

        for ($i = 0; $i < count($data['sub_categories']); $i++) {
            $cat_ids[$i] = $data['sub_categories'][$i]['id'];
        }
        $data['ad_image'] = Categories_ad::select('image', 'ad_type as type', 'content')
            ->where('deleted', '0')->wherein('cat_id', $cat_ids)->where('deleted', '0')->where('type', 'sub_three_category')->inRandomOrder()->take(1)->get();

        $data['products'] = $products;


        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

    public function get_sub_categories_level4(Request $request)
    {
        $lang = $request->lang;
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'category_id' => 'required'
        ]);

        if ($validator->fails() && !isset($request->sub_category_level2_id) && !isset($request->sub_category_level1_id)) {
            $response = APIHelpers::createApiResponse(true, 406, 'بعض الحقول مفقودة', 'بعض الحقول مفقودة', null, $request->lang);
            return response()->json($response, 406);
        }


        if ($request->sub_category_level1_id == 0) {
            $subCategories = SubCategory::where('deleted', 0)->where('category_id', $request->category_id)->pluck('id')->toArray();
            $subCategoriesTwo = SubTwoCategory::where('deleted', 0)->whereIn('sub_category_id', $subCategories)->pluck('id')->toArray();
        } else {
            $subCategoriesTwo = SubTwoCategory::where('deleted', 0)->where('sub_category_id', $request->sub_category_level1_id)->pluck('id')->toArray();
        }

        if ($request->sub_category_level2_id == 0) {
            $subCategoriesThree = SubThreeCategory::where('deleted', 0)->whereIn('sub_category_id', $subCategoriesTwo)->pluck('id')->toArray();
        } else {
            $subCategoriesThree = SubThreeCategory::where('deleted', 0)->where('sub_category_id', $request->sub_category_level2_id)->pluck('id')->toArray();
        }

        if ($request->sub_category_id != 0) {
            $data['sub_categories'] = SubFourCategory::where('deleted', 0)->where('sub_category_id', $request->sub_category_id)->select('id', 'image', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
            $data['sub_category_level3'] = SubThreeCategory::where('id', $request->sub_category_id)->select('id', 'title_' . $lang . ' as title', 'sub_category_id')->first();
            if ($request->sub_category_level2_id == 0) {
                $data['sub_category_array'] = SubFourCategory::where('deleted', 0)->whereIn('sub_category_id', $request->sub_category_id)
                    ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
            } else {
                $data['sub_category_array'] = SubFourCategory::where('deleted', 0)->where('sub_category_id', $request->sub_category_id)
                    ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
            }

            $data['category'] = Category::where('id', $request->category_id)->select('id', 'title_' . $lang . ' as title')->first();
        } else {
            $data['sub_categories'] = SubFourCategory::where('deleted', 0)->whereIn('sub_category_id', $subCategoriesThree)->select('id', 'image', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
            $data['sub_category_level3'] = (object)[
                "id" => 0,
                "title" => "All",
                "sub_category_id" => $request->sub_category_level2_id
            ];
            if ($request->sub_category_level2_id == 0) {
                $data['sub_category_array'] = SubFourCategory::where('deleted', 0)->whereIn('sub_category_id', $request->sub_category_id)
                    ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
            } else {
                $data['sub_category_array'] = SubFourCategory::where('deleted', 0)->where('sub_category_id', $request->sub_category_id)
                    ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
            }

            $data['category'] = Category::where('id', $request->category_id)->select('id', 'title_' . $lang . ' as title')->first();
        }

        $cat_ids[] = null;
        $All_sub_cat = false;
        for ($i = 0; $i < count($data['sub_categories']); $i++) {
            //make favorite
            if ($user) {
                $favorite = Favorite::where('user_id', $user->id)->where('type', 'category')->where('category_type', '4')->where('product_id', $data['sub_categories'][$i]['id'])->first();
                if ($favorite) {
                    $data['sub_categories'][$i]['favorite'] = true;
                } else {
                    $data['sub_categories'][$i]['favorite'] = false;
                }
            } else {
                $data['sub_categories'][$i]['favorite'] = false;
            }
            $cat_ids[$i] = $data['sub_categories'][$i]['id'];
            $subThreeCats = SubFiveCategory::where('sub_category_id', $data['sub_categories'][$i]['id'])->where('deleted', '0')->select('id')->first();
            $data['sub_categories'][$i]['next_level'] = false;
            if (isset($subThreeCats['id'])) {
                $data['sub_categories'][$i]['next_level'] = true;
            }
            if ($data['sub_categories'][$i]['next_level'] == true) {
                // check after this level layers
                $data['sub_next_categories'] = SubFiveCategory::where('deleted', '0')->where('sub_category_id', $subThreeCats->id)->select('id', 'image', 'title_' . $lang . ' as title')->orderBy('sort', 'asc')->get()->toArray();
                if (count($data['sub_next_categories']) > 0) {
                    for ($i = 0; $i < count($data['sub_next_categories']); $i++) {
                        $five_products = Product::where('sub_category_five_id', $data['sub_next_categories'][$i]['id'])->where('status', 1)->where('publish', 'Y')->where('deleted', 0)->first();
                        if ($five_products == null) {
                            $have_next_level = false;
                        } else {
                            $have_next_level = true;
                        }
                        if ($have_next_level == false) {
                            $data['sub_categories'][$i]['next_level'] = false;
                        } else {
                            $data['sub_categories'][$i]['next_level'] = true;
                            break;
                        }
                    }
                }
                //End check
            }
        }
        if (count($data['sub_category_array']) == 0) {
            $data['sub_category_array'] = SubThreeCategory::where('deleted', 0)->where('sub_category_id', $request->sub_category_level2_id)->select('id', 'image', 'title_' . $lang . ' as title')->orderBy('sort', 'asc')->get()->toArray();
        }


        for ($n = 0; $n < count($data['sub_category_array']); $n++) {
            if ($data['sub_category_array'][$n]['id'] == $request->sub_category_id) {
                $data['sub_category_array'][$n]['selected'] = true;
            } else {
                $data['sub_category_array'][$n]['selected'] = false;
            }
        }
        //to add all button
        $title = 'All';
        if ($request->lang == 'ar') {
            $title = 'الكل';
        }
        $all = new \StdClass;
        $all->id = 0;
        $all->title = $title;
        $all->image = null;
        $all->selected = false;
        array_unshift($data['sub_category_array'], $all);
        //end all button
        $products = Product::where('status', 1)->where('deleted', 0)->where('publish', 'Y');
        if ($request->sub_category_id != 0) {
            $products = $products->where('sub_category_three_id', $request->sub_category_id);

        }

        if ($request->sub_category_level2_id != 0) {
            $products = $products->where('sub_category_two_id', $request->sub_category_level2_id);
        }

        if ($request->sub_category_level1_id != 0) {
            $products = $products->where('sub_category_id', $request->sub_category_level1_id);
        }
        if ($request->orderBy == null) {

            $request->orderBy = 'desc';
        }
        $products = $products->select('id', 'title', 'price', 'main_image as image', 'pin', 'created_at')
            ->orderBy('price', $request->orderBy)->simplePaginate(12);
        for ($i = 0; $i < count($products); $i++) {
            $products[$i]['price'] = number_format((float)($products[$i]['price']), 3);
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

        $data['ad_image'] = Categories_ad::select('image', 'ad_type as type', 'content')->where('deleted', '0')->wherein('cat_id', $cat_ids)->where('deleted', '0')->where('type', 'sub_four_category')->inRandomOrder()->take(1)->get();

        $data['products'] = $products;


        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

    public function get_sub_categories_level5(Request $request)
    {
        $lang = $request->lang;
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'category_id' => 'required'
        ]);
        if ($validator->fails() && !isset($request->sub_category_level2_id) && !isset($request->sub_category_level1_id)) {
            $response = APIHelpers::createApiResponse(true, 406, 'بعض الحقول مفقودة', 'بعض الحقول مفقودة', null, $request->lang);
            return response()->json($response, 406);
        }
        if ($request->sub_category_level1_id == 0) {
            $subCategories = SubCategory::where('deleted', 0)->where('category_id', $request->category_id)->pluck('id')->toArray();
            $subCategoriesTwo = SubTwoCategory::where('deleted', 0)->whereIn('sub_category_id', $subCategories)->pluck('id')->toArray();
        } else {
            $subCategoriesTwo = SubTwoCategory::where('deleted', 0)->where('sub_category_id', $request->sub_category_level1_id)->pluck('id')->toArray();
        }
        if ($request->sub_category_level2_id == 0) {
            $subCategoriesThree = SubThreeCategory::where('deleted', 0)->whereIn('sub_category_id', $subCategoriesTwo)->pluck('id')->toArray();
        } else {
            $subCategoriesThree = SubThreeCategory::where('deleted', 0)->where('sub_category_id', $request->sub_category_level2_id)->pluck('id')->toArray();
        }
        if ($request->sub_category_level3_id == 0) {
            $subCategoriesFour = SubFourCategory::where('deleted', 0)->whereIn('sub_category_id', $subCategoriesThree)->pluck('id')->toArray();
        } else {
            $subCategoriesFour = SubFourCategory::where('deleted', 0)->where('sub_category_id', $request->sub_category_level3_id)->pluck('id')->toArray();
        }
        if ($request->sub_category_id != 0) {
            $data['sub_categories'] = SubFiveCategory::where('deleted', '0')->where('sub_category_id', $request->sub_category_id)->select('id', 'image', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
            $data['sub_category_level4'] = SubFourCategory::where('deleted', 0)->where('sub_category_id', $request->sub_category_id)->select('id', 'image', 'title_' . $lang . ' as title')->first();
            if ($request->sub_category_level3_id == 0) {
                $data['sub_category_array'] = SubFiveCategory::where('deleted', '0')->whereIn('sub_category_id', $request->sub_category_id)
                    ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
            } else {
                $data['sub_category_array'] = SubFiveCategory::where('deleted', '0')->where('sub_category_id', $request->sub_category_id)
                    ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
            }
            $data['category'] = Category::where('id', $request->category_id)->select('id', 'title_' . $lang . ' as title')->first();
        } else {
            $data['sub_categories'] = SubFiveCategory::where('deleted', '0')->where('sub_category_id', $subCategoriesFour)->select('id', 'image', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
            $data['sub_category_level3'] = (object)[
                "id" => 0,
                "title" => "All",
                "sub_category_id" => $request->sub_category_level2_id
            ];
            if ($request->sub_category_level3_id == 0) {
                $data['sub_category_array'] = SubFiveCategory::where('deleted', '0')->whereIn('sub_category_id', $request->sub_category_id)
                    ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
            } else {
                $data['sub_category_array'] = SubFiveCategory::where('deleted', '0')->where('sub_category_id', $request->sub_category_id)
                    ->select('id', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
            }
            $data['category'] = Category::where('id', $request->category_id)->select('id', 'title_' . $lang . ' as title')->first();
        }
        $cat_ids[] = null;
        for ($i = 0; $i < count($data['sub_categories']); $i++) {
            //make favorite
            if ($user) {
                $favorite = Favorite::where('user_id', $user->id)->where('type', 'category')->where('category_type', '5')->where('product_id', $data['sub_categories'][$i]['id'])->first();
                if ($favorite) {
                    $data['sub_categories'][$i]['favorite'] = true;
                } else {
                    $data['sub_categories'][$i]['favorite'] = false;
                }
            } else {
                $data['sub_categories'][$i]['favorite'] = false;
            }
            $cat_ids[$i] = $data['sub_categories'][$i]['id'];
            $five_products = Product::where('sub_category_five_id', $data['sub_categories'][$i]['id'])->where('status', 1)->where('publish', 'Y')->where('deleted', 0)->first();
//            $subFiveCats = SubFiveCategory::where('sub_category_id', $data['sub_categories'][$i]['id'])->where('deleted', '0')->select('id', 'deleted')->first();
            $data['sub_categories'][$i]['next_level'] = false;
            if (isset($five_products['id'])) {
                $data['sub_categories'][$i]['next_level'] = true;
            }

        }

        $data['ad_image'] = Categories_ad::select('image', 'ad_type as type', 'content')->where('deleted', '0')->wherein('cat_id', $cat_ids)->where('deleted', '0')->where('type', 'sub_five_category')->inRandomOrder()->take(1)->get();

        if (count($data['sub_category_array']) == 0) {
            $data['sub_category_array'] = SubFourCategory::where('deleted', 0)->where('sub_category_id', $request->sub_category_level3_id)->select('id', 'image', 'title_' . $lang . ' as title')->orderBy('sort', 'asc')->get()->toArray();
        }
        for ($n = 0; $n < count($data['sub_category_array']); $n++) {
            if ($data['sub_category_array'][$n]['id'] == $request->sub_category_id) {
                $data['sub_category_array'][$n]['selected'] = true;
            } else {
                $data['sub_category_array'][$n]['selected'] = false;
            }
        }
        //to add all button
        $title = 'All';
        if ($request->lang == 'ar') {
            $title = 'الكل';
        }
        $all = new \StdClass;
        $all->id = 0;
        $all->title = $title;
        $all->sub_category_id = $request->sub_category_id;
        $all->selected = false;
        array_unshift($data['sub_category_array'], $all);
        //end all button
        $products = Product::where('status', 1)->where('publish', 'Y')->where('deleted', 0);
        if ($request->sub_category_id != 0) {
            $products = $products->where('sub_category_four_id', $request->sub_category_id);
        }
        if ($request->sub_category_level3_id != 0) {
            $products = $products->where('sub_category_three_id', $request->sub_category_level3_id);
        }
        if ($request->sub_category_level2_id != 0) {
            $products = $products->where('sub_category_two_id', $request->sub_category_level2_id);
        }
        if ($request->sub_category_level1_id != 0) {
            $products = $products->where('sub_category_id', $request->sub_category_level1_id);
        }
        if ($request->orderBy == null) {
            $request->orderBy = 'desc';
        }
        $products = $products->select('id', 'title', 'price', 'main_image as image', 'pin', 'created_at')
            ->orderBy('price', $request->orderBy)->simplePaginate(12);
        for ($i = 0; $i < count($products); $i++) {
            $products[$i]['price'] = number_format((float)($products[$i]['price']), 3);
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
        $data['products'] = $products;


        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

    public function getproducts(Request $request)
    {
        $lang = $request->lang;
        $validator = Validator::make($request->all(), [
            'sub_category_level1_id' => 'required',
            'sub_category_level2_id' => 'required',
            'sub_category_level3_id' => 'required',
            'sub_category_level4_id' => 'required',
            'category_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true, 406, 'بعض الحقول مفقودة', 'بعض الحقول مفقودة', null, $request->lang);
            return response()->json($response, 406);
        }

        if ($request->sub_category_level1_id == 0) {
            $subCategories = SubCategory::where('deleted', 0)->where('category_id', $request->category_id)->pluck('id')->toArray();
            $subCategoriesTwo = SubTwoCategory::where('deleted', 0)->whereIn('sub_category_id', $subCategories)->pluck('id')->toArray();
        } else {
            $subCategoriesTwo = SubTwoCategory::where('deleted', 0)->where('sub_category_id', $request->sub_category_level1_id)->pluck('id')->toArray();
        }

        if ($request->sub_category_level2_id == 0) {
            $subCategoriesThree = SubThreeCategory::where('deleted', 0)->whereIn('sub_category_id', $subCategoriesTwo)->pluck('id')->toArray();
        } else {
            $subCategoriesThree = SubThreeCategory::where('deleted', 0)->where('sub_category_id', $request->sub_category_level2_id)->pluck('id')->toArray();
        }
        if ($request->sub_category_level3_id == 0) {
            $subCategoriesFour = SubFourCategory::where('deleted', 0)->whereIn('sub_category_id', $subCategoriesThree)->pluck('id')->toArray();
        } else {
            $subCategoriesFour = SubFourCategory::where('deleted', 0)->where('sub_category_id', $request->sub_category_level3_id)->pluck('id')->toArray();
        }

        if ($request->sub_category_level4_id != 0) {
            $data['sub_categories'] = SubFiveCategory::where('deleted', '0')->where('sub_category_id', $request->sub_category_level4_id)->select('id', 'image', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
        } else {
            $data['sub_categories'] = SubFiveCategory::where('deleted', '0')->whereIn('sub_category_id', $subCategoriesFour)->select('id', 'image', 'title_' . $lang . ' as title', 'sub_category_id')->orderBy('sort', 'asc')->get()->toArray();
        }
        if ($request->sub_category_level3_id != 0) {
            $data['sub_category_array'] = SubFiveCategory::where('deleted', '0')
                ->where('sub_category_id', $request->sub_category_level4_id)
                ->select('id', 'image', 'title_' . $lang . ' as title')->orderBy('sort', 'asc')->get()->toArray();
        } else {
            $data['sub_category_array'] = SubFiveCategory::where('deleted', '0')
                ->whereIn('sub_category_id', $subCategoriesFour)->select('id', 'image', 'title_' . $lang . ' as title')
                ->orderBy('sort', 'asc')->get()->toArray();
        }
        if (count($data['sub_category_array']) == 0) {
            $data['sub_category_array'] = SubFiveCategory::where('deleted', '0')->where('sub_category_id', $request->sub_category_level4_id)
                ->select('id', 'image', 'title_' . $lang . ' as title')->orderBy('sort', 'asc')->get()->toArray();
        }


        //to add all button
        $title = 'All';
        if ($request->lang == 'ar') {
            $title = 'الكل';
        }
        $all = new \StdClass;
        $all->id = 0;
        $all->title = $title;
        $all->sub_category_id = $request->sub_category_id;
        $all->selected = false;
        array_unshift($data['sub_category_array'], $all);
        //end all button
        $products = Product::where('status', 1)->where('publish', 'Y')->where('deleted', 0);
        if ($request->sub_category_level1_id != 0) {
            $products = $products->where('sub_category_id', $request->sub_category_level1_id);
        }
        if ($request->sub_category_level2_id != 0) {
            $products = $products->where('sub_category_two_id', $request->sub_category_level2_id);
        }
        if ($request->sub_category_level3_id != 0) {
            $products = $products->where('sub_category_three_id', $request->sub_category_level3_id);
        }
        if ($request->sub_category_level4_id != 0) {
            $products = $products->where('sub_category_four_id', $request->sub_category_level4_id);
        }
        $products = $products->where('sub_category_five_id', $request->sub_category_id)->select('id', 'title', 'price', 'main_image as image', 'pin', 'created_at')->where('publish', 'Y')->orderBy('pin', 'DESC')->orderBy('created_at', 'desc')->simplePaginate(12);
        for ($i = 0; $i < count($products); $i++) {
            $products[$i]['price'] = number_format((float)($products[$i]['price']), 3);
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
            $month = $products[$i]['created_at']->format('F');
            $products[$i]['time'] = APIHelpers::get_month_day($products[$i]['created_at'], $lang);
        }
        $data['products'] = $products;
        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }
    //nasser code
    // get ad categories for create ads
    public function show_first_cat(Request $request)
    {
        if ($request->lang == 'en') {
            $data['categories'] = Category::where('deleted', 0)->select('id', 'title_en as title', 'image')->orderBy('sort', 'asc')->get();
        } else {
            $data['categories'] = Category::where('deleted', 0)->select('id', 'title_ar as title', 'image')->orderBy('sort', 'asc')->get();
        }
        if (count($data['categories']) > 0) {
            for ($i = 0; $i < count($data['categories']); $i++) {
                $subThreeCats = SubCategory::where('category_id', $data['categories'][$i]['id'])->where('deleted', 0)->select('id')->first();
                $data['categories'][$i]['next_level'] = false;
                if (isset($subThreeCats['id'])) {
                    $data['categories'][$i]['next_level'] = true;
                }
            }
        }
        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

    public function show_second_cat(Request $request, $cat_id)
    {
        if ($request->lang == 'en') {
            $data['categories'] = SubCategory::where('category_id', $cat_id)->where('deleted', 0)->select('id', 'title_en as title', 'image')->orderBy('sort', 'asc')->get();
        } else {
            $data['categories'] = SubCategory::where('category_id', $cat_id)->where('deleted', 0)->select('id', 'title_ar as title', 'image')->orderBy('sort', 'asc')->get();
        }
        if (count($data['categories']) > 0) {
            for ($i = 0; $i < count($data['categories']); $i++) {
                $subThreeCats = SubTwoCategory::where('sub_category_id', $data['categories'][$i]['id'])->where('deleted', 0)->where('deleted', 0)->select('id')->first();
                $data['categories'][$i]['next_level'] = false;
                if (isset($subThreeCats['id'])) {
                    $data['categories'][$i]['next_level'] = true;
                }
            }
        }
        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

    public function show_third_cat(Request $request, $sub_cat_id)
    {
        if ($request->lang == 'en') {
            $data['categories'] = SubTwoCategory::where('sub_category_id', $sub_cat_id)->where('deleted', 0)->select('id', 'title_en as title', 'image')->orderBy('sort', 'asc')->get();
        } else {
            $data['categories'] = SubTwoCategory::where('sub_category_id', $sub_cat_id)->where('deleted', 0)->select('id', 'title_ar as title', 'image')->orderBy('sort', 'asc')->get();
        }
        if (count($data['categories']) > 0) {
            for ($i = 0; $i < count($data['categories']); $i++) {
                $subThreeCats = SubThreeCategory::where('sub_category_id', $data['categories'][$i]['id'])->where('deleted', 0)->select('id')->first();
                $data['categories'][$i]['next_level'] = false;
                if (isset($subThreeCats['id'])) {
                    $data['categories'][$i]['next_level'] = true;
                }
            }
        }
        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

    public function show_four_cat(Request $request, $sub_sub_cat_id)
    {
        if ($request->lang == 'en') {
            $data['categories'] = SubThreeCategory::where('sub_category_id', $sub_sub_cat_id)->where('deleted', 0)->select('id', 'title_en as title', 'image')->orderBy('sort', 'asc')->get();
        } else {
            $data['categories'] = SubThreeCategory::where('sub_category_id', $sub_sub_cat_id)->where('deleted', 0)->select('id', 'title_ar as title', 'image')->orderBy('sort', 'asc')->get();
        }
        if (count($data['categories']) > 0) {
            for ($i = 0; $i < count($data['categories']); $i++) {
                $subThreeCats = SubFourCategory::where('sub_category_id', $data['categories'][$i]['id'])->where('deleted', 0)->select('id')->first();
                $data['categories'][$i]['next_level'] = false;
                if (isset($subThreeCats['id'])) {
                    $data['categories'][$i]['next_level'] = true;
                }
            }
        }
        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

    public function show_five_cat(Request $request, $sub_sub_cat_id)
    {
        if ($request->lang == 'en') {
            $data['categories'] = SubFourCategory::where('sub_category_id', $sub_sub_cat_id)->where('deleted', 0)->select('id', 'title_en as title', 'image')->orderBy('sort', 'asc')->get();
        } else {
            $data['categories'] = SubFourCategory::where('sub_category_id', $sub_sub_cat_id)->where('deleted', 0)->select('id', 'title_ar as title', 'image')->orderBy('sort', 'asc')->get();
        }
        if (count($data['categories']) > 0) {
            for ($i = 0; $i < count($data['categories']); $i++) {
                $subThreeCats = SubFiveCategory::where('sub_category_id', $data['categories'][$i]['id'])->where('deleted', '0')->select('id')->first();
                $data['categories'][$i]['next_level'] = false;
                if (isset($subThreeCats['id'])) {
                    $data['categories'][$i]['next_level'] = true;
                }
            }
        }
        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

    public function show_six_cat(Request $request, $sub_sub_cat_id)
    {
        if ($request->lang == 'en') {
            $data['categories'] = SubFiveCategory::where('sub_category_id', $sub_sub_cat_id)->where('deleted', '0')->select('id', 'title_en as title', 'image')->orderBy('sort', 'asc')->get();
        } else {
            $data['categories'] = SubFiveCategory::where('sub_category_id', $sub_sub_cat_id)->where('deleted', '0')->select('id', 'title_ar as title', 'image')->orderBy('sort', 'asc')->get();
        }
        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }


    // get category options
    public function getCategoryOptions(Request $request, Category $category)
    {
        if ($request->lang == 'en') {
            $data['options'] = Category_option::where('cat_id', $category['id'])->where('cat_type', 'category')->where('deleted', '0')->select('id as option_id', 'title_en as title', 'is_required')->get();

            if (count($data['options']) > 0) {
                for ($i = 0; $i < count($data['options']); $i++) {
                    $data['options'][$i]['type'] = 'input';
                    $optionValues = Category_option_value::where('option_id', $data['options'][$i]['option_id'])->where('deleted', '0')->select('id as value_id', 'value_en as value')->get();
                    if (count($optionValues) > 0) {

                        $data['options'][$i]['type'] = 'select';
                        $data['options'][$i]['values'] = $optionValues;
                    }
                }
            }
        } else {
            $data['options'] = Category_option::where('cat_id', $category['id'])->where('cat_type', 'category')->where('deleted', '0')->select('id as option_id', 'title_ar as title', 'is_required')->get();
            if (count($data['options']) > 0) {
                for ($i = 0; $i < count($data['options']); $i++) {
                    $data['options'][$i]['type'] = 'input';
                    $optionValues = Category_option_value::where('option_id', $data['options'][$i]['option_id'])->where('deleted', '0')->select('id as value_id', 'value_ar as value')->get();
                    if (count($optionValues) > 0) {
                        $data['options'][$i]['type'] = 'select';
                        $data['options'][$i]['values'] = $optionValues;
                    }
                }
            }
        }
        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

    // get sub category options
    public function getSubCategoryOptions(Request $request, Category $category, SubCategory $sub_category)
    {
        if ($request->lang == 'en') {
            $data['options'] = Category_option::where('cat_id', $sub_category['id'])->where('cat_type', 'subcategory')->where('deleted', '0')->select('id as option_id', 'title_en as title', 'is_required')->get();
            if (count($data['options']) > 0) {
                for ($i = 0; $i < count($data['options']); $i++) {
                    $data['options'][$i]['type'] = 'input';
                    $optionValues = Category_option_value::where('option_id', $data['options'][$i]['option_id'])->where('deleted', '0')->select('id as value_id', 'value_en as value')->get();
                    if (count($optionValues) > 0) {

                        $data['options'][$i]['type'] = 'select';
                        $data['options'][$i]['values'] = $optionValues;
                    }
                }
            }
        } else {
            $data['options'] = Category_option::where('cat_id', $sub_category['id'])->where('cat_type', 'subcategory')->where('deleted', '0')->select('id as option_id', 'title_ar as title', 'is_required')->get();
            if (count($data['options']) > 0) {
                for ($i = 0; $i < count($data['options']); $i++) {
                    $data['options'][$i]['type'] = 'input';
                    $optionValues = Category_option_value::where('option_id', $data['options'][$i]['option_id'])->where('deleted', '0')->select('id as value_id', 'value_ar as value')->get();
                    if (count($optionValues) > 0) {
                        $data['options'][$i]['type'] = 'select';
                        $data['options'][$i]['values'] = $optionValues;
                    }
                }
            }
        }

        if (count($data['options']) == 0) {
            if ($request->lang == 'en') {
                $data['options'] = Category_option::where('cat_id', $category['id'])->where('cat_type', 'category')->where('deleted', '0')->select('id as option_id', 'title_en as title', 'is_required')->get();

                if (count($data['options']) > 0) {
                    for ($i = 0; $i < count($data['options']); $i++) {
                        $data['options'][$i]['type'] = 'input';
                        $optionValues = Category_option_value::where('option_id', $data['options'][$i]['option_id'])->where('deleted', '0')->select('id as value_id', 'value_en as value')->get();
                        if (count($optionValues) > 0) {

                            $data['options'][$i]['type'] = 'select';
                            $data['options'][$i]['values'] = $optionValues;
                        }
                    }
                }
            } else {
                $data['options'] = Category_option::where('cat_id', $category['id'])->where('cat_type', 'category')->where('deleted', '0')->select('id as option_id', 'title_ar as title', 'is_required')->get();
                if (count($data['options']) > 0) {
                    for ($i = 0; $i < count($data['options']); $i++) {
                        $data['options'][$i]['type'] = 'input';
                        $optionValues = Category_option_value::where('option_id', $data['options'][$i]['option_id'])->where('deleted', '0')->select('id as value_id', 'value_ar as value')->get();
                        if (count($optionValues) > 0) {
                            $data['options'][$i]['type'] = 'select';
                            $data['options'][$i]['values'] = $optionValues;
                        }
                    }
                }
            }
        }
        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

    public function getSubTwoCategoryOptions(Request $request, $category, $sub_category, $sub_two_category)
    {
        $lang = $request->lang;
        $data['options'] = [];
        if ($sub_two_category != 0) {
            $data['options'] = Category_option::where('cat_id', $sub_two_category)->where('cat_type', 'subTwoCategory')->where('deleted', '0')->select('id as option_id', 'title_' . $lang . ' as title', 'is_required')->get();
            if (count($data['options']) > 0) {
                for ($i = 0; $i < count($data['options']); $i++) {
                    $data['options'][$i]['type'] = 'input';
                    $optionValues = Category_option_value::where('option_id', $data['options'][$i]['option_id'])->where('deleted', '0')->select('id as value_id', 'value_' . $lang . ' as value')->get();
                    if (count($optionValues) > 0) {
                        $data['options'][$i]['type'] = 'select';
                        $data['options'][$i]['values'] = $optionValues;
                    }
                }
            }
        }

        if ($sub_category != 0) {
            if (count($data['options']) == 0) {
                $data['options'] = Category_option::where('cat_id', $sub_category)->where('cat_type', 'subcategory')->where('deleted', '0')->select('id as option_id', 'title_' . $lang . ' as title', 'is_required')->get();
                if (count($data['options']) > 0) {
                    for ($i = 0; $i < count($data['options']); $i++) {
                        $data['options'][$i]['type'] = 'input';
                        $optionValues = Category_option_value::where('option_id', $data['options'][$i]['option_id'])->where('deleted', '0')->select('id as value_id', 'value_' . $lang . ' as value')->get();
                        if (count($optionValues) > 0) {

                            $data['options'][$i]['type'] = 'select';
                            $data['options'][$i]['values'] = $optionValues;
                        }
                    }
                }
            }
        }


        if ($category != 0) {
            if (count($data['options']) == 0) {
                $data['options'] = Category_option::where('cat_id', $category)->where('cat_type', 'category')->where('deleted', '0')->select('id as option_id', 'title_' . $lang . ' as title', 'is_required')->get();
                if (count($data['options']) > 0) {
                    for ($i = 0; $i < count($data['options']); $i++) {
                        $data['options'][$i]['type'] = 'input';
                        $optionValues = Category_option_value::where('option_id', $data['options'][$i]['option_id'])->where('deleted', '0')->select('id as value_id', 'value_' . $lang . ' as value')->get();
                        if (count($optionValues) > 0) {
                            $data['options'][$i]['type'] = 'select';
                            $data['options'][$i]['values'] = $optionValues;
                        }
                    }
                }
            }
        }
        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }


}
