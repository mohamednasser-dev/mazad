@extends('admin.app')

@section('title' , __('messages.edit'))
@section('content')
    <div class="col-lg-12 col-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-header">
                <div class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                        <h4>{{ __('messages.edit') }}</h4>
                 </div>
            </div>
        </div>
        <form action="{{route('cities.update',$data->id)}}" method="post" enctype="multipart/form-data" >
            @csrf
            <div class="form-group mb-4">
                <label for="plan_price">{{ __('messages.name_ar') }}</label>
                <input required type="text" name="title_ar" value="{{$data->title_ar}}" class="form-control" >
            </div>
            <div class="form-group mb-4">
                <label for="plan_price">{{ __('messages.name_en') }}</label>
                <input required type="text" name="title_en" value="{{$data->title_en}}" class="form-control" >
            </div>
            <input type="submit" value="{{ __('messages.edit') }}" class="btn btn-primary">
        </form>
    </div>
@endsection
