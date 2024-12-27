@extends('admin.layouts.app')

@push('page-css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">Edit Sale</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Sales</a></li>
        <li class="breadcrumb-item active">Edit Sale</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body custom-edit-service">
                <!-- Edit Sale -->
                <form method="POST" action="{{ route('sales.update', $sale->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row form-row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Product <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="bar_code_id" required>
    <option value="" disabled>Select Product</option>
    @foreach ($purchases as $purchase)
        @if ($purchase->total_quantity > 0)
            <option value="{{ $purchase->bar_code_id }}" 
                {{ $purchase->bar_code_id == optional($sale->purchase)->bar_code_id ? 'selected' : '' }}>
                {{ $purchase->barCodeData->product_name }} - (Qty: {{ $purchase->total_quantity }})
            </option>
        @endif
    @endforeach
</select>

                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" value="{{ $sale->quantity }}" name="quantity" min="1" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="sale_type">Sale Type</label>
                                <input type="text" class="form-control" id="sale_type" value="{{ ucfirst($sale->sale_by) }}" readonly>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Discount Type</label>
                                <input type="text" class="form-control" value="{{ $sale->discount_type }}" name="discount_type">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Discount Value</label>
                                <input type="number" class="form-control" value="{{ $sale->discount_value }}" name="discount_value" step="0.01">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Payment Method</label>
                                <input type="text" class="form-control" value="{{ $sale->payment_method }}" name="payment_method">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Pay Amount (Dollar)</label>
                                <input type="number" class="form-control" value="{{ $sale->pay_amount_dollar }}" name="pay_amount_dollar" step="0.01">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Pay Amount (Riel)</label>
                                <input type="number" class="form-control" value="{{ $sale->pay_amount_riel }}" name="pay_amount_riel" step="0.01">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Due Amount (Dollar)</label>
                                <input type="number" class="form-control" value="{{ $sale->due_amount_dollar }}" name="due_amount_dollar" step="0.01">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Due Amount (Riel)</label>
                                <input type="number" class="form-control" value="{{ $sale->due_amount_riel }}" name="due_amount_riel" step="0.01">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Cashback (Dollar)</label>
                                <input type="number" class="form-control" value="{{ $sale->cashback_dollar }}" name="cashback_dollar" step="0.01">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Cashback (Riel)</label>
                                <input type="number" class="form-control" value="{{ $sale->cashback_riel }}" name="cashback_riel" step="0.01">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">Save Changes</button>
                </form>
                <!--/ Edit Sale -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Select or search for a product',
            allowClear: true
        });
    });
</script>
@endpush

