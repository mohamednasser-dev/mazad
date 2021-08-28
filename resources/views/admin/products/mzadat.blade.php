@extends('admin.app')

@if(Route::current()->getName() == 'products.choose_to_you')
    @section('title' , __('messages.choose_to_you'))
@else
    @section('title' , __('messages.mazads'))
@endif



@section('content')
    <div id="tableSimple" class="col-lg-12 col-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-header">
                <div class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                        @if(Route::current()->getName() == 'products.choose_to_you')
                            <h4>{{ __('messages.choose_to_you') }}</h4>
                        @else
                            <h4>{{ __('messages.mazads') }}</h4>
                        @endif
                    </div>
                </div>
            </div>
            <div class="widget-content widget-content-area">
                <div class="table-responsive">
                    <table id="html5-extension" class="table table-hover non-hover" style="width:100%">
                        <thead>
                        <tr>
                            <th class="text-center">Id</th>
                            <th class="text-center">{{ __('messages.price_mazad') }}</th>
                            <th class="text-center">{{ __('messages.user') }}</th>
                            <th class="text-center">{{ __('messages.status') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 1; ?>
                        @foreach ($data as $row)
                            <tr>
                                <td class="text-center"><?=$i;?></td>
                                <td class="text-center">
                                    {{$row->price}}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('users.details', $row->user->id) }}" target="_blank">
                                        {{ $row->user->name }}
                                    </a>
                                </td>
                                <td class="text-center">{{ $row->status == 'new' ? __('messages.new') : __('messages.winner') }}</td>
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
    </div>
@endsection

