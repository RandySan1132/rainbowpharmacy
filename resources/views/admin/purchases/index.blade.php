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
<div class="col-sm-7 col-auto">
    <h3 class="page-title khmer-text">ទំនិញទិញចូល</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item khmer-text"><a href="{{route('dashboard')}}">ទំព័រដើម</a></li>
        <li class="breadcrumb-item active khmer-text">ទំនិញទិញចូល</li>
    </ul>
</div>
<div class="col-sm-5 col">
    <a href="{{route('purchases.create')}}" class="btn btn-success float-right mt-2 khmer-text">បញ្ចូលទំនិញទិញចូល</a>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="purchase-table" class="datatable table table-hover table-center mb-0">
                        <thead>
                            <tr class="khmer-text">
                                <th>ឈ្មោះទំនិញ</th>
                                <th>ប្រភេទ</th>
                                <th>អ្នកផ្គត់ផ្គង់</th>
                                <th>តម្លៃទិញចូល</th>
                                <th>ស្តុក</th>
                                <th>ចំនួនគ្រាប់ថ្នាំសរុប</th> <!-- Update this line -->
                                <th>ថ្នាំនៅសល់</th>
                                <th>កាលផុតកំណត់</th>
                                <th>កាលបរិច្ឆេទទិញចូល</th>
                                <th>Invoice No</th>
                                <th class="action-btn">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
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
    var table = $('#purchase-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('purchases.index') }}",
        columns: [
            { data: 'product', name: 'product' },
            { data: 'category', name: 'category' },
            { data: 'supplier', name: 'supplier' },
            { data: 'cost_price', name: 'cost_price' },
            { data: 'quantity', name: 'quantity' },
            { 
                data: 'pill_amount', 
                name: 'pill_amount',
                render: function (data, type, row) {
                    return data !== null ? data : '-';
                }
            },
            { 
                data: 'leftover_pills', 
                name: 'leftover_pills',
                render: function (data, type, row) {
                    return data !== null ? data : '-';
                }
            },
            { data: 'expiry_date', name: 'expiry_date' },
            { data: 'purchase_date', name: 'purchase_date' },
            { data: 'invoice_no', name: 'invoice_no' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $('#purchase-table').on('click', '.delete-btn', function(e) {
        e.preventDefault();
        var route = $(this).data('route');
        var id = $(this).data('id');

        Swal.queue([
            {
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-trash"></i> Delete!',
                cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                confirmButtonClass: "btn btn-success mt-2",
                cancelButtonClass: "btn btn-danger ml-2 mt-2",
                buttonsStyling: false,
                preConfirm: function() {
                    return new Promise(function(resolve) {
                        $.ajax({
                            url: route,
                            type: "DELETE",
                            data: {
                                _token: '{{ csrf_token() }}',
                                id: id
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: "Deleted!",
                                        text: "Delete success",
                                        icon: "success",
                                        showConfirmButton: false,
                                        timer: 1500,
                                    });
                                    table.ajax.reload();
                                } else {
                                    Swal.fire(
                                        'Failed!',
                                        'Failed to delete purchase: ' + response.message,
                                        'error'
                                    );
                                }
                                resolve();
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    'Error!',
                                    'An error occurred while deleting the purchase.',
                                    'error'
                                );
                                resolve();
                            }
                        });
                    });
                }
            }
        ]).catch(Swal.noop);
    });
});
</script>
@endpush
