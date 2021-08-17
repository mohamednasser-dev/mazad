@extends('admin.app')

@section('title' , __('messages.setting'))

@section('content')
    <div class="col-lg-12 col-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-header">
                <div class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                        <h4>{{ __('messages.setting') }}</h4>

                    </div>
                </div>
                <form method="post" action="{{route('save.settings')}}" enctype="multipart/form-data">
                    @csrf

                    <div id="toggleAccordion">
                        <div class="card">
                            <div class="card-header" id="...">
                                <section class="mb-0 mt-0">
                                    <div role="menu" class="collapsed" data-toggle="collapse"
                                         data-target="#defaultAccordionOne" aria-expanded="true"
                                         aria-controls="defaultAccordionOne">
                                        {{ __('messages.basic_info') }}
                                        <div class="icons">
                                            <svg> ...</svg>
                                        </div>
                                    </div>
                                </section>
                            </div>
                            <div id="defaultAccordionOne" class="collapse" aria-labelledby="..."
                                 data-parent="#toggleAccordion">
                                <div class="card-body">
                                    <div class="form-group mb-4">
                                        <img src="{{image_cloudinary_url()}}{{$data['setting']['logo']}}">
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="logo">{{ __('messages.logo') }}</label>
                                        <input type="file" name="logo" class="form-control" id="logo"
                                               placeholder="{{ __('messages.logo') }}" value="">
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="app_name_ar">{{ __('messages.app_name') }}</label>
                                        <input required type="text" name="app_name_ar" class="form-control"
                                               id="app_name_ar"
                                               placeholder="{{ __('messages.app_name') }}"
                                               value="{{$data['setting']['app_name_ar']}}">
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="email">{{ __('messages.email') }}</label>
                                        <input required type="email" name="email" class="form-control" id="email"
                                               placeholder="{{ __('messages.email') }}"
                                               value="{{$data['setting']['email']}}">
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="phone">{{ __('messages.support_phone') }}</label>
                                        <input required type="phone" name="phone" class="form-control" id="phone"
                                               placeholder="{{ __('messages.phone') }}"
                                               value="{{$data['setting']['phone']}}">
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="address_ar">{{ __('messages.address_ar') }}</label>
                                        <input type="text" name="address_ar" class="form-control" id="address_ar"
                                               placeholder="{{ __('messages.address_ar') }}"
                                               value="{{$data['setting']['address_ar']}}">
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="address_ar">{{ __('messages.address_en') }}</label>
                                        <input type="text" name="address_en" class="form-control" id="address_en"
                                               placeholder="{{ __('messages.address_en') }}"
                                               value="{{$data['setting']['address_en']}}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="...">
                                <section class="mb-0 mt-0">
                                    <div role="menu" class="collapsed" data-toggle="collapse"
                                         data-target="#defaultAccordionTwo" aria-expanded="true"
                                         aria-controls="defaultAccordionTwo">
                                        {{ __('messages.social_media_links') }}
                                        <div class="icons">
                                            <svg> ...</svg>
                                        </div>
                                    </div>
                                </section>
                            </div>
                            <div id="defaultAccordionTwo" class="collapse" aria-labelledby="..."
                                 data-parent="#toggleAccordion">
                                <div class="card-body">

                                    <div class="form-group mb-4">
                                        <label for="facebook">{{ __('messages.facebook') }}</label>
                                        <input type="text" name="facebook" class="form-control" id="facebook"
                                               placeholder="{{ __('messages.facebook') }}"
                                               value="{{$data['setting']['facebook']}}">
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="twitter">{{ __('messages.twitter') }}</label>
                                        <input type="text" name="twitter" class="form-control" id="twitter"
                                               placeholder="{{ __('messages.twitter') }}"
                                               value="{{$data['setting']['twitter']}}">
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="snap_chat">{{ __('messages.snap_chat') }}</label>
                                        <input type="text" name="snap_chat" class="form-control" id="snap_chat"
                                               placeholder="{{ __('messages.snap_chat') }}"
                                               value="{{$data['setting']['snap_chat']}}">
                                    </div>
                                </div>
                            </div>
                        </div>
{{--                        <div class="card">--}}
{{--                            <div class="card-header" id="...">--}}
{{--                                <section class="mb-0 mt-0">--}}
{{--                                    <div role="menu" class="collapsed" data-toggle="collapse"--}}
{{--                                         data-target="#defaultAccordionThree" aria-expanded="true"--}}
{{--                                         aria-controls="defaultAccordionThree">--}}
{{--                                        {{ __('messages.ad_balance') }}--}}
{{--                                        <div class="icons">--}}
{{--                                            <svg> ...</svg>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </section>--}}
{{--                            </div>--}}
{{--                            <div id="defaultAccordionThree" class="collapse" aria-labelledby="..."--}}
{{--                                 data-parent="#toggleAccordion">--}}
{{--                                <div class="card-body">--}}
{{--                                    <div class="form-group mb-4">--}}
{{--                                        <label for="map_url">{{ __('messages.expier_days') }}</label>--}}
{{--                                        <input type="number" min="1" name="expier_days" class="form-control" id="expier_days"--}}
{{--                                               value="{{$data['setting']['expier_days']}}">--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
                    </div>
                    <input type="submit" value="{{ __('messages.edit') }}" class="btn btn-primary">
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function () {
            if ($('input[id="loop_free_balance"]').prop("checked") == true) {
                $('#loop_cont_1').show();
                $('#loop_cont_2').show();
            } else {
                $('#loop_cont_1').hide();
                $('#loop_cont_2').hide();
            }
            $('input[id="loop_free_balance"]').click(function () {
                if ($(this).prop("checked") == true) {
                    $('#loop_cont_1').show();
                    $('#loop_cont_2').show();
                } else if ($(this).prop("checked") == false) {
                    $('#loop_cont_1').hide();
                    $('#loop_cont_2').hide();
                }
            });
        });
    </script>
@endsection

