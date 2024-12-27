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
<h3 class="page-title khmer-text" style="color: black;">ទំនិញទាំងអស់</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item khmer-text"><a href="{{route('dashboard')}}">ទំព័រដើម</a></li>
		<li class="breadcrumb-item active khmer-text">មុខទំនិញ</li>
	</ul>
</div>
<div class="col-sm-5 col">
	<a href="{{route('products.create')}}" class="btn btn-success float-right mt-2 khmer-text">បញ្ចូលមុខទំនិញ</a>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">
	
		<!-- Products -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table id="product-table" class="datatable table table-hover table-center mb-0">
						<thead>
							<tr class="khmer-text">
								<th>ឈ្មោះទំនិញ</th>
								<th>ប្រភេទ</th>
								<th>អ្នកផ្គត់ផ្គង់</th> <!-- New Supplier Column -->
								<th>ធ្នើ</th>    <!-- New Shelf Column -->
								<th>តម្លៃទិញចូល</th> <!-- New Cost Price Column -->
								<th>តម្លៃលក់</th>
								<th>ចំណេញ</th>
								<th>ចំនួនគ្រាប់ថ្នាំ</th> <!-- New Pill Amount Column -->
								<th>តម្លៃថ្នាំមួយគ្រាប់</th> <!-- New Price per Pill Column -->
								<th class="action-btn">Action</th>
							</tr>
						</thead>
						<tbody>
							<!-- Table data will be populated by DataTable -->
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- /Products -->
	</div>
</div>
@endsection

@push('page-js')
<script>
$(document).ready(function() {
    var table = $('#product-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('products.index') }}",
        columns: [
            { data: 'product', name: 'bar_code_data.product_name', orderable: true, searchable: true },
            { data: 'category', name: 'categories.name', orderable: true, searchable: true },
            { data: 'supplier', name: 'suppliers.name', orderable: true, searchable: true },
            { data: 'shelf', name: 'bar_code_data.shelf', orderable: true, searchable: true },
            { data: 'cost_price', name: 'bar_code_data.cost_price', orderable: true, searchable: true },
            { data: 'price', name: 'bar_code_data.price', orderable: true, searchable: true },
            { data: 'discount', name: 'bar_code_data.discount', orderable: true, searchable: true },
            { data: 'pill_amount', name: 'bar_code_data.pill_amount', orderable: true, searchable: true },
            { data: 'price_per_pill', name: 'bar_code_data.price_per_pill', orderable: true, searchable: true },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']] // Default sorting on the first column (Product Name)
    });

    // Delete functionality with SweetAlert2
    $('#product-table').on('click', '.delete-btn', function(e) {
        e.preventDefault();
        var route = $(this).data('route');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fas fa-trash"></i> Delete!',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire('Failed!', 'Failed to delete product: ' + response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'An error occurred while deleting the product.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
