<?php

namespace App\Http\Controllers\Admin;

use App\Balance_package;
use App\City;
use App\Forum;
use App\Forum_category;
use Illuminate\Http\Request;
use JD\Cloudder\Facades\Cloudder;

class ForumController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Forum::where('deleted','0')->orderBy('id','desc')->get();
        return view('admin.forums.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Forum_category::where('deleted','0')->orderBy('id','desc')->get();
        $cities = City::where('deleted','0')->orderBy('id','desc')->get();
        return view('admin.forums.create',compact('categories','cities'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $this->validate(\request(),
            [
                'image' => 'required',
                'title_ar' => 'required',
                'title_en' => 'required',
                'desc_ar' => 'required',
                'desc_en' => 'required',
                'city_id' => 'required',
                'cat_id' => 'required'
            ]);

            if($request->file('image')){
                $offer_name = $request->file('image')->getRealPath();
                Cloudder::upload($offer_name, null);
                $imagereturned = Cloudder::getResult();
                $image_id = $imagereturned['public_id'];
                $image_format = $imagereturned['format'];
                $image_new_name = $image_id.'.'.$image_format;
                $data['image'] = $image_new_name;
            }
        Forum::create($data);
        session()->flash('success', trans('messages.added_s'));
        return redirect( route('forums.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = Forum::where('id',$id)->first();
        $categories = Forum_category::where('deleted','0')->orderBy('id','desc')->get();
        $cities = City::where('deleted','0')->orderBy('id','desc')->get();
        return view('admin.forums.edit',compact('data','categories','cities'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $this->validate(\request(),
            [
                'image' => '',
                'title_ar' => 'required',
                'title_en' => 'required',
                'desc_ar' => 'required',
                'desc_en' => 'required',
                'city_id' => 'required',
                'cat_id' => 'required'
            ]);

        if($request->file('image')){
            $offer_name = $request->file('image')->getRealPath();
            Cloudder::upload($offer_name, null);
            $imagereturned = Cloudder::getResult();
            $image_id = $imagereturned['public_id'];
            $image_format = $imagereturned['format'];
            $image_new_name = $image_id.'.'.$image_format;
            $data['image'] = $image_new_name;
        }
        Forum::where('id',$id)->update($data);
        session()->flash('success', trans('messages.updated_s'));
        return redirect( route('forums.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Forum::where('id',$id)->update(['deleted'=>'1']);
        session()->flash('success', trans('messages.deleted_s'));
        return back();
    }
}
