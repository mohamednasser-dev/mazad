<?php

namespace App\Http\Controllers;

use App\Forum;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Helpers\APIHelpers;
use App\Forum_category;
use Illuminate\Support\Facades\Session;


class ForumController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api' , ['except' => [ 'all_forum']]);
    }

    public function all_forum(Request $request){
        $lang = $request->lang ;
        Session::put('lang_api', $request->lang);
        $data['categories'] = Forum_category::select('id' , 'title_'.$lang.' as title' )
                                ->where('deleted','0')
                                ->where('type','category')
                                ->orderBy('id','desc')
                                ->get();
        $cat = Forum_category::select('id' , 'title_'.$lang.' as title' )
                            ->where('deleted','0')
                            ->where('type','category')
                            ->orderBy('id','desc')
                            ->first();
        $data['forum'] = Forum::select('id', 'image', 'title_'.$lang.' as title','desc_'.$lang.' as description','city_id','created_at')
            ->with('City_data')
            ->where('cat_id', $cat->id)
            ->where('deleted', '0')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get()->makeHidden(['city_id']);

        $response = APIHelpers::createApiResponse(false , 200 ,  '', '' , $data, $request->lang );
        return response()->json($response , 200);
    }
    public function forum_by_cat(Request $request,$cat_id){
        $lang = $request->lang ;
        Session::put('lang_api', $request->lang);
        $data = Forum::select('id', 'image', 'title_'.$lang.' as title','desc_'.$lang.' as description','city_id','created_at')
            ->with('City_data')
            ->where('cat_id', $cat_id)
            ->where('deleted', '0')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get()->makeHidden(['city_id']);

        $response = APIHelpers::createApiResponse(false , 200 ,   '', '' , $data, $request->lang );
        return response()->json($response , 200);
    }
    public function Forum_details(Request $request,$forum_id){
        $lang = $request->lang ;
        Session::put('lang_api', $request->lang);
        $data['forum_data'] = Forum::select('id', 'image', 'title_'.$lang.' as title','desc_'.$lang.' as description','city_id','cat_id','created_at')
            ->with('City_data')
            ->with('Category_data')
            ->where('id', $forum_id)
            ->first()->makeHidden(['city_id','cat_id']);

        $data['related_forum'] = Forum::select('id', 'image', 'title_'.$lang.' as title','desc_'.$lang.' as description','city_id','cat_id','created_at')
            ->with('City_data')
            ->with('Category_data')
            ->where('cat_id', $data['forum_data']->cat_id)
            ->where('deleted', '0')
            ->orderBy('id', 'desc')
            ->limit(3)
            ->get()->makeHidden(['city_id','cat_id']);

        $response = APIHelpers::createApiResponse(false , 200 ,  '', '' , $data, $request->lang );
        return response()->json($response , 200);
    }


}
