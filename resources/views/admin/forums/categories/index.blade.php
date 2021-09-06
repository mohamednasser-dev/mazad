@extends('admin.app')
@section('title' , __('messages.forum_categories'))
@section('scripts')
    <script src="https://code.jquery.com/ui/1.10.4/jquery-ui.js" type="text/javascript"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $("tbody#sortable").sortable({
            items: "tr",
            placeholder: "ui-state-hightlight",
            update: function () {
                var ids = $('tbody#sortable').sortable("serialize");
                var url = "{{ route('category.sort') }}";
                $.post(url, ids + "&_token={{ csrf_token() }}");
            }
        });
    </script>
@endsection
@section('content')
    <div id="tableSimple" class="col-lg-12 col-12 layout-spacing">

        <div class="statbox widget box box-shadow">
            <div class="widget-header">
                <div class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                        <h4>{{ __('messages.forum_categories') }}</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                        <a class="btn btn-primary"
                           href="{{route('forum_categories.create')}}">{{ __('messages.add') }}</a>
                    </div>
                </div>
            </div>
            <div class="widget-content widget-content-area">
                <div class="table-responsive">
                    <table id="html5-extension" class="table table-hover non-hover" style="width:100%">
                        <thead>
                        <tr>
                            <th>Id</th>
                            <th>{{ __('messages.image') }}</th>
                            <th>{{ __('messages.category_title') }}</th>
{{--                            <th class="text-center">{{ __('messages.sub_category_first') }}</th>--}}
                            @if(Auth::user()->update_data)
                                <th class="text-center">{{ __('messages.edit') }}</th>@endif
                            @if(Auth::user()->delete_data)
                                <th class="text-center">{{ __('messages.delete') }}</th>@endif
                        </tr>
                        </thead>
                        <tbody id="sortable">
                        <?php $i = 1; ?>
                        @foreach ($data as $category)
                            <tr id="id_{{ $category->id }}">
                                <td><?=$i;?></td>
                                <td class="text-center"><img src="{{image_cloudinary_url()}}{{ $category->image }}"/>
                                </td>
                                <td>{{ app()->getLocale() == 'en' ? $category->title_en : $category->title_ar }}</td>
{{--                                <td class="text-center blue-color">--}}
{{--                                    <a href="{{route('sub_cat.show',$category->id)}}">--}}
{{--                                        <div class="">--}}
{{--                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"--}}
{{--                                                 viewBox="0 0 24 24"--}}
{{--                                                 fill="none" stroke="currentColor" stroke-width="2"--}}
{{--                                                 stroke-linecap="round"--}}
{{--                                                 stroke-linejoin="round" class="feather feather-layers">--}}
{{--                                                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>--}}
{{--                                                <polyline points="2 17 12 22 22 17"></polyline>--}}
{{--                                                <polyline points="2 12 12 17 22 12"></polyline>--}}
{{--                                            </svg>--}}
{{--                                        </div>--}}
{{--                                    </a>--}}
{{--                                </td>--}}
                                @if(Auth::user()->update_data)
                                    <td class="text-center blue-color"><a
                                            href="{{route('forum_categories.edit',$category->id)}}"><i
                                                class="far fa-edit"></i></a></td>
                                @endif
                                @if(Auth::user()->delete_data)
                                    <td class="text-center blue-color"><a
                                            onclick="return confirm('Are you sure you want to delete this item?');"
                                            href="{{route('forum_categories.destroy',$category->id)}}"><i
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
    </div>
@endsection
