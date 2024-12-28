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
    <h3 class="page-title khmer-text">ការលក់</h3>
</div>
@can('create-sale')
<div class="col-sm-5 col">
    <a href="{{ route('sales.create') }}" class="btn btn-primary float-right mt-2 khmer-text">បញ្ចូលការលក់តាម POS</a>
</div>
@endcan
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <table id="salesTable" class="table table-bordered">
                    <thead>
                        <tr class="khmer-text">
                            <th>Invoice ID</th>
                            <th>តម្លៃសរុប</th>
                            <th>ការទូទាត់</th>
                            <th>កាលបរិច្ឆេទ</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate this -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
$(document).ready(function() {
    $('#salesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('sales.index') }}',
        columns: [
            { data: 'invoice_id', name: 'invoice_id', orderable: true },
            { data: 'total_price', name: 'total_price', orderable: true },
            { data: 'payment_method', name: 'payment_method', orderable: true },
            { data: 'date', name: 'date', orderable: true },
            { data: 'action', name: 'action', orderable: false, searchable: false, render: function(data, type, row) {
                return `
                    <a href="{{ url('admin/receipt/print/${row.invoice_id}') }}" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i> View Receipt</a>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="${row.invoice_id}" data-route="{{ url('admin/sales/${row.invoice_id}') }}"><i class="fa fa-trash"></i></button>
                    <a href="{{ url('admin/sales/manual-delete/${row.invoice_id}') }}" class="btn btn-sm btn-warning"><i class="fa fa-exclamation-triangle"></i> Manual Delete</a>
                `;
            }},
        ],
        order: [[0, 'asc']] // Default sorting
    });

    // Handle delete button click
    $('#salesTable').on('click', '.delete-btn', function() {
        let invoiceId = $(this).data('id'); // Use 'invoice_id' instead of 'sale_id'
        let deleteRoute = $(this).data('route');

        console.log('Attempting to delete sale with invoice ID:', invoiceId); // Add this line

        if (confirm('Are you sure you want to delete this sale?')) {
            $.ajax({
                url: deleteRoute,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function(response) {
                    console.log('Delete response:', response); // Add this line
                    if (response.success) {
                        $('#salesTable').DataTable().ajax.reload();
                        alert(response.message);
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    console.error('Delete error:', xhr); // Add this line
                    alert('Failed to delete the sale.');
                }
            });
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
@endpush
