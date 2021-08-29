@extends('admin.app')

@section('title' , __('messages.category_edit'))

@section('content')
    <div class="col-lg-12 col-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-header">
                <div class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">

                        <h4>{{ __('messages.category_edit') }}</h4>
                    </div>
                </div>
                <form action="{{route('forums.update_new',$data->id)}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group mb-4">
                        <label for="">{{ __('messages.current_image') }}</label><br>
                        <img src="{{image_cloudinary_url()}}{{ $data->image }}"/>
                    </div>
                    <div class="custom-file-container" data-upload-id="myFirstImage">
                        <label>{{ __('messages.upload') }} ({{ __('messages.single_image') }})
                            <a href="javascript:void(0)" class="custom-file-container__image-clear" title="Clear Image">x</a></label>
                        <label class="custom-file-container__custom-file">
                            <input type="file"  name="image"
                                   class="custom-file-container__custom-file__custom-file-input" accept="image/*">
                            <input type="hidden" name="MAX_FILE_SIZE" value="10485760"/>
                            <span class="custom-file-container__custom-file__custom-file-control"></span>
                        </label>
                        <div class="custom-file-container__image-preview"></div>
                    </div>
                    <div class="form-group mb-4">
                        <label for="title_ar">{{ __('messages.title_ar') }}</label>
                        <input required type="text" value="{{$data->title_ar}}" name="title_ar" class="form-control"
                               id="name_ar">
                    </div>
                    <div class="form-group mb-4">
                        <label for="title_ar">{{ __('messages.title_en') }}</label>
                        <input required type="text" value="{{$data->title_en}}" name="title_en" class="form-control"
                               id="name_en">
                    </div>
                    <div class="form-group mb-4">
                        <label for="title_ar">{{ __('messages.desc_ar') }}</label>
                        <input required type="text" value="{{$data->desc_ar}}" name="desc_ar" class="form-control"
                               id="desc_ar">
                    </div>
                    <div class="form-group mb-4">
                        <label for="title_ar">{{ __('messages.desc_en') }}</label>
                        <input required type="text" value="{{$data->desc_en}}" name="desc_en" class="form-control"
                               id="desc_en">
                    </div>
                    <h4>{{ __('messages.category') }}</h4>
                    <div class="form-group">
                        <select required class="form-control" name="cat_id" id="cmb_cat_id">
                            @foreach($categories as $row)
                                @if($row->id == $data->cat_id)
                                    <option value="{{$row->id}}"
                                            selected> {{ app()->getLocale() == 'en' ? $row->title_en : $row->title_ar }}</option>
                                @else
                                    <option
                                        value="{{$row->id}}"> {{ app()->getLocale() == 'en' ? $row->title_en : $row->title_ar }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <h4>{{ __('messages.city') }}</h4>
                    <div class="form-group">
                        <select required class="form-control" name="city_id" id="cmb_city_id">
                            @foreach($cities as $row)
                                @if($row->id == $data->city_id)
                                    <option value="{{$row->id}}"
                                            selected> {{ app()->getLocale() == 'en' ? $row->title_en : $row->title_ar }}</option>
                                @else
                                    <option
                                        value="{{$row->id}}"> {{ app()->getLocale() == 'en' ? $row->title_en : $row->title_ar }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <input type="submit" value="{{ __('messages.edit') }}" class="btn btn-primary">
                </form>
            </div>
@endsection
