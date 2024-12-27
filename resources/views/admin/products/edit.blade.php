@extends('admin.layouts.app')

@push('page-css')

@endpush

@push('page-header')
<div class="col-sm-12">
	<h3 class="page-title">Edit Product</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Edit Product</li>
	</ul>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body custom-edit-service">
				<!-- Edit Product -->
				<form method="post" enctype="multipart/form-data" id="update_service" action="{{route('products.update', $product->id)}}">
					@csrf
                    @method("PUT")

					<!-- Product Name -->
					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-12">
								<div class="form-group">
									<label>Product Name <span class="text-danger">*</span></label>
									<input type="text" class="form-control" name="product_name" value="{{$product->product_name}}" required>
								</div>
							</div>
						</div>
					</div>

					<!-- Barcode -->
					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-6">
								<div class="form-group">
									<label>Barcode</label>
									<input type="text" class="form-control" name="bar_code" value="{{$product->bar_code}}" >
								</div>
							</div>

							<!-- Shelf -->
							<div class="col-lg-6">
								<div class="form-group">
									<label>Shelf (Optional)</label>
									<input type="text" class="form-control" name="shelf" value="{{$product->shelf}}">
								</div>
							</div>
						</div>
					</div>

					<!-- Category -->
					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-12">
								<div class="form-group">
									<label>Category <span class="text-danger">*</span></label>
									<select class="form-control select2" name="category_id" id="category_id" required>
										@foreach($categories as $category)
											<option value="{{$category->id}}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
												{{$category->name}}
											</option>
										@endforeach
									</select>
								</div>
							</div>
						</div>
					</div>

					 <!-- Pill Amount -->
					<div class="service-fields mb-3" id="pill-amount-field" style="{{ $product->category_id == $medicineCategoryId ? '' : 'display:none;' }}">
						<div class="row">
							<div class="col-lg-12">
								<div class="form-group">
									<label>Pill Amount</label>
									<input type="number" class="form-control" name="pill_amount" value="{{ $product->pill_amount }}">
								</div>
							</div>
						</div>
					</div>

					<!-- Supplier -->
					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-12">
								<div class="form-group">
									<label>Supplier <span class="text-danger">*</span></label>
									<select class="form-control select2" name="supplier_id" required>
										@foreach($suppliers as $supplier)
											<option value="{{$supplier->id}}" {{ $product->supplier_id == $supplier->id ? 'selected' : '' }}>
												{{$supplier->name}}
											</option>
										@endforeach
									</select>
								</div>
							</div>
						</div>
					</div>

					<!-- Price and Margin -->
					<div class="service-fields mb-3">
						<div class="row">
						<div class="col-lg-6">
    <div class="form-group">
        <label>Cost Price <span class="text-danger">*</span></label>
        <input class="form-control" type="number" step="0.01" id="cost-price" name="cost_price" 
               value="{{$product->cost_price}}" required>
    </div>
</div>

<div class="col-lg-6">
    <div class="form-group">
        <label>Selling Price <span class="text-danger">*</span></label>
        <input class="form-control" type="number" step="0.01" id="selling-price" name="price" 
               value="{{$product->price}}" required>
    </div>
</div>

<div class="col-lg-6">
    <div class="form-group">
        <label>Margin (%) <span class="text-danger">*</span></label>
        <input class="form-control" type="text" id="margin" name="discount" value="{{$product->discount}}" readonly>
    </div>
</div>

<div class="col-lg-6" id="price-per-pill-field" style="{{ $product->category_id == $medicineCategoryId ? '' : 'display:none;' }}">
    <div class="form-group">
        <label>Price per Pill</label>
        <input type="text" class="form-control" id="price-per-pill" name="price_per_pill" value="{{ $product->price_per_pill }}" readonly>
    </div>
</div>

						</div>
					</div>

					

					<!-- Description -->
					<div class="service-fields mb-3">
						<div class="row">
							<div class="col-lg-12">
								<div class="form-group">
									<label>Description</label>
									<textarea class="form-control service-desc" name="description">{{$product->description}}</textarea>
								</div>
							</div>
						</div>
					</div>

					<div class="submit-section">
						<button class="btn btn-success submit-btn" type="submit" name="form_submit" value="submit">Submit</button>
					</div>
				</form>
				<!-- /Edit Product -->
			</div>
		</div>
	</div>
</div>
@endsection

@push('page-js')
<script>
$(document).ready(function () {
    // Calculate margin when the cost price or selling price is changed
    $('#cost-price, #selling-price').on('input', function () {
        calculateMargin();
    });

    function calculateMargin() {
        const costPrice = parseFloat($('#cost-price').val()) || 0;
        const sellingPrice = parseFloat($('#selling-price').val()) || 0;

        if (costPrice > 0 && sellingPrice > 0) {
            const margin = ((sellingPrice - costPrice) / costPrice) * 100;
            $('#margin').val(margin.toFixed(2)); // Update the margin field
        } else {
            $('#margin').val(0); // Reset margin if inputs are invalid
        }
    }

    function calculatePricePerPill() {
        const costPrice = parseFloat($('#cost-price').val()) || 0;
        const pillAmount = parseInt($('input[name="pill_amount"]').val()) || 0;

        if (costPrice > 0 && pillAmount > 0) {
            const pricePerPill = costPrice / pillAmount;
            $('#price-per-pill').val(pricePerPill.toFixed(2));
        } else {
            $('#price-per-pill').val(0);
        }
    }

    $('input[name="pill_amount"], #cost-price').on('input', function () {
        calculatePricePerPill();
    });

    calculatePricePerPill(); // Initial calculation on page load

    // Show or hide the pill amount and price per pill fields based on the selected category
    $('#category_id').on('change', function () {
        const selectedCategoryId = $(this).val();
        if (selectedCategoryId == '{{ $medicineCategoryId }}') {
            $('#pill-amount-field').show();
            $('#price-per-pill-field').show();
        } else {
            $('#pill-amount-field').hide();
            $('#price-per-pill-field').hide();
        }
    }).trigger('change'); // Trigger change event on page load to set initial state
});
</script>
@endpush
