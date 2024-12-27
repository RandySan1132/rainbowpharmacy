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
    <h3 class="page-title khmer-text">របាយការណ៍ ទំនិញទិញចូល</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item khmer-text"><a href="{{route('dashboard')}}">ទំព័រដើម</a></li>
        <li class="breadcrumb-item active khmer-text">របាយការណ៍ទិញចូល</li>
    </ul>
</div>
<div class="col-sm-5 col">
    <a href="#generate_report" data-toggle="modal" class="btn btn-success float-right mt-2 khmer-text">បង្កើតរបាយការណ៍</a>
</div>
@endpush

@section('content')
    @isset($purchases)
    <div class="row">
        <div class="col-md-12">
            <!-- Purchases reports-->
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
                                    <th>ចំនួន</th>
                                    <th>កាលផុតកំណត់</th>
                                    <th>កាលបរិចេ្ឆទទិញចូល</th>
                                    <th>Invoice No</th>
                                </tr>
                            </thead>
                            <tbody>
    @foreach ($purchases as $purchase)
        <tr>
            <td>
                <h2 class="table-avatar">
                    @if (!empty($purchase->barCodeData) && !empty($purchase->barCodeData->image))
                        <span class="avatar avatar-sm mr-2">
                            <img class="avatar-img" src="{{ asset('storage/purchases/' . $purchase->barCodeData->image) }}" alt="product image">
                        </span>
                    @endif
                    {{ $purchase->barCodeData->product_name ?? 'No Product Name' }}
                </h2>
            </td>
            <td>{{ $purchase->category->name ?? 'No Category' }}</td>
            <td>{{ $purchase->supplier->name ?? 'No Supplier' }}</td>
            <td>{{ AppSettings::get('app_currency', '$') }}{{ $purchase->cost_price }}</td>
            <td>{{ $purchase->original_quantity }}</td>
            <td>{{ date_format(date_create($purchase->expiry_date), "d M, Y") }}</td>
            <td>{{ $purchase->purchaseDetails->first()->date ?? 'N/A' }}</td>
            <td>{{ $purchase->purchaseDetails->first()->invoice_no ?? 'N/A' }}</td>
        </tr>
    @endforeach                         
</tbody>
<tfoot>
    <tr>
        <th colspan="3" class="khmer-text" style="text-align:right;" >តម្លៃទិញចូលសរុប</th>
        <th colspan="5" id="total-cost" style="text-align:left;">$0.00</th>
    </tr>
</tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /Purchases Report -->
        </div>
    </div>
    @endisset

    <!-- Generate Modal -->
    <div class="modal fade" id="generate_report" aria-hidden="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title khmer-text">បង្កើតរបាយការណ៍</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('purchases.generateReport') }}">
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
<script src="{{ asset('assets/plugins/DataTables/pdfmake-0.1.36/pdfmake.min.js') }}"></script>
<script src="{{ asset('assets/plugins/DataTables/pdfmake-0.1.36/vfs_fonts.js') }}"></script>
<script>
    $(document).ready(function(){
        let table = $('#purchase-table').DataTable({
            paging: false,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'collection',
                    text: 'Export Data',
                    buttons: [
                        // PDF export configuration
                        {
                            extend: 'pdf',
                            title: 'Purchase Report',
                            messageTop: function() {
                                const fromDate = $('.from_date').val();
                                const toDate = $('.to_date').val();
                                return `Purchase report from ${fromDate || 'N/A'} to ${toDate || 'N/A'}`;
                            },
                            exportOptions: {
                                columns: "thead th:not(.action-btn)"
                            },
                            customize: function (doc) {
                                doc.content[1].table.widths = ['20%', '20%', '15%', '15%', '10%', '10%', '10%', '10%'];

                                let totalCost = 0;
                                $('#purchase-table tbody tr').each(function () {
                                    const cost = parseFloat($(this).find('td').eq(3).text().replace(/[^0-9.-]+/g, "")) || 0;
                                    totalCost += cost;
                                });

                                doc.content.push({
                                    table: {
                                        widths: ['*', '*', '*', '*', '*', '*', '*', '*'],
                                        body: [
                                            [
                                                { text: '', border: [false, false, false, false] },
                                                { text: '', border: [false, false, false, false] },
                                                { text: '', border: [false, false, false, false] },
                                                { text: 'Total', bold: true, alignment: 'right' },
                                                { text: `$${totalCost.toFixed(2)}`, alignment: 'right', bold: true },
                                                { text: '', border: [false, false, false, false] }
                                            ]
                                        ]
                                    },
                                    margin: [0, 10, 0, 0]
                                });
                            }
                        },
                        {
                            extend: 'excel',
                            title: 'Purchase Report',
                            exportOptions: {
                                columns: "thead th:not(.action-btn)"
                            }
                        },
                        {
                            extend: 'csv',
                            title: 'Purchase Report',
                            exportOptions: {
                                columns: "thead th:not(.action-btn)"
                            }
                        },
                        {
                            extend: 'print',
                            title: 'Purchase Report',
                            messageTop: function() {
                                const fromDate = $('.from_date').val();
                                const toDate = $('.to_date').val();
                                return `Purchase report from ${fromDate || 'N/A'} to ${toDate || 'N/A'}`;
                            },
                            exportOptions: {
                                columns: "thead th:not(.action-btn)"
                            }
                        }
                    ]
                }
            ]
        });

        updateTotalCost(); // Call once on initial load

        // Calculate total cost and update footer
        function updateTotalCost() {
            let total = 0;
            $('#purchase-table tbody tr').each(function() {
                let costText = $(this).find('td').eq(3).text().replace(/[^0-9.-]+/g,"") || 0;
                total += parseFloat(costText);
            });
            $('#total-cost').text('$' + total.toFixed(2));
        }

        table.on('draw', function() {
            updateTotalCost(); // Ensures cost recalculates after pagination
        });

        // Today button sets both dates to current date
        $('#today-btn').on('click', function() {
            let today = new Date().toISOString().substr(0, 10);
            $('input[name="from_date"]').val(today);
            $('input[name="to_date"]').val(today);
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
