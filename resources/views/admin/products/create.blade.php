@extends('admin.layouts.app')

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
    <h3 class="page-title khmer-text">បញ្ចូលទំនិញ</h3>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body custom-edit-service">
                <!-- Add Product -->
                <form method="post" enctype="multipart/form-data" id="product-form" action="{{ route('products.store') }}">
                    @csrf

                    <!-- Optional Barcode and Product Name -->
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Barcode</label>
                                    <input type="text" class="form-control" name="barcode" value="{{ old('barcode') }}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label​ class="khmer-text">ឈ្មោះទំនិញ <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="product_name" value="{{ old('product_name') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shelf and Description -->
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label​ class="khmer-text">ធ្នើ (Optional)</label>
                                    <input type="text" name="shelf" class="form-control" value="{{ old('shelf') }}" placeholder="Enter Shelf">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="khmer-text">ព័ត៌មានបន្ថែម</label>
                                    <input type="text" class="form-control" name="description" value="{{ old('description') }}" placeholder="Enter Description">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category and Supplier -->
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <labe class="khmer-text">ប្រភេទ <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="category_id" id="category_id" required>
                                        <option value="" selected disabled>Select a Category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="khmer-text">អ្នកផ្គត់ផ្គង់ <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="supplier_id" required>
                                        <option value="" selected disabled>Select a Supplier</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pill Amount -->
                    <div class="service-fields mb-3" id="pill-amount-field" style="display: none;">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label​ class="khmer-text">ចំនួនគ្រាប់ថ្នាំ ក្នុងមួយប្រអប់</label>
                                    <input type="number" class="form-control" name="pill_amount" value="{{ old('pill_amount') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cost and Selling Price -->
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="khmer-text">តម្លៃទិញចូល <span class="text-danger">*</span></label>
                                    <input class="form-control" type="number" step="0.01" id="cost-price" name="cost_price" value="{{ old('cost_price', 0) }}" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="khmer-text">តម្លៃលក់ចេញ <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="selling-price" name="price" value="{{ old('price') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Image and Margin -->
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="khmer-text">រូបភាពទំនិញ</label>
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="khmer-text">ចំណេញ (%)</label>
                                    <input class="form-control" type="text" id="margin" name="discount" value="0" readonly>
                                </div>
                            </div>
                            <div class="col-lg-6" id="price-per-pill-field" style="display: none;">
                                <div class="form-group">
                                    <label class="khmer-text">តម្លៃថ្នាំមួយគ្រាប់</label>
                                    <input type="text" class="form-control" id="price-per-pill" name="price_per_pill" value="0" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="submit-section">
                        <button class="btn btn-success submit-btn" type="submit" name="form_submit" value="submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
$(document).ready(function () {
    $('#category_id').change(function () {
        var selectedCategory = $(this).find('option:selected').text().toLowerCase();
        if (selectedCategory === 'medicine') {
            $('#pill-amount-field').show();
            $('#price-per-pill-field').show();
        } else {
            $('#pill-amount-field').hide();
            $('#price-per-pill-field').hide();
        }
    });

    // Calculate margin or price based on input changes
    $('#selling-price, #cost-price').on('input', function () {
        calculateMarginOrPrice();
    });

    function calculateMarginOrPrice() {
        const costPrice = parseFloat($('#cost-price').val()) || 0;
        const sellingPrice = parseFloat($('#selling-price').val()) || 0;

        if (costPrice > 0 && sellingPrice > 0) {
            const margin = ((sellingPrice - costPrice) / costPrice) * 100;
            $('#margin').val(margin.toFixed(2));
        }
    }

    function calculateSellingPrice() {
        const costPrice = parseFloat($('#cost-price').val()) || 0;
        const margin = parseFloat($('#margin').val()) || 0;

        if (costPrice > 0 && margin > 0) {
            const sellingPrice = costPrice + (costPrice * (margin / 100));
            $('#selling-price').val(sellingPrice.toFixed(2));
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
});
</script>
@endpush
