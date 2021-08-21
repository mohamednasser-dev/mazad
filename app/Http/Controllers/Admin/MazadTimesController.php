<?php

namespace App\Http\Controllers\Admin;

use App\Balance_package;
use App\Mazad_time;
use Illuminate\Http\Request;

class MazadTimesController extends AdminController
{

    public function index()
    {
        $data = Mazad_time::where('deleted','0')->orderBy('id','desc')->get();
        return view('admin.mazad_times.index',compact('data'));
    }
    public function create()
    {
        return view('admin.mazad_times.create');
    }
    public function store(Request $request)
    {
        $data = $this->validate(\request(),
            [
                'day_num' => 'required'
            ]);
        Mazad_time::create($data);
        session()->flash('success', trans('messages.added_s'));
        return redirect( route('mazad_times.index'));
    }

    public function edit($id)
    {
        $data = Mazad_time::where('id',$id)->first();
        return view('admin.mazad_times.edit',compact('data'));
    }

    public function update(Request $request, $id)
    {
        $data = $this->validate(\request(),
            [
                'day_num' => 'required'
            ]);
        Mazad_time::where('id',$id)->update($data);
        session()->flash('success', trans('messages.updated_s'));
        return redirect( route('mazad_times.index'));
    }

    public function destroy($id)
    {
        $data['deleted'] = '1';
        Mazad_time::where('id',$id)->update($data);
        session()->flash('success', trans('messages.deleted_s'));
        return back();
    }

}
