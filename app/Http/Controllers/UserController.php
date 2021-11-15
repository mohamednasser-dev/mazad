<?php

namespace App\Http\Controllers;

use App\Product_mazad;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use JD\Cloudder\Facades\Cloudder;
use Illuminate\Http\Request;
use App\Helpers\APIHelpers;
use App\WalletTransaction;
use App\UserNotification;
use App\Balance_package;
use App\Notification;
use App\ProductImage;
use App\Category;
use App\Favorite;
use App\Setting;
use App\Product;
use App\User;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['my_bids', 'pay_sucess', 'pay_error', 'excute_pay', 'my_account', 'my_balance', 'resetforgettenpassword', 'checkphoneexistance', 'checkphoneexistanceandroid', 'getownerprofile']]);
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
            }
        }
    }

    public function getprofile(Request $request)
    {
        $user = auth()->user();
        $returned_user['user_name'] = $user['name'];
        $returned_user['name'] = $user['name'];
        $returned_user['phone'] = $user['phone'];
        $returned_user['email'] = $user['email'];
        $returned_user['image'] = $user['image'];
        $response = APIHelpers::createApiResponse(false, 200, '', '', $returned_user, $request->lang);
        return response()->json($response, 200);
    }

    public function checkphoneexistanceandroid(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true, 406, 'Missing Required Fields', 'حقل الهاتف اجباري', (object)[], $request->lang);
            return response()->json($response, 406);
        }

        $user = User::where('phone', $request->phone)->first();
        if ($user) {

            if ($request->email) {
                $user_email = User::where('email', $request->email)->first();
                if ($user_email) {
                    $response = APIHelpers::createApiResponse(false, 200, '', '', (object)[], $request->lang);
                    $response['phone'] = true;
                    $response['email'] = true;
                    return response()->json($response, 200);
                } else {
                    $response = APIHelpers::createApiResponse(false, 200, '', '', (object)[], $request->lang);
                    $response['phone'] = true;
                    $response['email'] = false;
                    return response()->json($response, 200);
                }

            }
            $response = APIHelpers::createApiResponse(false, 200, '', '', (object)[], $request->lang);
            return response()->json($response, 200);
        }
        if ($request->email) {
            $user_email = User::where('email', $request->email)->first();
            if ($user_email) {
                $response = APIHelpers::createApiResponse(false, 200, '', '', (object)[], $request->lang);
                $response['phone'] = false;
                $response['email'] = true;
                return response()->json($response, 200);
            }

        }

        $response = APIHelpers::createApiResponse(false, 200, 'Phone and Email Not Exists Before', 'الهاتف و البريد غير موجودين من قبل', (object)[], $request->lang);
        $response['phone'] = false;
        $response['email'] = false;

        return response()->json($response, 200);

    }

    public function updateprofile(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            "email" => 'required',
            "image" => '',
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true, 406, $validator->errors()->first(), $validator->errors()->first(), null, $request->lang);
            return response()->json($response, 406);
        }

        $currentuser = auth()->user();
        $user_by_phone = User::where('phone', '!=', $currentuser->phone)->where('phone', $request->phone)->first();
        if ($user_by_phone) {
            $response = APIHelpers::createApiResponse(true, 409, 'رقم الهاتف موجود من قبل', '', null, $request->lang);
            return response()->json($response, 409);
        }

        $user_by_email = User::where('email', '!=', $currentuser->email)->where('email', $request->email)->first();
        if ($user_by_email) {
            $response = APIHelpers::createApiResponse(true, 409, 'البريد الإلكتروني موجود من قبل', '', null, $request->lang);
            return response()->json($response, 409);
        }
        if ($request->image != null) {
            $image = $request->image;
            Cloudder::upload("data:image/jpeg;base64," . $image, null);
            $imagereturned = Cloudder::getResult();
            $image_id = $imagereturned['public_id'];
            $image_format = $imagereturned['format'];
            $image_new_name = $image_id . '.' . $image_format;
            $input['image'] = $image_new_name;
        } else {
            unset($input['image']);
        }
        User::where('id', $currentuser->id)->update($input);

        $newuser = User::find($currentuser->id);
        $response = APIHelpers::createApiResponse(false, 200, '', '', $newuser, $request->lang);
        return response()->json($response, 200);
    }


    public function resetpassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            "old_password" => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true, 406, 'بعض الحقول مفقودة', '', null, $request->lang);
            return response()->json($response, 406);
        }

        $user = auth()->user();
        if (!Hash::check($request->old_password, $user->password)) {
            $response = APIHelpers::createApiResponse(true, 406, 'كلمه المرور السابقه خطأ', '', null, $request->lang);
            return response()->json($response, 406);
        }
        if ($request->old_password == $request->password) {
            $response = APIHelpers::createApiResponse(true, 406, 'لا يمكنك تعيين نفس كلمه المرور السابقه', '', null, $request->lang);
            return response()->json($response, 406);
        }
        User::where('id', $user->id)->update(['password' => Hash::make($request->password)]);
        $newuser = User::find($user->id);
        $response = APIHelpers::createApiResponse(false, 200, '', '', $newuser, $request->lang);
        return response()->json($response, 200);
    }

    public function resetforgettenpassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'phone' => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true, 406, 'بعض الحقول مفقودة', '', null, $request->lang);
            return response()->json($response, 406);
        }

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            $response = APIHelpers::createApiResponse(true, 403, 'رقم الهاتف غير موجود', '', null, $request->lang);
            return response()->json($response, 403);
        }

        User::where('phone', $user->phone)->update(['password' => Hash::make($request->password)]);
        $newuser = User::where('phone', $user->phone)->first();

        $token = auth()->login($newuser);
        $newuser->token = $this->respondWithToken($token);

        $response = APIHelpers::createApiResponse(false, 200, '', '', $newuser, $request->lang);
        return response()->json($response, 200);
    }

    // check if phone exists before or not
    public function checkphoneexistance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true, 406, 'حقل الهاتف اجباري', '', null, $request->lang);
            return response()->json($response, 406);
        }

        $user = User::where('phone', $request->phone)->first();
        if ($user) {
            $response = APIHelpers::createApiResponse(false, 200, '', '', $user, $request->lang);
            return response()->json($response, 200);
        }

        $response = APIHelpers::createApiResponse(true, 403, 'الهاتف غير موجود من قبل', '', null, $request->lang);
        return response()->json($response, 403);

    }


    // get notifications
    public function notifications(Request $request)
    {
        $user = auth()->user();
        if ($user->active == 0) {
            $response = APIHelpers::createApiResponse(true, 406, 'تم حظر حسابك من الادمن', '', null, $request->lang);
            return response()->json($response, 406);
        }

        $user_id = $user->id;
        $notifications_ids = UserNotification::where('user_id', $user_id)->orderBy('id', 'desc')->select('notification_id')->get();
        $notifications = [];
        for ($i = 0; $i < count($notifications_ids); $i++) {
            $notifications[$i] = Notification::select('id', 'title', 'body', 'image', 'created_at')->find($notifications_ids[$i]['notification_id']);
        }
        $data['notifications'] = $notifications;
        $response = APIHelpers::createApiResponse(false, 200, '', '', $data['notifications'], $request->lang);
        return response()->json($response, 200);
    }

    // get ads count
    public function getadscount(Request $request)
    {
        $user = auth()->user();
        $returned_user['free_ads_count'] = $user->free_ads_count;
        $returned_user['paid_ads_count'] = $user->paid_ads_count;
        $response = APIHelpers::createApiResponse(false, 200, '', '', $returned_user, $request->lang);
        return response()->json($response, 200);
    }

    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 432000
        ];
    }

    // get current ads
    public function getcurrentads(Request $request)
    {
        $user = auth()->user();
        if ($user->active == 0) {
            $response = APIHelpers::createApiResponse(true, 406, 'تم حظر حسابك من الادمن', '', null, $request->lang);
            return response()->json($response, 406);
        }

        $user = auth()->user();

        $products = Product::where('user_id', $user->id)->where('status', 1)->orderBy('publication_date', 'DESC')->select('id', 'title', 'price', 'publication_date as date', 'type')->simplePaginate(12);
        for ($i = 0; $i < count($products); $i++) {
            $products[$i]['price'] = number_format((float)($products[$i]['price']), 3);
            $products[$i]['image'] = ProductImage::where('product_id', $products[$i]['id'])->select('image')->first()['image'];
            $favorite = Favorite::where('user_id', $user->id)->where('type', 'product')->where('product_id', $products[$i]['id'])->first();
            if ($favorite) {
                $products[$i]['favorite'] = true;
            } else {
                $products[$i]['favorite'] = false;
            }
            $date = date_create($products[$i]['date']);
            $products[$i]['date'] = date_format($date, 'd M Y');
        }
        $response = APIHelpers::createApiResponse(false, 200, '', '', $products, $request->lang);
        return response()->json($response, 200);
    }

    // get history date
    public function getexpiredads(Request $request)
    {
        $user = auth()->user();
        if ($user->active == 0) {
            $response = APIHelpers::createApiResponse(true, 406, 'تم حظر حسابك من الادمن', '', null, $request->lang);
            return response()->json($response, 406);
        }

        $user = auth()->user();

        $products = Product::where('user_id', $user->id)->where('status', 2)->orderBy('publication_date', 'DESC')->select('id', 'title', 'price', 'publication_date as date', 'type')->simplePaginate(12);
        for ($i = 0; $i < count($products); $i++) {
            $products[$i]['price'] = number_format((float)($products[$i]['price']), 3);
            $products[$i]['image'] = ProductImage::where('product_id', $products[$i]['id'])->select('image')->first()['image'];
            $favorite = Favorite::where('user_id', $user->id)->where('type', 'product')->where('product_id', $products[$i]['id'])->first();
            if ($favorite) {
                $products[$i]['favorite'] = true;
            } else {
                $products[$i]['favorite'] = false;
            }
            $date = date_create($products[$i]['date']);
            $products[$i]['date'] = date_format($date, 'd M Y');
        }
        $response = APIHelpers::createApiResponse(false, 200, '', '', $products, $request->lang);
        return response()->json($response, 200);
    }

    public function renewad(Request $request)
    {
        $user = auth()->user();
        if ($user->active == 0) {
            $response = APIHelpers::createApiResponse(true, 406, 'تم حظر حسابك', '', null, $request->lang);
            return response()->json($response, 406);
        }
        if ($user->free_ads_count == 0 && $user->paid_ads_count == 0) {
            $response = APIHelpers::createApiResponse(true, 406, 'ليس لديك رصيد إعلانات لتجديد الإعلان يرجي شراء باقه إعلانات', '', null, $request->lang);
            return response()->json($response, 406);
        }
        $product = Product::where('id', $request->product_id)->where('user_id', $user->id)->first();
        if ($product->status == 1) {
            $response = APIHelpers::createApiResponse(true, 406, 'هذا الاعلان لم ينتهى بعد', 'this ad not ended yet', null, $request->lang);
            return response()->json($response, 406);
        }
        if ($product->deleted == 1) {
            $response = APIHelpers::createApiResponse(true, 406, 'هذا الاعلان تم حذفة', 'this ad deleted before', null, $request->lang);
            return response()->json($response, 406);
        }
        if ($product) {
            if ($user->free_ads_count > 0) {
                $count = $user->free_ads_count;
                $user->free_ads_count = $count - 1;
            } else {
                $count = $user->paid_ads_count;
                $user->paid_ads_count = $count - 1;
            }
            $user->save();
            $settings = $settings = Setting::where('id', 1)->first();
            $product->publication_date = date("Y-m-d H:i:s");
            $mytime = Carbon::now();
            $today = Carbon::parse($mytime->toDateTimeString())->format('Y-m-d H:i');
            $final_date = Carbon::createFromFormat('Y-m-d H:i', $today);
            $final_expire_date = $final_date->addDays($settings->ad_period);
            $product->expiry_date = $final_expire_date;
            $product->status = 1;
            $product->publish = 'Y';
            $product->save();
            $response = APIHelpers::createApiResponse(false, 200, '', '', $product, $request->lang);
            return response()->json($response, 200);

        } else {
            $response = APIHelpers::createApiResponse(true, 406, 'ليس لديك الصلاحيه لتجديد هذا الاعلان', '', null, $request->lang);
            return response()->json($response, 406);

        }

    }

    public function deletead(Request $request)
    {
        $user = auth()->user();
        if ($user->active == 0) {
            $response = APIHelpers::createApiResponse(true, 406, 'تم حظر حسابك', '', null, $request->lang);
            return response()->json($response, 406);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true, 406, 'بعض الحقول مفقودة', '', null, $request->lang);
            return response()->json($response, 406);
        }

        $product = Product::where('id', $request->product_id)->where('user_id', $user->id)->first();

        if ($product) {
            $product->delete();
            $response = APIHelpers::createApiResponse(false, 200, '', '', null, $request->lang);
            return response()->json($response, 200);
        } else {
            $response = APIHelpers::createApiResponse(true, 406, 'ليس لديك الصلاحيه لحذف هذا الاعلان', '', null, $request->lang);
            return response()->json($response, 406);
        }

    }

    public function editad(Request $request)
    {
        $user = auth()->user();
        if ($user->active == 0) {
            $response = APIHelpers::createApiResponse(true, 406, 'تم حظر حسابك', '', null, $request->lang);
            return response()->json($response, 406);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true, 406, 'بعض الحقول مفقودة', '', null, $request->lang);
            return response()->json($response, 406);
        }

        $product = Product::where('id', $request->product_id)->where('user_id', $user->id)->first();
        if ($product) {
            if ($request->title) {
                $product->title = $request->title;
            }

            if ($request->description) {
                $product->description = $request->description;
            }

            if ($request->price) {
                $product->price = $request->price;
            }

            if ($request->category_id) {
                $product->category_id = $request->category_id;
            }

            if ($request->type) {
                $product->type = $request->type;
            }

            $product->save();

            if ($request->image) {
                $product_image = ProductImage::where('product_id', $request->product_id)->first();
                $image = $request->image;
                Cloudder::upload("data:image/jpeg;base64," . $image, null);
                $imagereturned = Cloudder::getResult();
                $image_id = $imagereturned['public_id'];
                $image_format = $imagereturned['format'];
                $image_new_name = $image_id . '.' . $image_format;
                $product_image->image = $image_new_name;
                $product_image->save();
            }

            $response = APIHelpers::createApiResponse(false, 200, '', '', $product, $request->lang);
            return response()->json($response, 200);
        } else {
            $response = APIHelpers::createApiResponse(true, 406, 'ليس لديك الصلاحيه لتعديل هذا الاعلان', '', null, $request->lang);
            return response()->json($response, 406);
        }

    }

    public function delteadimage(Request $request)
    {
        $user = auth()->user();
        if ($user->active == 0) {
            $response = APIHelpers::createApiResponse(true, 406, 'تم حظر حسابك', '', null, $request->lang);
            return response()->json($response, 406);
        }

        $validator = Validator::make($request->all(), [
            'image_id' => 'required',
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true, 406, 'بعض الحقول مفقودة', '', null, $request->lang);
            return response()->json($response, 406);
        }

        $image = ProductImage::find($request->image_id);
        if ($image) {
            $image->delete();
            $response = APIHelpers::createApiResponse(false, 200, '', '', null, $request->lang);
            return response()->json($response, 200);

        } else {
            $response = APIHelpers::createApiResponse(true, 406, 'Invalid Image Id', '', null, $request->lang);
            return response()->json($response, 406);
        }

    }

    public function getaddetails(Request $request)
    {
        $ad_id = $request->id;
        $ad = Product::select('id', 'title', 'description', 'price', 'type', 'category_id')->find($ad_id);
        $ad['category_name'] = Category::find($ad['category_id'])['title_ar'];
        $images = ProductImage::where('product_id', $ad_id)->select('id', 'image')->get()->toArray();

        $ad['image'] = array_shift($images)['image'];
        $ad['images'] = $images;
        $response = APIHelpers::createApiResponse(false, 200, '', '', $ad, $request->lang);
        return response()->json($response, 200);
    }

    public function getownerprofile(Request $request)
    {
        $user_id = $request->id;
        $data['user'] = User::select('id', 'name', 'phone', 'email')->find($user_id);
        $products = Product::where('status', 1)->where('user_id', $user_id)->orderBy('publication_date', 'DESC')->select('id', 'title', 'price', 'type', 'publication_date as date')->get();
        for ($i = 0; $i < count($products); $i++) {
            $products[$i]['image'] = ProductImage::where('product_id', $products[$i]['id'])->first()['image'];
            $date = date_create($products[$i]['date']);
            $products[$i]['date'] = date_format($date, 'd M Y');

            $user = auth()->user();
            if ($user) {
                $favorite = Favorite::where('user_id', $user->id)->where('type', 'product')->where('product_id', $products[$i]['id'])->first();
                if ($favorite) {
                    $products[$i]['favorite'] = true;
                } else {
                    $products[$i]['favorite'] = false;
                }
            } else {
                $products[$i]['favorite'] = false;
            }

        }
        $data['products'] = $products;

        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

//nasser code
    public function my_account(Request $request)
    {
        $user = auth()->user();
        $user_data = User::where('id', $user->id)->select('name', 'email', 'image', 'phone', 'free_balance', 'payed_balance', 'created_at')->first();

        $user_data->current_ads = Product::where('status', 1)->where('user_id', $user->id)->where('deleted', 0)->get()->count();
        $user_data->end_ads = Product::where('status', 2)->where('user_id', $user->id)->where('deleted', 0)->get()->count();
        $response = APIHelpers::createApiResponse(false, 200, '', '', $user_data, $request->lang);
        return response()->json($response, 200);
    }

    public function my_bids(Request $request, $type)
    {

        $user = auth()->user();
        if ($user) {
            if ($type == 'current_ads' || $type == 'ended_ads') {
                $user_ids = Product_mazad::with('Product')->where('user_id', $user->id)->wherehas($type)
                    ->orderBy('created_at','desc')->get()->unique('product_id')->pluck('id')->toArray();

                $data = Product_mazad::whereIn('id',$user_ids)->with('Product')->where('user_id', $user->id)->wherehas($type)
                    ->paginate(20);

                $data->map(function ($entire_data) use ($user) {
                    $bid = Product_mazad::select('price')->where('product_id', $entire_data->product_id)
                        ->where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
                    $entire_data->my_last_bid = number_format($bid->price, 3);
                    $entire_data->highest_bid = $entire_data->Product->price;
                    $favorite = Favorite::where('user_id', $user->id)->where('type', 'product')->where('product_id', $entire_data->id)->first();
                    if ($favorite) {
                        $entire_data->favorite = true;
                    } else {
                        $entire_data->favorite = false;
                    }
                    return $entire_data;
                });
            } elseif ($type == 'winner_ads') {
                $data = Product_mazad::with('Product')->where('user_id', $user->id)
                    ->where('status', 'winner')->orderBy('created_at','desc')
                    ->paginate(20);
                $data->map(function ($entire_data) use ($user) {
                    $bid = Product_mazad::select('price')->where('product_id', $entire_data->product_id)->where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
                    $entire_data->my_last_bid = number_format($bid->price, 3);
                    $entire_data->highest_bid = $entire_data->Product->price;
                    $favorite = Favorite::where('user_id', $user->id)->where('type', 'product')->where('product_id', $entire_data->product_id)->first();
                    if ($favorite) {
                        $entire_data->favorite = true;
                    } else {
                        $entire_data->favorite = false;
                    }
                    return $entire_data;
                });
            }
            $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
            return response()->json($response, 200);
        } else {
            $response = APIHelpers::createApiResponse(true, 406, 'you should login first', 'يجب تسجيل الدخول اولا', null, $request->lang);
            return response()->json($response, 406);
        }
    }

    public function payments_date(Request $request)
    {
        $user = auth()->user();
        $lang = $request->lang;

        $data = WalletTransaction::where('user_id', $user->id)
            ->where('type', 'payed')
            ->select('price', 'type', 'user_id', 'package_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($wallet) use ($lang) {
                $package = Balance_package::where('id', $wallet->package_id)->first();
                if ($lang == 'ar') {
                    $wallet->pakage_name = $package->name_ar;
                } else {
                    $wallet->pakage_name = $package->name_en;
                }
                $wallet->day = $wallet->created_at->format('d');
                $wallet->month = $wallet->created_at->format('F');
                if ($lang == 'ar') {
                    if ($wallet->month == 'January') {
                        $wallet->month = 'يناير';
                    } else if ($wallet->month == 'February') {
                        $wallet->month = 'فبراير';
                    } else if ($wallet->month == 'March') {
                        $wallet->month = 'مارس';
                    } else if ($wallet->month == 'April') {
                        $wallet->month = 'ابريل';
                    } else if ($wallet->month == 'May') {
                        $wallet->month = 'مايو';
                    } else if ($wallet->month == 'June') {
                        $wallet->month = 'يونيو';
                    } else if ($wallet->month == 'July') {
                        $wallet->month = 'يوليو';
                    } else if ($wallet->month == 'August') {
                        $wallet->month = 'أغسطي';
                    } else if ($wallet->month == 'September') {
                        $wallet->month = 'سبتمبر';
                    } else if ($wallet->month == 'October') {
                        $wallet->month = 'أكتوبر';
                    } else if ($wallet->month == 'November') {
                        $wallet->month = 'نوفمبر';
                    } else if ($wallet->month == 'December') {
                        $wallet->month = 'ديسمبر';
                    }
                }
                $wallet->date = $wallet->created_at->format('d/m/Y');

                return $wallet;
            });

        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

    public function my_balance(Request $request)
    {
        $data = User::where('id', auth()->user()->id)->select('id', 'free_balance', 'payed_balance')->first();
        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

// add balance to wallet
    public function addBalance(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'package_id' => 'required|exists:balance_packages,id'
        ]);
        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true, 406, $validator->messages()->first(), $validator->messages()->first(), null, $request->lang);
            return response()->json($response, 406);
        }
        $package = Balance_package::find($request->package_id);
        $user = auth()->user();
        $root_url = $request->root();
        $path = 'https://apitest.myfatoorah.com/v2/SendPayment';
        $token = "bearer rLtt6JWvbUHDDhsZnfpAhpYk4dxYDQkbcPTyGaKp2TYqQgG7FGZ5Th_WD53Oq8Ebz6A53njUoo1w3pjU1D4vs_ZMqFiz_j0urb_BH9Oq9VZoKFoJEDAbRZepGcQanImyYrry7Kt6MnMdgfG5jn4HngWoRdKduNNyP4kzcp3mRv7x00ahkm9LAK7ZRieg7k1PDAnBIOG3EyVSJ5kK4WLMvYr7sCwHbHcu4A5WwelxYK0GMJy37bNAarSJDFQsJ2ZvJjvMDmfWwDVFEVe_5tOomfVNt6bOg9mexbGjMrnHBnKnZR1vQbBtQieDlQepzTZMuQrSuKn-t5XZM7V6fCW7oP-uXGX-sMOajeX65JOf6XVpk29DP6ro8WTAflCDANC193yof8-f5_EYY-3hXhJj7RBXmizDpneEQDSaSz5sFk0sV5qPcARJ9zGG73vuGFyenjPPmtDtXtpx35A-BVcOSBYVIWe9kndG3nclfefjKEuZ3m4jL9Gg1h2JBvmXSMYiZtp9MR5I6pvbvylU_PP5xJFSjVTIz7IQSjcVGO41npnwIxRXNRxFOdIUHn0tjQ-7LwvEcTXyPsHXcMD8WtgBh-wxR8aKX7WPSsT1O8d8reb2aR7K3rkV3K82K_0OgawImEpwSvp9MNKynEAJQS6ZHe_J_l77652xwPNxMRTMASk1ZsJL";
        $headers = array(
            'Authorization:' . $token,
            'Content-Type:application/json'
        );
        $call_back_url = $root_url . "/api/wallet/excute_pay?user_id=" . $user->id . "&balance=" . $request->package_id;
        $error_url = $root_url . "/api/pay/error";
