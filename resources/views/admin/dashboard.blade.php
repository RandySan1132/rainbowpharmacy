@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
    <link rel="stylesheet" href="{{asset('assets/plugins/chart.js/Chart.min.css')}}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hanuman&display=swap">
    <style>
        .khmer-text {
            font-family: 'Hanuman', serif;
        }
    </style>
@endpush

@push('page-header')
<!-- <div class="col-sm-12">
	<h3 class="page-title">Welcome {{auth()->user()->name}}!</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item active">Dashboard</li>
	</ul>
</div> -->
@endpush

@section('content')
<div class="row">
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-success border-success">
                        <i class="fe fe-money"></i>
                    </span>
                    <div class="dash-count">
                        <h3>{{AppSettings::get('app_currency', '$')}} {{$today_sales}}</h3>
                    </div>
                </div>
                <div class="dash-widget-info">
                <h6 class="text-muted khmer-text" style="font-weight: normal;">ការលក់ថ្ងៃនេះ</h6>
                <!-- <div class="progress progress-sm">
                        <div class="progress-bar bg-success w-50"></div>
                    </div> -->
                    <a href="{{ route('sales.index') }}" class="show-details">Show Details</a>

                </div>
            </div>
        </div>
    </div><!-- Visit codeastro.com for more projects -->
    <div class="col-xl-3 col-sm-6 col-12">
    <div class="card">
        <div class="card-body">
            <div class="dash-widget-header">
                <span class="dash-widget-icon text-info">
                <i class="fa fa-th-large"></i>
                </span>
                <div class="dash-count">
                <h3>{{$total_purchases}}</h3>
                </div>
            </div>
            <div class="dash-widget-info">
                <h6 class="text-muted khmer-text" style="font-weight: normal;">ការទិញចូលសរុប</h6>
                <a href="{{ route('purchases.index') }}" class="show-details">Show Details</a>

            </div>
        </div>
    </div>
</div>

    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-danger border-danger">
                        <i class="fe fe-folder"></i>
                    </span>
                    <div class="dash-count">
                        <h3>{{$total_expired_products}}</h3>
                    </div>
                </div>
                <div class="dash-widget-info">
                    
                    <h6 class="text-muted khmer-text" style="font-weight: normal;">ទំនិញផុតកំណត់</h6>
                    <a href="{{ route('expired') }}" class="show-details">Show Details</a>

                    <!-- <div class="progress progress-sm">
                        <div class="progress-bar bg-danger w-50"></div>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
    <div class="card">
        <div class="card-body">
            <div class="dash-widget-header">
                <span class="dash-widget-icon text-danger border-danger">
                <img src="{{ asset('assets/icons/package.svg') }}" alt="Out of Stock Icon" style="width:100px; height:30px;">
                </span>
                <div class="dash-count">
                    <h3>{{$out_of_stock}}</h3> <!-- Display out_of_stock count here -->
                </div>
            </div>
            <div class="dash-widget-info">
                <h6 class="text-muted khmer-text" style="font-weight: normal;">ទំនិញអស់ពីស្តុក</h6> <!-- Label as OUT OF STOCK -->
                <a href="{{ route('outstock') }}" class="show-details">Show Details</a>

            </div>
        </div>
    </div>
</div>

</div><!-- Visit codeastro.com for more projects -->
<div class="row">    
    <div class="col-md-12 col-lg-5">
                    
                    <!-- Pie Chart -->
                    <div class="card card-chart">
                        <div class="card-header">
                            <h6 class="card-title text-center khmer-text">ការសង្ខេបចំណូល&ចំណាយ</h6>
                        </div>
                        <div class="card-body">
                            <div style="">
                                {!! $pieChart->render() !!}
                            </div>
                        </div>
                    </div>
                    <!-- /Pie Chart -->
                    
                </div>	
    <div class="col-md-12 col-lg-7">
        
        <!-- Notification Box -->
        <div class="card card-table" style="height: 350px; overflow-y: auto;">
            <div class="card-header">
                <h6 class="card-title">Notifications</h6>
            </div>
            <div class="card-body">
                @if($low_stock_notifications->isEmpty() && $expired_notifications->isEmpty() && $nearly_expired_notifications->isEmpty())
                    <p>No notifications available.</p>
                @else
                    <ul>
                        @foreach($low_stock_notifications as $notification)
                            <li>{{ $notification->data['message'] }}</li>
                        @endforeach
                        @foreach($expired_notifications as $notification)
                            <li>{{ $notification->data['message'] }}</li>
                        @endforeach
                        @foreach($nearly_expired_notifications as $notification)
                            <li>{{ $notification->data['message'] }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        <!-- /Notification Box -->
    </div><!-- Visit codeastro.com for more projects -->


    
    
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-chart">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="khmer-text">ទំនិញលក់ដាច់ ប្រចាំខែ</h6>
                <div>
                    <a href="{{ route('dashboard', ['month' => \Carbon\Carbon::parse($currentMonth)->subMonth()->format('Y-m')]) }}" class="btn btn-sm btn-primary">&larr;</a>
                    <a href="{{ route('dashboard', ['month' => \Carbon\Carbon::parse($currentMonth)->addMonth()->format('Y-m')]) }}" class="btn btn-sm btn-primary">&rarr;</a>
                </div>
            </div>
            <div class="card-body">
                {!! $bestSalesChart->render() !!}
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-chart">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="khmer-text">របាយការណ៍ប្រចាំខែ</h6>
                <div>
                    <a href="{{ route('dashboard', ['month' => \Carbon\Carbon::parse($currentMonth)->subMonth()->format('Y-m')]) }}" class="btn btn-sm btn-primary">&larr;</a>
                    <a href="{{ route('dashboard', ['month' => \Carbon\Carbon::parse($currentMonth)->addMonth()->format('Y-m')]) }}" class="btn btn-sm btn-primary">&rarr;</a>
                </div>
            </div>
            <div class="card-body">
                {!! $barChart->render() !!}
            </div>
        </div>
    </div>
</div>

@endsection

@push('page-js')
<script>
$(document).ready(function() {
    var table = $('#sales-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('sales.index') }}",  // Ensure this route works
        columns: [
            { data: 'product', name: 'product' },
            { data: 'quantity', name: 'quantity' },
            { data: 'total_price', name: 'total_price' },
            { data: 'date', name: 'date' }
        ],
        order: [[3, 'desc']],  // Sort by date descending by default
        searching: false,  // Enable search feature
        language: {
            searchPlaceholder: "Search Sales...",
            search: "_INPUT_"
        }
    });
});

</script> 
<script src="{{asset('assets/plugins/chart.js/Chart.bundle.min.js')}}"></script>

@endpush