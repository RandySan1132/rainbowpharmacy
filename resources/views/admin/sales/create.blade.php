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
@section('content')
<div class="row" style="padding-top: 60px;">
    <!-- Category Sidebar -->
    <div class="col-md-12 mb-3" style="position: fixed; top: 60px; width: 100%; z-index: 1000; background-color: white; padding: 10px; margin-left: -13px;">
        <ul class="nav nav-pills d-flex justify-content-between" id="category-list">
            <div class="d-flex">
                <li class="nav-item">
                    <a class="nav-link active" data-category="all" href="#">All</a>
                </li>
                @foreach($categories as $category)
                <li class="nav-item">
                    <a class="nav-link" data-category="{{ $category->id }}" href="#">{{ $category->name }}</a>
                </li>
                @endforeach
            </div>
            <div class="d-flex" style="position: fixed; left: 1300px; top: 70px;">
                <!-- Search Bar -->
                <input type="text" id="product-search" class="form-control mr-2" placeholder="Search products...">
                <!-- Barcode Input -->
                <input type="text" id="barcode-input" class="form-control" placeholder="Scan barcode">
            </div>
        </ul>
    </div>
    <!-- Product Grid -->
    <div class="col-md-9">
        <div class="card" style="border: none; margin-left:-30px; margin-top: -70px; margin-bottom: -10px;">
            <div class="card-body" style="padding-top: 50px;">
                <div class="row" id="product-grid">
                    @foreach($products as $product)
                    @php
                        $isOutOfStock = (
                            ($product->box_stock <= 0) &&
                            ($product->pill_stock <= 0) &&
                            ($product->leftover_pill_stock <= 0)
                        );
                    @endphp
                    <div class="col-md-2 mb-4">
                        <div class="card product-card {{ $isOutOfStock ? 'out-of-stock' : '' }}" 
                            data-id="{{ $product->id }}" 
                            data-category="{{ $product->category_id }}" 
                            data-box-stock="{{ $product->box_stock ?? 0 }}" 
                            data-pill-stock="{{ $product->pill_stock ?? 0 }}" 
                            data-leftover-pill-stock="{{ $product->leftover_pill_stock ?? 0 }}" 
                            data-barcode="{{ $product->bar_code }}">
                            <img src="{{ asset('storage/purchases/' . $product->image) }}" class="card-img-top" alt="{{ $product->product_name }}">
                            <div class="card-body text-center" style="padding: 10px; position: relative;">
                                <h6 class="card-title" style="font-size: 0.9rem;">{{ $product->product_name }}</h6>
                                <a href="#" class="view-stock-btn khmer-text" data-id="{{ $product->id }}" style="display: block; color: #007bff; text-decoration: initial; cursor: pointer;">តាមដានស្តុក</a>
                                <p class="card-text" style="font-size: 1rem; top: 100px; left: 50%; ">
                                    ${{ number_format($product->price, 2) }}<br>
                                    {{ number_format($product->price * 4100, 0) }}៛
                                </p>
                                @if($isOutOfStock)
                                <span class="badge badge-danger">Out of Stock</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <!-- Cart -->
    <div class="col-md-3">
        <div class="card" style="position: fixed; top: 120px; width: 20%; z-index: 1000; border: none; background-color: transparent; box-shadow: none;">
            <div class="card-body" style="border: none; background-color: transparent;">
                <form method="POST" action="{{ route('sales.store') }}" id="sales-form" data-sale-id="{{ $sale->id ?? '' }}">
                    @csrf
                    <div id="cart-items">
                        <!-- Cart items will be displayed here -->
                    </div>
                    <!-- Summary Section -->
                    <div class="summary-section mt-3">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <strong class="khmer-text">សរុប ($)</strong> 
                        <span id="total-price">0.00 $</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <strong class="khmer-text">សរុប (៛)</strong> 
                        <span id="total-price-riel">0 ៛</span>
                    </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-primary btn-block mb-2 khmer-text" id="payment-btn">បង់ប្រាក់</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Include the receipt modal -->
@include('admin.sales.partials.receipt-modal')

<!-- Bank Selection Modal -->
<div class="modal fade" id="bankModal" tabindex="-1" aria-labelledby="bankModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bankModalLabel">Select Bank</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="bank-selection">Choose a bank:</label>
                    <select id="bank-selection" class="form-control">
                        <option value="ABA">ABA</option>
                        <option value="ACELEDA">ACELEDA</option>
                        <!-- Add more bank options as needed -->
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-bank-selection">Confirm</button>
            </div>
        </div>
    </div>
