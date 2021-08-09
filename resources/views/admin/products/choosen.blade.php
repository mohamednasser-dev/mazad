@extends('admin.app')

@section('title' , __('messages.choose_to_you'))

@section('content')
    <div id="tableSimple" class="col-lg-12 col-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-header">
            <div class="row">

                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                    <h4>{{ __('messages.choose_to_you') }} {{ isset($data['user']) ? '( ' . $data['user'] . ' )' : '' }} {{ isset($data['category']) ? '( ' . $data['category'] . ' )' : '' }}</h4>
                </div>
            </div>
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
                            <th class="text-center">{{ __('messages.choose_to_you') }}</th>
                            <th class="text-center">{{ __('messages.details') }}</th>
{{--                            @if(Auth::user()->update_data)--}}
{{--                                <th class="text-center">{{ __('messages.edit') }}</th>--}}
{{--                            @endif--}}
                            @if(Auth::user()->delete_data)
                                <th class="text-center" >{{ __('messages.delete') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                            @foreach ($data['products'] as $product)
                                <tr >
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
                                        <a class="btn btn-secondary  mb-2 mr-2 rounded-circle" title="" data-original-title="Tooltip using BUTTON tag">
                                            <i class="far fa-heart"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a class="btn btn-warning  mb-2 mr-2 rounded-circle" title="" data-original-title="Tooltip using BUTTON tag">
                                            <i class="far fa-smile"></i>
                                        </a>
                                    </td>
                                    <td class="text-center blue-color"><a href="{{ route('products.details', $product->id) }}" ><i class="far fa-eye"></i></a></td>
{{--                                    @if(Auth::user()->update_data)--}}
{{--                                        <td class="text-center blue-color" ><a href="{{ route('products.edit', $product->id) }}" ><i class="far fa-edit"></i></a></td>--}}
{{--                                    @endif--}}
                                    @if(Auth::user()->delete_data)
                                        <td class="text-center blue-color" ><a onclick="return confirm('{{ __('messages.are_you_sure') }}');" href="{{ route('delete.product', $product->id) }}" ><i class="far fa-trash-alt"></i></a></td>
                                    @endif
                                    <?php $i++; ?>
                                </tr>
                            @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- <div class="paginating-container pagination-solid">
            <ul class="pagination">
                <li class="prev"><a href="{{$data['products']->previousPageUrl()}}">Prev</a></li>
                @for($i = 1 ; $i <= $data['products']->lastPage(); $i++ )
                    <li class="{{ $data['products']->currentPage() == $i ? "active" : '' }}"><a href="/admin-panel/users/show?page={{$i}}">{{$i}}</a></li>
                @endfor
                <li class="next"><a href="{{$data['products']->nextPageUrl()}}">Next</a></li>
            </ul>
        </div>   --}}
    </div>
@endsection

