@extends('admin.layouts.app')

@push('page-css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">View Sale</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Sales</a></li>
        <li class="breadcrumb-item active">View Sale</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body custom-edit-service">
                <!-- View Sale -->
                <div class="row form-row">
                    <div class="col-12">
                        <div class="form-group">
                            <label>Invoice ID</label>
                            <input type="text" class="form-control" value="{{ $invoice_id }}" readonly>
                        </div>
                    </div>
                    @foreach ($sales as $sale)
                        <div class="col-12">
                            <div class="form-group">
                                <label>Product</label>
                                <input type="text" class="form-control" value="{{ $sale->barCodeData->product_name }}" readonly>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Quantity</label>
                                <input type="number" class="form-control" value="{{ $sale->quantity }}" readonly>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Total Price</label>
                                <input type="text" class="form-control" value="{{ $sale->total_price }}" readonly>
                            </div>
                        </div>
                    @endforeach
                    <div class="col-12">
                        <div class="form-group">
                            <label>Discount Type</label>
                            <input type="text" class="form-control" value="{{ $sales->first()->discount_type }}" readonly>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Discount Value</label>
                            <input type="text" class="form-control" value="{{ $sales->first()->discount_value }}" readonly>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Payment Method</label>
                            <input type="text" class="form-control" value="{{ $sales->first()->payment_method }}" readonly>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Pay Amount (Dollar)</label>
                            <input type="text" class="form-control" value="{{ $sales->first()->pay_amount_dollar }}" readonly>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Pay Amount (Riel)</label>
                            <input type="text" class="form-control" value="{{ $sales->first()->pay_amount_riel }}" readonly>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Due Amount (Dollar)</label>
                            <input type="text" class="form-control" value="{{ $sales->first()->due_amount_dollar }}" readonly>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Due Amount (Riel)</label>
                            <input type="text" class="form-control" value="{{ $sales->first()->due_amount_riel }}" readonly>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Cashback (Dollar)</label>
                            <input type="text" class="form-control" value="{{ $sales->first()->cashback_dollar }}" readonly>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Cashback (Riel)</label>
                            <input type="text" class="form-control" value="{{ $sales->first()->cashback_riel }}" readonly>
                        </div>
                    </div>
                </div>
                <h4>Receipts</h4>
                @foreach($receipts as $receipt)
                    <div class="receipt">
                        <p>Date: {{ $receipt->date }}</p>
                        <p>Total Amount: ${{ number_format($receipt->total_amount, 2) }}</p>
                        <p>Details: {!! nl2br(e($receipt->receipt_details)) !!}</p>
                    </div>
                @endforeach
                <a href="{{ route('sales.index') }}" class="btn btn-primary btn-block">Back to Sales</a>
                <!--/ View Sale -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
@endpush
