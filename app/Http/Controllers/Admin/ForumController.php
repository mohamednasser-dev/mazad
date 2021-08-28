<?php

namespace App\Http\Controllers\Admin;

use App\Balance_package;
use App\City;
use App\Forum;
use App\Forum_category;
use Illuminate\Http\Request;

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
        return view('admin.Forums.index',compact('data'));
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
        return view('admin.forums.edit',compact('data'));
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
                'name_ar' => 'required',
                'name_en' => 'required',
                'desc_ar' => 'required',
                'desc_en' => 'required',
                'price' => 'required',
                'amount' => 'required'
            ]);
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
        Forum::where('id',$id)->delete();
        session()->flash('success', trans('messages.deleted_s'));
        return back();
    }
}
