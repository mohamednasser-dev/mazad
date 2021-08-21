@extends('admin.app')

@section('title' , __('messages.add_plan'))

@section('content')
    <div class="col-lg-12 col-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-header">
                <div class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                        <h4>{{ __('messages.add_plan') }}</h4>
                    </div>
                </div>
            </div>
            <form action="{{route('mazad_times.store')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-group mb-4">
                    <label for="title_ar">{{ __('messages.day_num') }}</label>
                    <input required type="number" min="1" name="day_num" class="form-control" id="day_num">
                </div>
                <input type="submit" value="{{ __('messages.add') }}" class="btn btn-primary">
            </form>
        </div>
    </div>
@endsection
