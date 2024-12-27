@extends('admin.layouts.app')

@push('page-css')
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap-datetimepicker.min.css')}}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hanuman&display=swap">
    <style>
        .khmer-text {
            font-family: 'Hanuman', serif;
        }
    </style>
@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title khmer-text">ព័ត៌មានការទិញចូល</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item khmer-text"><a href="{{route('dashboard')}}">ទំព័រដើម</a></li>
        <li class="breadcrumb-item khmer-text"><a href="{{route('purchases.index')}}">បញ្ជីការទិញចូល</a></li>
        <li class="breadcrumb-item active khmer-text">មើល Invoice</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <h3 class="khmer-text">លេខ Invoice: {{ $purchases->first()->purchaseDetails->first()->invoice_no ?? '' }}</h3>
                @if($purchases->first()->purchaseDetails->first()->invoice_image)
                    <div class="mb-3">
                        <label class="khmer-text">រូបថត Invoice:</label>
                        <img src="{{ asset('storage/purchases/' . $purchases->first()->purchaseDetails->first()->invoice_image) }}" alt="Invoice Image" class="img-thumbnail" style="max-width: 300px;" id="invoiceImage">
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-hover table-center mb-0">
                        <thead>
                            <tr class="khmer-text">
                                <th>ទំនិញ</th>
                                <th>តម្លៃទិញចូល</th>
                                <th>ចំនួន</th>
                                <th>កាលផុតកំណត់</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchases as $purchase)
                            <tr>
                                <td>{{ $purchase->barCodeData->product_name ?? 'N/A' }}</td>
                                <td>{{ $purchase->cost_price }}</td>
                                <td>{{ $purchase->quantity }}</td>
                                <td>{{ $purchase->expiry_date }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for enlarging the image -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Invoice Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img src="" id="enlargedImage" class="img-fluid" alt="Invoice Image">
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script src="{{asset('assets/js/moment.min.js')}}"></script>
<script src="{{asset('assets/js/bootstrap-datetimepicker.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $('#invoiceImage').on('click', function() {
            var src = $(this).attr('src');
            $('#enlargedImage').attr('src', src);
            $('#imageModal').modal('show');
        });
    });
</script>
@endpush
