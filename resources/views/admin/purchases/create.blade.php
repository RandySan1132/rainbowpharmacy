@extends('admin.layouts.app')

@push('page-css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">
    <link rel="stylesheet" href="{{asset('assets/plugins/chart.js/Chart.min.css')}}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hanuman&display=swap">
    <style>
        .khmer-text {
            font-family: 'Hanuman', serif;
        }
    </style>
@endpush

@push('page-js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title khmer-text">បញ្ចូលទំនិញទិញចូល</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item khmer-text"><a href="{{ route('dashboard') }}">ទំព័រដើម</a></li>
        <li class="breadcrumb-item active khmer-text">បញ្ចូលទំនិញទិញចូល</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body custom-edit-service">
                <form method="post" enctype="multipart/form-data" autocomplete="off" action="{{ route('purchases.store') }}">
                    @csrf

                    <!-- Supplier Selection -->
                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="khmer-text">អ្នកផ្គត់ផ្គង់ <span class="text-danger">*</span></label>
                                    <select class="form-control" id="supplier" name="supplier" required>
                                        <option value="" selected disabled>Select </option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Invoice No <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="invoice_no" required>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="khmer-text">កាលបរិចេ្ឆទ <span class="text-danger">*</span></label>
                                    <input class="form-control" type="date" name="date" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="service-fields mb-3">
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="khmer-text">រូបវិក័យបត្រ</label>
                                    <input type="file" name="invoice_image" class="form-control" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Medicine Table -->
                    <fieldset id="medicine-fieldset" disabled>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="medicines-table">
                                <thead>
                                    <tr class="khmer-text">
                                        <th>ឈ្មោះទំនិញ</th>
                                        <th>Bar Code</th>
                                        <th>កាលផុតកំណត់</th>
                                        <th>ចំនួនប្រអប់</th>
                                        <th>តម្លៃទិញចូល</th>
                                        <th>តម្លៃទិញចូលសរុប</th>
                                        <th>ចំនួនគ្រាប់ថ្នាំ</th>
                                        <th>Preview</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tr>
    <td>
        <select class="form-control medicine-name-select" name="product_id[]" required></select>
    </td>
    <td><input type="text" name="bar_code[]" class="form-control bar-code-input"></td>
    <td><input type="date" name="expiry_date[]" class="form-control" required></td>
    <td><input type="number" name="box_qty[]" class="form-control box-qty" min="0" required></td>
    <td><input type="number" name="cost_price[]" class="form-control supplier-price" step="0.01" min="0" required></td>
    <td><input type="number" name="total_purchase_price[]" class="form-control total-purchase-price" readonly></td>
    <td><input type="number" name="pill_amount[]" class="form-control pill-amount" readonly></td>
    <td>
        <img src="" alt="Image Preview" class="img-preview" style="max-width: 100px; max-height: 50px;" />
    </td>
    <td>
        <button type="button" class="btn btn-danger remove-row">
            <i class="fas fa-trash"></i>
        </button>
    </td>
</tr>

                                </tbody>
                            </table>
                            <button type="button" class="btn btn-info khmer-text" id="add-medicine-row">
                                <i class="fas fa-plus"></i> បញ្ចូលទំនិញ
                            </button>
                        </div>
                    </fieldset>

                    <div class="submit-section">
                        <button class="btn btn-success submit-btn" type="submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        // Initialize Select2 for the medicine dropdown with AJAX
        function initializeSelect2(element) {
            element.select2({
                placeholder: 'Search for a product',
                minimumInputLength: 1,
                ajax: {
                    url: "{{ route('purchases.getSupplierProducts') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        const supplierId = $('#supplier').val();
                        if (!supplierId) {
                            alert('Please select a supplier first.');
                            return false;
                        }
                        return {
                            supplier_id: supplierId,
                            search: params.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.products.map(product => ({
                                id: product.id,
                                text: product.product_name
                            }))
                        };
                    },
                    cache: true
                }
            }).on('select2:select', function (e) {
                const productId = e.params.data.id;
                const row = $(this).closest('tr');
                fetchProductDetailsById(productId, row);
            });
        }

        // Enable medicine fieldset after supplier selection
        $('#supplier').change(function () {
            const supplierId = $(this).val();
            if (supplierId) {
                $('#medicine-fieldset').prop('disabled', false);
                initializeSelect2($('.medicine-name-select'));
            } else {
                $('#medicine-fieldset').prop('disabled', true);
            }
        });

        // Add a new row to the medicine table
        $('#add-medicine-row').click(function () {
            const newRow = `
                <tr>
                    <td>
                        <select class="form-control medicine-name-select" name="product_id[]" required></select>
                    </td>
                    <td><input type="text" name="bar_code[]" class="form-control bar-code-input"></td>
                    <td><input type="date" name="expiry_date[]" class="form-control" required></td>
                    <td><input type="number" name="box_qty[]" class="form-control box-qty" min="0" required></td>
                    <td><input type="number" name="cost_price[]" class="form-control supplier-price" step="0.01" min="0" required></td>
                    <td><input type="number" name="total_purchase_price[]" class="form-control total-purchase-price" readonly></td>
                    <td><input type="number" name="pill_amount[]" class="form-control pill-amount" readonly></td>
                    <td>
                        <img src="" alt="Image Preview" class="img-preview" style="max-width: 100px; max-height: 50px;" />
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger remove-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            $('#medicines-table tbody').append(newRow);

            // Initialize Select2 for the newly added row
            initializeSelect2($('.medicine-name-select').last());
        });

        // Fetch product details by product ID or barcode
        function fetchProductDetailsById(productId, row) {
            console.log("Fetching product details for ID:", productId); // Add this line
            $.ajax({
                url: "{{ route('purchases.getBarcodeData') }}",
                method: 'GET',
                data: { product_id: productId },
                success: function (response) {
                    console.log("Response received:", response); // Add this line
                    if (response.success) {
                        // Check for duplicate products
                        const isDuplicate = $('.medicine-name-select').filter(function() {
                            return $(this).val() == productId;
                        }).length > 1;

                        if (isDuplicate) {
                            alert('This product is already added.');
                            row.remove();
                        } else {
                            updateRowFields(row, response);
                        }
                    } else {
                        alert('Product not found.');
                    }
                },
                error: function () {
                    alert('Failed to fetch product details.');
                }
            });
        }

        // Update row fields with fetched product details
        function updateRowFields(row, data) {
            console.log("Updating row fields with data:", data);
            console.log("Category name received:", data.category_name); // Debug the actual category name
            const categoryName = (data.category_name || '').toLowerCase();

            if (categoryName === 'medicine') {
                if (data.pill_amount === null || data.pill_amount === undefined) {
                    alert('The selected product has no pill amount defined.');
                    row.remove();
                    return;
                }
                const pillAmountPerBox = parseFloat(data.pill_amount);
                row.find('.pill-amount').data('per-box', pillAmountPerBox);
                row.find('.pill-amount').prop('readonly', true);
                const boxQty = parseFloat(row.find('.box-qty').val()) || 0;
                const calculatedPillAmount = boxQty * pillAmountPerBox;
                row.find('.pill-amount').val(calculatedPillAmount);
                console.log("Pill amount set to:", calculatedPillAmount);
            } else {
                row.find('.pill-amount').val('').data('per-box', null);
                row.find('.pill-amount').prop('readonly', true);
            }

            row.find('.bar-code-input').val(data.bar_code);
            row.find('.supplier-price').val(data.cost_price);
            row.find('.img-preview').attr('src', data.image_url || '');
        }

        // Handle input on barcode field and fetch product details
        let barcodeTimeout;
        $(document).on('input', '.bar-code-input', function () {
            clearTimeout(barcodeTimeout);
            const barCodeInput = $(this);
            barcodeTimeout = setTimeout(function () {
                const barCode = barCodeInput.val();
                const row = barCodeInput.closest('tr');

                if (barCode) {
                    $.ajax({
                        url: "{{ route('purchases.getBarcodeData') }}",
                        method: 'GET',
                        data: { bar_code: barCode },
                        success: function (response) {
                            if (response.success) {
                                updateRowFields(row, response);

                                // Set the medicine name in the Select2 dropdown
                                const medicineSelect = row.find('.medicine-name-select');
                                medicineSelect.empty().append(
                                    new Option(response.product_name, response.product_id, true, true)
                                ).trigger('change');
                            } else {
                                alert('Product not found.');
                            }
                        },
                        error: function () {
                            alert('Failed to fetch product details.');
                        }
                    });
                }
            }, 500); // Delay in milliseconds
        });

        // Calculate total purchase price and pill amount when quantity or price changes
        $(document).on('input', '.box-qty, .supplier-price', function () {
            const row = $(this).closest('tr');
            const boxQty = parseFloat(row.find('.box-qty').val()) || 0;
            const supplierPrice = parseFloat(row.find('.supplier-price').val()) || 0;
            // Retrieve using 'per-box'
            const perBoxPillAmount = parseFloat(row.find('.pill-amount').data('per-box'));

            console.log("Box Quantity changed:", boxQty);
            console.log("Supplier Price:", supplierPrice);
            console.log("Pill Amount per Box:", perBoxPillAmount);

            const total = boxQty * supplierPrice;
            row.find('.total-purchase-price').val(total.toFixed(2));

            // Calculate pill amount regardless of zero value
            if (!isNaN(perBoxPillAmount)) {
                const calculatedPillAmount = boxQty * perBoxPillAmount;
                row.find('.pill-amount').val(calculatedPillAmount);
                console.log("Calculated Pill Amount:", calculatedPillAmount);
            } else {
                row.find('.pill-amount').val('');
            }
        });

        // Remove a row from the table
        $(document).on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
        });
    });
</script>
@endpush

