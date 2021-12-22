@extends('layouts.app')

@section('title') @if(! empty($title)) {{$title}} @endif - @parent @endsection

@section('content')

    <div class="dashboard-wrap">
        <div class="container">
            <div id="wrapper">

                @include('admin.menu')

                <div id="page-wrapper">

                    @if( ! empty($title))
                        <div class="row">
                            <div class="col-lg-12">
                                <h1 class="page-header"> {{ $title }} </h1>
                            </div> <!-- /.col-lg-12 -->
                        </div> <!-- /.row -->
                    @endif

                    @include('admin.flash_msg')

                    @if($withdraw_requests->count() > 0)
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th>@lang('app.campaign')</th>
                                <th>@lang('app.method')</th>
                                <th>@lang('app.amount')</th>
                                <th>@lang('app.status')</th>
                                <th>@lang('app.date')</th>
                                <th>#</th>
                            </tr>

                            @foreach($withdraw_requests as $withdraw)
                                <tr>
                                    <td> @if($withdraw->campaign) {{$withdraw->campaign->title }} @endif</td>
                                    <td>{{$withdraw->withdrawal_account}}</td>
                                    <td>{!! get_amount($withdraw->withdrawal_amount) !!}</td>
                                    <td>{{$withdraw->status}}</td>
                                    <td>{{$withdraw->created_at->format('jS F, Y')}}</td>
                                    <td><a href="{{route('withdraw_request_view', $withdraw->id)}}" class="btn-link"><i class="fa fa-eye"></i> </a> </td>
                                </tr>
                            @endforeach
                        </table>
                    @endif

                    {!! $withdraw_requests->links() !!}


                </div>

            </div>
        </div>
    </div>

@endsection