</div>
<!-- Product Detail Modal -->
<div class="modal fade" id="productDetailModal" tabindex="-1" aria-labelledby="productDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title khmer-text" id="productDetailModalLabel">តាមដានស្តុកទំនិញ</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="font-size: 1.5rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 20px; font-size: 1rem; line-height: 1.6;">
                <div class="text-center mb-4">
                    <img id="product-detail-image" src="" class="img-fluid rounded shadow-sm" alt="Product Image" style="max-height: 300px; object-fit: cover;">
                </div>
                <h5 id="product-detail-name" class="text-center font-weight-bold mb-3">Product Name</h5>
                <div class="text-center mb-3" style="font-size: 1.25rem; font-weight: bold; border: 1px solid #e0e0e0; padding: 15px; border-radius: 10px; background-color: #f9f9f9;">
    <p style="margin: 0; color: #333;" class="khmer-text">
        <strong>តម្លៃ:</strong>
    </p>
    <p style="margin: 5px 0; font-size: 1.75rem; font-weight: bold; color: #007bff;">
        <span id="product-detail-price">0.00</span>$
    </p>
    <p style="margin: 5px 0; font-size: 1.5rem; font-weight: bold; color: #6c757d;">
        <span id="product-detail-price-riel">0</span>៛
    </p>
</div>

                <p class="khmer-text"><strong>ធ្នើ:</strong> <span id="product-detail-shelf" style="color: #007bff;">A1</span></p>
                <p class="khmer-text"><strong>ស្តុកប្រអប់:</strong> <span id="product-detail-box-stock" style="color: #28a745;">50</span></p>
                <p class="khmer-text"><strong>ស្តុកគ្រាប់ថ្នាំ:</strong> <span id="product-detail-pill-stock" style="color: #28a745;">48</span></p>
                {{-- Add Leftover Pill --}}
                <p class="khmer-text"><strong>គ្រាប់ថ្នាំនៅក្រៅប្រអប់:</strong> <span id="product-detail-leftover-pill" style="color: #28a745;">200</span></p>
            </div>
        </div>
    </div>
</div>

<!-- Medicine Product Modal -->
<div class="modal fade" id="medicineProductModal" tabindex="-1" aria-labelledby="medicineProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title khmer-text">បញ្ចូលទំនិញថ្នាំឳសថ</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <h5 id="medicine-product-name"></h5>
                <p class="khmer-text"><strong>នៅក្នុងស្តុក:</strong> <span id="medicine-product-stock"></span></p>
                <img id="medicine-product-image" src="" class="img-fluid mb-3" alt="Product Image">
                <div class="form-group">
                    <label class="khmer-text">លក់ដោយ</label>
                    <select id="medicine-sale-by" class="form-control khmer-text">
                        <option value="box">ប្រអប់</option>
                        <option value="pill">គ្រាប់ថ្នាំ</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="khmer-text">ចំនួន</label>
                    <input type="number" id="medicine-quantity" class="form-control" value="1" min="1">
                </div>
                <button type="button" class="btn btn-primary khmer-text" id="add-medicine-to-cart">ដាក់ចូលកន្ត្រក</button>
            </div>
        </div>
    </div>
</div>
<!-- Payment Summary Modal -->
<div class="modal fade" id="paymentSummaryModal" tabindex="-1" aria-labelledby="paymentSummaryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="paymentSummaryModalLabel">ការបង់ប្រាក់ (Payment Summary)</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="font-size: 1.5rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body" style="background-color: #f9f9f9; font-size: 1rem; line-height: 1.5;">
                <!-- Discount Section -->
                <div style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 15px;">
                    <div style="flex: 1;">
                        <label for="discount-type" class="font-weight-bold">Discount Type:</label>
                        <select class="form-control" id="discount-type" onchange="updateSymbol()" style="font-size: 1rem; padding: 10px;">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed-dollar">Fixed ($)</option>
                            <option value="fixed-riel">Fixed (៛)</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label for="discount-value" class="font-weight-bold">Discount Value:</label>
                        <input type="number" class="form-control" id="discount-value" value="0" min="0" style="font-size: 1rem; padding: 10px;">
                    </div>
                    <div style="flex: 0 0 50px; text-align: center;">
                        <label style="visibility: hidden;">Symbol</label>
                        <span id="discount-symbol" class="form-control bg-light text-center" style="font-size: 1rem; font-weight: bold; padding: 10px;">%</span>
                    </div>
                </div>

                <!-- Payment Amount Section -->
                <div class="form-row align-items-center mb-3">
                    <div class="col-md-6">
                        <label for="pay-amount-dollar" class="font-weight-bold">Pay ($):</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control" id="pay-amount-dollar" value="0" min="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="pay-amount-riel" class="font-weight-bold">Pay (៛):</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">៛</span>
                            </div>
                            <input type="number" class="form-control" id="pay-amount-riel" value="0" min="0">
                        </div>
                    </div>
                </div>

                <!-- Due/Cashback Section -->
                <div class="form-row align-items-center mb-3">
                    <div class="col-md-6">
                        <label id="due-label" for="due-amount" class="font-weight-bold">Due Amount ($):</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control" id="due-amount" value="0" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="due-amount-riel" class="font-weight-bold">Due Amount (៛):</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">៛</span>
                            </div>
                            <input type="number" class="form-control" id="due-amount-riel" value="0" readonly>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="form-group mb-3">
                    <label for="payment-method" class="font-weight-bold">Payment Method:</label>
                    <select class="form-control" id="payment-method" style="font-size: 1rem; padding: 10px;">
                        <option value="cash">&#128176; Cash</option>
                        <option value="bank">&#128179; Bank</option>
                    </select>
                </div>

                <!-- Total Price Section -->
                <div style="background-color: #fff; padding: 15px; border-radius: 10px; border: 1px solid #ddd; margin-top: 15px;">
                    <p style="display: flex; justify-content: space-between; margin: 5px 0; font-size: 1.25rem;">
                        <strong>សរុប ($):</strong>
                        <span id="modal-total-price">0.00</span>
                    </p>
                    <p style="display: flex; justify-content: space-between; margin: 5px 0; font-size: 1.25rem;">
                        <strong>សរុប​ (៛):</strong>
                        <span id="modal-total-price-riel">0៛</span>
                    </p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button id="confirmPaymentBtn" class="btn btn-primary" style="display: none;">Confirm Payment</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-css')
