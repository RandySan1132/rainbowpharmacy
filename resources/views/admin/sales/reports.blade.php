@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-css')
<style>
    /* Solid background highlight for sales list */
    #sales-table tbody tr {
        background-color: #e9ecef; /* Light gray for all rows */
    }

    #sales-table tbody tr:hover {
        background-color: #ced4da; /* Darker highlight on hover */
        transition: background-color 0.3s;
    }
</style>

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
    <h3 class="page-title khmer-text">របាយការណ៍ ការលក់</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item khmer-text"><a href="{{ route('dashboard') }}">ទំព័រដើម</a></li>
        <li class="breadcrumb-item active khmer-text">បង្កើ់តរបាយការណ៍</li>
    </ul>
</div>
<div class="col-sm-5 col">
    <a href="#generate_report" data-toggle="modal" class="btn btn-success float-right mt-2 khmer-text">បង្កើ់តរបាយការណ៍</a>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        @isset($sales)
        @if (count($sales) > 0)
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="sales-table" class="datatable table table-hover table-center mb-0">
                        <thead>
                            <tr class="khmer-text">
                                <th>ឈ្មោះទំនិញ</th>
                                <th>ចំនួន</th>
                                <th>កាលបរិច្ឆេទ</th>
                                <th style="text-align: right;">តម្លៃលក់បានសរុប</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalSum = 0; @endphp
                            @foreach ($sales as $sale)
                                @php $totalSum += $sale->total_price; @endphp
                                <tr>
                                    <td>
                                        {{ $sale->barCodeData->product_name ?? ($sale->purchase->barCodeData->product_name ?? 'No Product') }}
                                        @if(isset($sale->barCodeData->category) && strtolower($sale->barCodeData->category->name) == 'medicine')
                                            ({{ $sale->sale_by == 'pill' ? 'គ្រាប់ថ្នាំ' : 'ប្រអប់' }})
                                        @endif
                                    </td>
                                    <td>{{ $sale->quantity }}</td>
                                    <td>{{ $sale->created_at->format('d M, Y') }}</td>
                                    <td style="text-align: right;">
                                        {{ settings('app_currency', '$') . ' ' . number_format($sale->total_price, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" style="text-align: right;">Total</th>
                                <th style="text-align: right;">
                                    {{ settings('app_currency', '$') . ' ' . number_format($totalSum, 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @else
            <p>No sales found for the selected date range.</p>
        @endif
        @endisset
    </div>
</div>

<!-- Generate Modal -->
<div class="modal fade" id="generate_report" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title khmer-text">បង្កើ់តរបាយការណ៍</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('sales.report') }}">
                    @csrf
                    <div class="row form-row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="khmer-text">ចាប់ពី</label>
                                <input type="date" name="from_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="khmer-text">ដល់</label>
                                <input type="date" name="to_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="today-btn" class="btn btn-info btn-block khmer-text">ថ្ងៃនេះ</button>
                    <button type="submit" class="btn btn-success btn-block khmer-text">បង្កើត</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /Generate Modal -->
@endsection

@push('page-js')
<script>
    $(document).ready(function(){
        $('#sales-table').DataTable({
            paging: false,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'collection',
                    text: 'Export Data',
                    buttons: [
                        {
                            extend: 'pdf',
                            exportOptions: {
                                columns: "thead th:not(.action-btn)"
                            }
                        },
                        {
                            extend: 'excel',
                            exportOptions: {
                                columns: "thead th:not(.action-btn)"
                            }
                        },
                        {
                            extend: 'csv',
                            exportOptions: {
                                columns: "thead th:not(.action-btn)"
                            }
                        },
                        {
                            extend: 'print',
                            exportOptions: {
                                columns: "thead th:not(.action-btn)"
                            }
                        }
                    ]
                }
            ]
        });

        // Today button: from_date = today, to_date = tomorrow
        $('#today-btn').on('click', function() {
            let now = new Date();
            let tomorrow = new Date(now);
            tomorrow.setDate(now.getDate() + 1);
            $('input[name="from_date"]').val(now.toISOString().substr(0, 10));
            $('input[name="to_date"]').val(tomorrow.toISOString().substr(0, 10));
        });
    });
</script>
@endpush

