<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\City;
use App\Area;

class CityController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = City::where('deleted','0')->OrderBy('id','desc')->get();
        return view('admin.cities.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.cities.create');
    }


    public function store(Request $request)
    {
        $data = $this->validate(\request(),
        [
            'title_ar' => 'required',
            'title_en' => 'required'
        ]);
        City::create($data);
        session()->flash('success', trans('messages.added_s'));
        return redirect( route('cities.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $city_id = $id;
        $data = Area::where('city_id',$id)->where('deleted','0')->OrderBy('id','desc')->get();
        return view('admin.cities.areas.index', compact('data','city_id'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        $data = City::findOrFail($id)->first();
        return view('admin.cities.edit', compact('data'));
    }
    public function update(Request $request, $id) {
        $data = $this->validate(\request(),
            [
                'title_ar' => 'required',
                'title_en' => 'required'
            ]);
        City::findOrFail($id)->update($data);
        session()->flash('success', trans('messages.updated_s'));
        return redirect( route('cities.index'));
    }

    public function destroy($id)
    {
        $data['deleted'] = '1';
        City::findOrFail($id)->update($data);
        session()->flash('success', trans('messages.deleted_s'));
        return back();
    }


    // area functions ........................................................
    public function create_area($id)
    {
        return view('admin.cities.areas.create',compact('id'));
    }

    public function store_area(Request $request)
    {
        $data = $this->validate(\request(),
        [
            'title_ar' => 'required',
            'title_en' => 'required',
            'city_id' => 'required'
        ]);
        Area::create($data);
        session()->flash('success', trans('messages.added_s'));
        return redirect( route('cities.show',$request->city_id));
    }
    public function edit_area($id) {
        $data = Area::findOrFail($id)->first();
        return view('admin.cities.areas.edit', compact('data'));
    }
    public function update_area(Request $request, $id) {
        $data = $this->validate(\request(),
            [
                'title_ar' => 'required',
                'title_en' => 'required'
            ]);
        Area::findOrFail($id)->update($data);
        $area = Area::where('id',$id)->first();
        session()->flash('success', trans('messages.updated_s'));
        return redirect( route('cities.show',$area->city_id));
    }
    public function destroy_area($id)
    {
        $data['deleted'] = '1';
        Area::findOrFail($id)->update($data);
        session()->flash('success', trans('messages.deleted_s'));
        return back();
    }
}
