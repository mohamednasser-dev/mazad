@extends('admin.app')
@section('title' , __('messages.payments'))
@section('content')
    <div id="tableSimple" class="col-lg-12 col-12 layout-spacing">

        <div class="statbox widget box box-shadow">
            <div class="widget-header">
            <div class="row">
                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                    <h4>{{ __('messages.payments') }}</h4>
                </div>
            </div>
                <div class="row">{{ __('messages.total_payments') }} &nbsp; <code>{{$data->sum('price')}}</code> </div>
        </div>
        <div class="widget-content widget-content-area">
            <div class="table-responsive">
                <table id="html5-extension" class="table table-hover non-hover" style="width:100%">
                    <thead>
                    <tr>
                        <th class="text-center blue-color">Id</th>
                        <th class="text-center blue-color">{{ __('messages.user_name') }}</th>
                        <th class="text-center blue-color">{{ __('messages.amount') }}</th>
                        <th class="text-center blue-color">{{ __('messages.price') }}</th>
                        <th class="text-center blue-color">{{ __('messages.date') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i = 1; ?>
                    @foreach ($data as $row)
                        <tr >
                            <td class="text-center blue-color"><?=$i;?></td>
                            <td class="text-center blue-color">{{ $row->User->name }}</td>
                            <td class="text-center blue-color">{{ $row->value }}</td>
                            <td class="text-center blue-color">{{ $row->price }}</td>
                            <td class="text-center">{{ $row->created_at->format('Y-m-d') }}</td>
                            <?php $i++; ?>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