<style>
    @font-face {
        font-family: 'Hanuman';
        src: url('{{ asset('fonts/Hanuman-Regular.ttf') }}') format('truetype');
        font-weight: normal;
        font-style: normal;
    }
    .view-stock-btn {
        font-family: 'Hanuman', sans-serif; /* Apply the Hanuman font to the view stock text */
        text-align: left; /* Align text to the left */
    }
    body {
        font-family: 'NotoSerifKhmer', sans-serif; /* Apply the default font to the body */
    }
    .product-card {
        cursor: pointer;
        height: 350px; /* Adjusted height for consistency */
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        border-radius: 10px; /* Added larger corner radius */
        position: relative; /* Added for positioning */
    }
    .product-card:hover {
        border: 1px solid #007bff;
    }
    .product-card img {
        max-height: 150px;
        object-fit: cover;
        border-top-left-radius: 10px; /* Ensure image corners match card corners */
        border-top-right-radius: 10px; /* Ensure image corners match card corners */
        z-index: 2; /* Ensure image is above clickable area */
    }
    .product-card .card-body {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex-grow: 1;
    }
    .product-card .card-title {
        font-size: 0.9rem;
        margin-bottom: 10px;
        text-align: center;
    }
    .product-card .card-text {
        font-size: 0.8rem;
        text-align: left; /* Align text to the left */
        margin-bottom: 10px;
        min-height: 20px; /* Ensure consistent height for price text */
    }
    .product-card .view-stock-btn {
        color: #007bff;
        text-decoration: initial;
        cursor: pointer;
        margin-bottom: 10px;
        text-align: left; /* Align text to the left */
        position: absolute;
        top: 100px;

    }
    .product-card .btn {
        margin-top: auto;
        z-index: 3; /* Ensure button is above clickable area */
        pointer-events: auto; /* Ensure button is clickable */
    }
    .summary-section h5 {
        margin: 5px 0;
    }
    .summary-section .btn {
        width: 100%;
    }
    /* Styles for cart item images and quantity indicator */
    .cart-item {
        position: relative;
        display: flex;
        align-items: center;
    }
    .cart-item img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        margin-right: 10px;
    }
    .quantity-indicator {
        position: absolute;
        top: -5px;
        left: 45px;
        background-color: red;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 12px;
    }
    .sale-type {
        font-size: 0.8rem;
        color: gray;
    }
    .quantity-controls button {
        padding: 2px 6px;
        font-size: 0.8rem;
        border-radius: 4px;
    }
    .product-card.out-of-stock {
        background-color: #f8f9fa;
        opacity: 0.6;
        pointer-events: none;
    }
    .product-card .clickable-area {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1;
        pointer-events: none; /* Prevent clickable area from blocking other elements */
    }

    /* Truncate product title with ellipsis */
    .cart-item-details strong {
        display: block;
        max-width: 150px; /* Adjust as needed */
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Fix price and remove button positioning */
    .cart-item {
        display: flex;
        align-items: center;
    }

    .cart-item-details {
        flex: 1;
        margin-right: 10px;
    }

    .quantity-controls {
        display: flex;
        align-items: center;
    }

    .ml-auto {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .remove-item-btn {
        margin-top: 5px;
    }

    /* Add styles for payment summary rows */
    .currency-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .currency-row .currency-label {
        width: 45%;
    }
    .currency-row .currency-value {
        width: 45%;
        text-align: right;
    }
</style>
@endpush

@push('page-js')
@include('admin.sales.scripts.create-scripts')
@include('admin.sales.scripts.receipt-scripts')
@endpush