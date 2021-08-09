@extends('admin.app')

@section('title' , __('messages.our_offers'))

@section('content')
    <div id="tableSimple" class="col-lg-12 col-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-header">
                <div class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                        <h4>{{ __('messages.our_offers') }} {{ isset($data['user']) ? '( ' . $data['user'] . ' )' : '' }} {{ isset($data['category']) ? '( ' . $data['category'] . ' )' : '' }}</h4>
                    </div>
                </div>
{{--                <div class="row">--}}
{{--                    <div class="col-md-6">--}}
{{--                        <h4>{{ __('messages.offer_image') }}</h4>--}}
{{--                        <div class="form-group mb-4">--}}
{{--                            <img style="height: 100px;"--}}
{{--                                 src="{{image_cloudinary_url()}}{{$data['offer_image']}}">--}}
{{--                        </div>--}}
{{--                        <div class="col-xl-12 col-md-12 col-sm-12 col-12">--}}
{{--                            <a class="btn btn-primary" data-toggle="modal"--}}
{{--                               data-target="#offer_image_Modal">{{ __('messages.edit_offer_image') }}</a>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-6">--}}
{{--                        <h4>{{ __('messages.offer_image_en') }}</h4>--}}
{{--                        <div class="form-group mb-4">--}}
{{--                            <img style="height: 100px;"--}}
{{--                                 src="{{image_cloudinary_url()}}{{$data['offer_image_en']}}">--}}
{{--                        </div>--}}
{{--                        <div class="col-xl-12 col-md-12 col-sm-12 col-12">--}}
{{--                            <a class="btn btn-primary" data-toggle="modal"--}}
{{--                               data-target="#offer_image_en_Modal">{{ __('messages.edit_offer_image') }}</a>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
            </div>
            <div class="widget-content widget-content-area">
                <div class="table-responsive">
                    <table id="html5-extension" class="table table-hover non-hover" style="width:100%">
                        <thead>
                        <tr>
                            <th class="text-center">Id</th>
                            <th class="text-center">{{ __('messages.publication_date') }}</th>
                            <th class="text-center">{{ __('messages.product_name') }}</th>

                            <th class="text-center">{{ __('messages.plan_name') }}</th>
                            <th class="text-center">{{ __('messages.user') }}</th>
                            <th class="text-center">{{ __('messages.archived_or_not') }}</th>
                            <th class="text-center">{{ __('messages.our_offers') }}</th>
{{--                            <th class="text-center">{{ __('messages.choose_to_you') }}</th>--}}
                            <th class="text-center">{{ __('messages.details') }}</th>
                            {{--                            @if(Auth::user()->update_data)--}}
                            {{--                                <th class="text-center">{{ __('messages.edit') }}</th>--}}
                            {{--                            @endif--}}
                            @if(Auth::user()->delete_data)
                                <th class="text-center">{{ __('messages.delete') }}</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 1; ?>
                        @foreach ($data['products'] as $product)
                            <tr>
                                <td class="text-center"><?=$i;?></td>
                                <td class="text-center">
                                    @if( $product->publication_date != null)
                                        {{date('Y-m-d', strtotime($product->publication_date))}}
                                    @else
                                        {{ __('messages.not_publish_yet') }}
                                    @endif</td>
                                <td class="text-center">{{ $product->title }}</td>
                                <td class="text-center">
                                    @if($product->plan_id !=null)
                                        @if(app()->getLocale() == 'ar')
                                            {{$product->Plan->title_ar}}
                                        @else
                                            {{$product->Plan->title_en}}
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center">{{ $product->status == 1 ? __('messages.published') : __('messages.archived') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('users.details', $product->user->id) }}" target="_blank">
                                        {{ $product->user->name }}
                                    </a>
                                </td>
                                <td class="text-center blue-color">
                                    @if($product->offer == 1 )
                                        <a href="{{route('products.make_offer',$product->id)}}"
                                           class="btn btn-danger  mb-2 mr-2 rounded-circle" title=""
                                           data-original-title="Tooltip using BUTTON tag">
                                            <i class="far fa-heart"></i>
                                        </a>
                                    @else
                                        <a href="{{route('products.make_offer',$product->id)}}"
                                           class="btn btn-dark  mb-2 mr-2 rounded-circle" title=""
                                           data-original-title="Tooltip using BUTTON tag">
                                            <i class="far fa-heart"></i>
                                        </a>
                                    @endif
                                </td>
{{--                                <td class="text-center">--}}
{{--                                    @if($product->choose_it == 1 )--}}
{{--                                        <a href="{{route('products.make_choose',$product->id)}}"--}}
{{--                                           class="btn btn-danger  mb-2 mr-2 rounded-circle" title=""--}}
{{--                                           data-original-title="Tooltip using BUTTON tag">--}}
{{--                                            <i class="far fa-gem"></i>--}}
{{--                                        </a>--}}
{{--                                    @else--}}
{{--                                        <a href="{{route('products.make_choose',$product->id)}}"--}}
{{--                                           class="btn btn-warning  mb-2 mr-2 rounded-circle" title=""--}}
{{--                                           data-original-title="Tooltip using BUTTON tag">--}}
{{--                                            <i class="far fa-gem"></i>--}}
{{--                                        </a>--}}
{{--                                    @endif--}}
{{--                                </td>--}}
                                <td class="text-center blue-color"><a
                                        href="{{ route('products.details', $product->id) }}"><i class="far fa-eye"></i></a>
                                </td>
                                {{--                                    @if(Auth::user()->update_data)--}}
                                {{--                                        <td class="text-center blue-color" ><a href="{{ route('products.edit', $product->id) }}" ><i class="far fa-edit"></i></a></td>--}}
                                {{--                                    @endif--}}
                                @if(Auth::user()->delete_data)
                                    <td class="text-center blue-color"><a
                                            onclick="return confirm('{{ __('messages.are_you_sure') }}');"
                                            href="{{ route('delete.product', $product->id) }}"><i
                                                class="far fa-trash-alt"></i></a></td>
                                @endif
                                <?php $i++; ?>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- update arbic offer paner--}}
        <div id="offer_image_Modal" class="modal animated zoomInUp custo-zoomInUp" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('messages.edit_offer_image') }}{{ __('messages.with_arabic') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <form action="{{route('update.offer.baner')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="custom-file-container" data-upload-id="myFirstImage">
                                <label>{{ __('messages.upload') }} ({{ __('messages.single_image') }}) <a
                                        href="javascript:void(0)" class="custom-file-container__image-clear"
                                        title="Clear Image">x</a></label>
                                <label class="custom-file-container__custom-file">
                                    <input type="file" required name="image"
                                           class="custom-file-container__custom-file__custom-file-input"
                                           accept="image/*">
                                    <input type="hidden" name="MAX_FILE_SIZE" value="10485760"/>
                                    <span class="custom-file-container__custom-file__custom-file-control"></span>
                                </label>
                                <div class="custom-file-container__image-preview"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="submit" value="{{ __('messages.edit') }}" class="btn btn-primary">
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- update english offer paner--}}
        <div id="offer_image_en_Modal" class="modal animated zoomInUp custo-zoomInUp" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('messages.edit_offer_image') }}{{ __('messages.with_english') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <form action="{{route('update.offer.baner_english')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="custom-file-container" data-upload-id="myFirstImage">
                                <label>{{ __('messages.upload') }} ({{ __('messages.multiple_images') }}) <a
                                        href="javascript:void(0)" class="custom-file-container__image-clear"
                                        title="Clear Image">x</a></label>
                                <label class="custom-file-container__custom-file">
                                    <input type="file" required name="image"
                                           class="custom-file-container__custom-file__custom-file-input" accept="image/*">
                                    <input type="hidden" name="MAX_FILE_SIZE" value="10485760"/>
                                    <span class="custom-file-container__custom-file__custom-file-control"></span>
                                </label>
                                <div class="custom-file-container__image-preview"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="submit" value="{{ __('messages.edit') }}" class="btn btn-primary">
                        </div>
                    </form>
                </div>
            </div>
        </div>
@endsection

