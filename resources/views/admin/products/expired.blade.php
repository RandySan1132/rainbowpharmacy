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
    <h3 class="page-title khmer-text">ទំនិញផុតកំណត់</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item khmer-text"><a href="{{ route('products.index') }}">ទំនិញទាំងអស់</a></li>
        <li class="breadcrumb-item active khmer-text">ផុតកំណត់</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="expired-product" class="datatable table table-striped table-bordered table-hover">
                        <thead>
                            <tr class="khmer-text">
                                <th>ឈ្មោះទំនិញ</th>
                                <th>ប្រភេទ</th>
                                <th>អ្នកផ្គត់ផ្គង់</th>
                                <th>ចំនួនស្តុក</th>
                                <th>កាលបរិច្ឆេទ ផុតកំណត់</th>
                            </tr>
                        </thead>
                        <tbody></tbody> <!-- Data will be populated by DataTables -->
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
$(document).ready(function() {
    $('#expired-product').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('expired') }}",
            error: function(xhr, error, code) {
                console.log('DataTables error:', error); // Log the error to the console
                console.log('XHR:', xhr); // Log the XHR object to the console
                console.log('Code:', code); // Log the error code to the console
            }
        },
        columns: [
            { data: 'product', name: 'product', orderable: false, searchable: false },
            { data: 'category', name: 'category' },
            { data: 'supplier', name: 'supplier' },
            { data: 'quantity', name: 'quantity' },
            { data: 'expiry_date', name: 'expiry_date' }
        ],
        order: [[4, 'asc']],  // Sort by expiry date ascending
        columnDefs: [
            { 
                targets: 0, 
                render: function(data, type, row) {
                    return data; // Render HTML content properly
                }
            }
        ]
    });
});
</script>
@endpush