//        dd($call_back_url);
        $fields = array(
            "CustomerName" => $user->name,
            "NotificationOption" => "LNK",
            "InvoiceValue" => $package->price,
            "CallBackUrl" => $call_back_url,
            "ErrorUrl" => $error_url,
            "Language" => "AR",
            "CustomerEmail" => $user->email
        );

        $payload = json_encode($fields);
        $curl_session = curl_init();
        curl_setopt($curl_session, CURLOPT_URL, $path);
        curl_setopt($curl_session, CURLOPT_POST, true);
        curl_setopt($curl_session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_session, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_session, CURLOPT_IPRESOLVE, CURLOPT_IPRESOLVE);
        curl_setopt($curl_session, CURLOPT_POSTFIELDS, $payload);
        $result = curl_exec($curl_session);
        curl_close($curl_session);
        $result = json_decode($result);

        $data['url'] = $result->Data->InvoiceURL;
        $response = APIHelpers::createApiResponse(false, 200, '', '', $data, $request->lang);
        return response()->json($response, 200);
    }

    // excute pay
    public function excute_pay(Request $request)
    {
        $package = Balance_package::findOrFail($request->balance);
        if ($package != null) {
            $user = auth()->user();
            $selected_user = User::findOrFail($user->id);
            $selected_user->my_wallet = $selected_user->my_wallet + $package->amount;
            $selected_user->payed_balance = $selected_user->payed_balance + $package->amount;
            $selected_user->save();
            WalletTransaction::create([
                'price' => $package->price,
                'value' => $package->amount,
                'package_id' => $request->balance,
                'user_id' => $request->user_id
            ]);
            return redirect('api/pay/success');
        }
    }

    public function pay_error()
    {
        return "Please wait error ...";
    }

    public function pay_sucess()
    {
        return "Please wait success ...";
    }


}
