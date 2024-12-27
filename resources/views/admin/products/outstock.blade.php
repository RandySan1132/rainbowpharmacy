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
<div class="col-sm-12">
    <h3 class="page-title khmer-text">ទំនិញអស់ស្តុក</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item khmer-text"><a href="{{route('products.index')}}">ទំនិញទាំងអស់</a></li>
        <li class="breadcrumb-item active khmer-text">អស់ស្តុក</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <!-- Outstock Products -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="outstock-product" class="table table-hover table-center mb-0">
                        <thead>
                            <tr class="khmer-text">
                                <th>ឈ្មោះទំនិញ</th>
                                <th>អ្នកផ្គត់ផ្គង់</th> <!-- Supplier Name column -->
                                <th>ចំនួនស្តុក</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be populated by DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /Outstock Products -->
    </div>
</div>
@endsection

@push('page-js')
<script>
    $(document).ready(function() {
        $('#outstock-product').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: "{{ route('outstock') }}",
        error: function(xhr, error, code) {
            console.error('AJAX error:', xhr.responseJSON ? xhr.responseJSON.message : code);
            alert('Error loading data: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'));
        }
    },
    columns: [
        { data: 'product', name: 'product' },
        { data: 'supplier', name: 'supplier' },
        { data: 'quantity', name: 'quantity' }
    ],
    order: [[2, 'asc']]
});

    });
</script>
@endpush
