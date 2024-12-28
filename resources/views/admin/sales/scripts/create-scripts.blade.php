<script>
    $(document).ready(function() {
        // Define the variable at the beginning of the script
        let updatingPayAmount = false;

        let products = @json($products); // Ensure 'box_stock', 'pill_stock', and 'bar_code' are included
        let cart = {};
        let selectedProduct = null;

        // Function to show the medicine product modal
        function showMedicineProductModal(product) {
            selectedProduct = product;
            // Calculate total leftover pills from individual purchases
            let totalLeftoverPills = product.purchases.reduce((sum, purchase) => sum + (purchase.leftover_pills ?? 0), 0);
            $('#medicine-product-name').text(product.product_name);
            $('#medicine-product-stock').text(
                `${product.box_stock ?? 0} boxes, ${product.pill_stock ?? 0} pills, ${totalLeftoverPills} leftover pills`
            );
            $('#medicine-product-image')
                .attr('src', '{{ asset("storage/purchases/") }}/' + product.image)
                .css('max-width', '50%');
            $('#medicine-sale-by').val('box').closest('.form-group').show();
            $('#medicine-quantity').val(1).closest('.form-group').show();
            $('#medicineProductModal').modal('show');
        }

        // Add product to cart
        $('.product-card img').click(function() {
            let card = $(this).closest('.product-card');
            if (card.hasClass('out-of-stock')) {
                return; // Don't proceed if out of stock
            }
            let productId = card.data('id');
            let categoryId = card.data('category');
            let medicineCategoryId = {{ $medicineCategoryId }}; // Define the medicine category ID in your controller and pass it to the view

            if (categoryId == medicineCategoryId) {
                let product = products.find(p => p.id == productId);
                if (product) {
                    showMedicineProductModal(product);
                }
            } else {
                addToCart(productId);
            }
        });

        // Barcode Input
        $('#barcode-input').on('keypress', function(e) {
            if (e.which == 13) {
                e.preventDefault();
                let barcode = $(this).val();
                let product = products.find(p => p.bar_code == barcode);
                if (product) {
                    let medicineCategoryId = {{ $medicineCategoryId }}; // Define the medicine category ID in your controller and pass it to the view
                    if (product.category_id == medicineCategoryId) {
                        showMedicineProductModal(product);
                    } else {
                        addToCart(product.id);
                    }
                    $(this).val('');
                } else {
                    alert('Product not found');
                    $(this).val('');
                }
            }
        });

        // Add medicine product to cart
        $('#add-medicine-to-cart').click(function() {
            let quantity = parseInt($('#medicine-quantity').val()) || 1;
            let saleBy = $('#medicine-sale-by').val();
            addToCart(selectedProduct.id, quantity, saleBy);
            $('#medicineProductModal').modal('hide');
        });

        // Function to add product to cart
        function addToCart(productId, quantity = 1, saleBy = 'box') {
            let product = products.find(p => p.id == productId);
            if (!product) return;

            // Generate a unique key for each cart item based on product ID and sale type
            let cartKey = `${productId}_${saleBy}`;

            if (cart[cartKey]) {
                cart[cartKey].quantity += quantity;
            } else {
                cart[cartKey] = {
                    id: product.id,
                    name: product.product_name,
                    price: product.price,
                    price_per_pill: product.price_per_pill,
                    image: product.image,
                    quantity: quantity,
                    sale_by: saleBy,
                    box_stock: product.box_stock ?? 0,
                    pill_stock: product.pill_stock ?? 0,
                    category_id: product.category_id,
                    cartKey: cartKey
                };
            }
            checkStock(cartKey);
            renderCart();
            updateTotals(); // Update totals immediately after adding to cart
        }

        // Product Search
        $('#product-search').on('keyup', function() {
            let query = $(this).val().toLowerCase();
            $('.product-card').each(function() {
                let productName = $(this).find('.card-title').text().toLowerCase();
                if (productName.includes(query)) {
                    $(this).parent().show(); // Show the parent column
                } else {
                    $(this).parent().hide(); // Hide the parent column
                }
            });
        });

        // Ensure there's only one Barcode Input event handler
        $('#barcode-input').on('keypress', function(e) {
            if (e.which == 13) {
                e.preventDefault();
                let barcode = $(this).val();
                let product = products.find(p => p.bar_code == barcode);
                if (product) {
                    let medicineCategoryId = {{ $medicineCategoryId }}; // Define the medicine category ID in your controller and pass it to the view
                    if (product.category_id == medicineCategoryId) {
                        selectedProduct = product;
                        $('#medicine-product-name').text(selectedProduct.product_name);
                        $('#medicine-product-stock').text(
                            `${selectedProduct.box_stock ?? 0} boxes, ${selectedProduct.pill_stock ?? 0} pills`
                        );
                        $('#medicine-product-image').attr('src', `{{ asset('storage/purchases/') }}/${selectedProduct.image}`).css('max-width', '50%');
                        $('#medicine-sale-by').val('box').closest('.form-group').show();
                        $('#medicine-quantity').val(1).closest('.form-group').show();
                        $('#medicineProductModal').modal('show');
                    } else {
                        addToCart(product.id);
                    }
                    $(this).val('');
                } else {
                    alert('Product not found');
                    $(this).val('');
                }
            }
        });

        // Category Filtering
        $('#category-list').on('click', '.nav-link', function(e) {
            e.preventDefault();
            let categoryId = $(this).data('category');
            $('#category-list .nav-link').removeClass('active');
            $(this).addClass('active');
            $('.product-card').each(function() {
                let productCategoryId = $(this).data('category');
                if (categoryId == 'all' || productCategoryId == categoryId) {
                    $(this).parent().show(); // Show the parent column
                } else {
                    $(this).parent().hide(); // Hide the parent column
                }
            });
        });

        // Function to format numbers with thousand separators
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        // Render cart items
        function renderCart() {
            let cartItemsContainer = $('#cart-items');
            cartItemsContainer.empty();
            let totalPrice = 0;

            $.each(cart, function(index, item) {
                let itemPrice = item.sale_by === 'box' ? item.price : item.price_per_pill;
                let itemTotal = itemPrice * item.quantity;
                totalPrice += itemTotal;

                let productImage = `{{ asset('storage/purchases/') }}/${item.image}`;
                let saleTypeText = '';
                if (item.category_id == {{ $medicineCategoryId }}) {
                    saleTypeText = `<div class="sale-type">Sale by: ${item.sale_by}</div>`;
                }

                cartItemsContainer.append(`
                    <div class="cart-item mb-2" data-key="${item.cartKey}">
                        <img src="${productImage}" alt="${item.name}">
                        <div class="cart-item-details" onclick="showProductDetails('${item.cartKey}')">
                            <strong>${item.name}</strong>
                            ${saleTypeText}
                            <div class="quantity-controls">
                                <button type="button" class="btn btn-sm btn-secondary decrease-qty">-</button>
                                <span class="quantity">${item.quantity}</span>
                                <button type="button" class="btn btn-sm btn-secondary increase-qty">+</button>
                            </div>
                        </div>
                        <div class="ml-auto">
                            $${itemTotal.toFixed(2)}
                            <button type="button" class="btn btn-danger btn-sm remove-item-btn" data-key="${item.cartKey}">
                                &times;
                            </button>
                        </div>
                    </div>
                `);
            });

            $('#total-price').text(formatNumber(totalPrice.toFixed(2)) + ' $');
            $('#total-price-riel').text(formatNumber((totalPrice * 4100).toFixed(0)) + '៛');
            updateTotals();
        }

        function showProductDetails(cartKey) {
            let item = cart[cartKey];
            let product = products.find(p => p.id == item.id);
            if (product) {
                // Calculate total leftover pills from individual purchases
                let totalLeftoverPills = product.purchases.reduce((sum, purchase) => sum + (purchase.leftover_pills ?? 0), 0);
                $('#product-detail-image').attr('src', `{{ asset('storage/purchases/') }}/${product.image}`);
                $('#product-detail-name').text(product.product_name);
                $('#product-detail-price').text(product.price.toFixed(2));
                $('#product-detail-price-riel').text((product.price * 4100).toFixed(0));
                $('#product-detail-shelf').text(product.shelf ?? 'N/A');
                $('#product-detail-box-stock').text(product.box_stock ?? 0);
                let totalPillStock = (product.box_stock ?? 0) * (product.pill_amount ?? 0) + totalLeftoverPills;
                $('#product-detail-pill-stock').text(totalPillStock);
                $('#product-detail-leftover-pill').text(totalLeftoverPills);
                $('#productDetailModal').modal('show');
            }
        }

        // Update totals and calculations
        function updateTotals() {
            let totalPrice = parseFloat($('#total-price').text()) || 0;
            let discountType = $('#discount-type').val();
            let discountValue = parseFloat($('#discount-value').val()) || 0;
            let discount = 0;

            if (discountType === 'percentage') {
                discount = (discountValue / 100) * totalPrice;
            } else if (discountType === 'fixed') {
                discount = discountValue;
            }

            let paidAmount = parseFloat($('#paid-amount').val()) || 0;
            let netTotal = totalPrice - discount;
            let dueAmount = netTotal - paidAmount;

            $('#net-total').text(netTotal.toFixed(2));
            $('#paid-amount-display').text(paidAmount.toFixed(2));
            $('#due-amount').text(dueAmount.toFixed(2));
        }

        // Event listeners for quantity adjustment buttons
        $('#cart-items').on('click', '.increase-qty', function() {
            let cartKey = $(this).closest('.cart-item').data('key');
            cart[cartKey].quantity += 1;
            checkStock(cartKey);
            renderCart();
        });

        $('#cart-items').on('click', '.decrease-qty', function() {
            let cartKey = $(this).closest('.cart-item').data('key');
            if (cart[cartKey].quantity > 1) {
                cart[cartKey].quantity -= 1;
                renderCart();
            }
        });

        // Remove item from cart
        $('#cart-items').on('click', '.remove-item-btn', function() {
            let cartKey = $(this).data('key');
            delete cart[cartKey];
            renderCart();
        });

        // Discount and Paid Amount change
        $('#discount, #paid-amount').on('input', function() {
            updateTotals();
        });

        // Full Paid Button
        $('#full-paid-btn').click(function() {
            let netTotal = parseFloat($('#net-total').text()) || 0;
            $('#paid-amount').val(netTotal.toFixed(2));
            updateTotals();
        });

        // Cash Payment
        $('#cash-payment-btn').click(function() {
            if (confirm('Do you want to print the receipt?')) {
                // Implement print functionality if needed
            }
            $('form').submit();
        });

        // Bank Payment
        $('#bank-payment-btn').click(function() {
            $('#bankModal').modal('show');
        });

        // Handle bank selection
        $('#confirm-bank-selection').click(function() {
            let selectedBank = $('#bank-selection').val();
            // Store the selected bank or proceed with the payment
            console.log('Selected Bank:', selectedBank);
            $('#bankModal').modal('hide');
            $('form').submit();
        });

        // Check stock quantity
        function checkStock(cartKey) {
            let item = cart[cartKey];
            let product = products.find(p => p.id == item.id);
            let totalPillStock = (product.box_stock ?? 0) * product.pill_amount + (product.pill_stock ?? 0) + (product.leftover_pills ?? 0);

            if (item.sale_by === 'box' && item.quantity > product.box_stock) {
                alert('Not enough box stock available.');
                item.quantity = product.box_stock;
            }

            // if (item.sale_by === 'pill' && item.quantity > totalPillStock) {
            //     alert('Not enough pill stock available.');
            //     item.quantity = totalPillStock;
            // }
        }

        // Initial calculation of totals
        updateTotals();

        $('#discount-type').change(function() {
            let type = $(this).val();
            if (type === 'percentage' || type === 'fixed') {
                $('#discount-value-container').show();
            } else {
                $('#discount-value-container').hide();
                $('#discount-value').val(0);
            }
            updateTotals();
        });

        $('#discount-value').on('input', function() {
            updateTotals();
        });

        // View product details
        $('#cart-items').on('click', '.cart-item-details', function() {
            let cartKey = $(this).closest('.cart-item').data('key');
            let item = cart[cartKey];
            let product = products.find(p => p.id == item.id);
            if (product) {
                $('#product-detail-image').attr('src', `{{ asset('storage/purchases/') }}/${product.image}`);
                $('#product-detail-name').text(product.product_name);
                $('#product-detail-price').text(product.price.toFixed(2));
                $('#product-detail-price-riel').text((product.price * 4100).toFixed(0));
                $('#product-detail-shelf').text(product.shelf ?? 'N/A');
                $('#product-detail-box-stock').text(product.box_stock ?? 0);
                let totalPillStock = (product.box_stock ?? 0) * (product.pill_amount ?? 0) + (product.pill_stock ?? 0);
                $('#product-detail-pill-stock').text(totalPillStock);
                $('#productDetailModal').modal('show');
            }
        });

        // Remove any existing event handlers and attach only one
        $('#barcode-input').off('keypress').on('keypress', function(e) {
            if (e.which == 13) {
                e.preventDefault();
                let barcode = $(this).val();
                let product = products.find(p => p.bar_code == barcode);

                if (product) {
                    let medicineCategoryId = {{ $medicineCategoryId }};
                    if (product.category_id == medicineCategoryId) {
                        selectedProduct = product;
                        // ...existing code to populate medicine modal...
                        $('#medicineProductModal').modal('show');
                    } else {
                        addToCart(product.id);
                    }
                } else {
                    alert('Product not found');
                }

                $(this).val(''); // Clear the input field
            }
        });

        // Remove unnecessary fields and buttons
        // $('#discount-type, #discount-value-container, #paid-amount, #full-paid-btn, #cash-payment-btn, #bank-payment-btn').remove();

        // Ensure the discount section is visible
        $('#discount-type, #discount-value, #pay-amount').show();

        // Add event listener for the Payment button
        $('#payment-btn').click(function() {
            let totalPrice = parseFloat($('#total-price').text()) || 0;
            let netTotal = totalPrice; // Assuming no discounts
            let dueAmount = netTotal; // Assuming no paid amount

            $('#modal-total-price').text(totalPrice.toFixed(2));
            $('#modal-total-price-riel').text((totalPrice * 4100).toFixed(0) + '៛');
            $('#modal-due-amount').text(dueAmount.toFixed(2));

            $('#paymentSummaryModal').modal('show');
        });

        // When 'Confirm Payment' button is clicked
        $('#confirmPaymentBtn').click(function() {
            // Prepare the cart data
            let cartData = [];
            $.each(cart, function(productId, item) {
                cartData.push({
                    bar_code_id: productId,
                    quantity: item.quantity,
                    sale_by: item.sale_by,
                });
            });

            // Validate cart is not empty
            if (cartData.length === 0) {
                alert('Your cart is empty.');
                return;
            }

            // Get discount type and value
            let discountType = $('#discount-type').val();
            let discountValue = parseFloat($('#discount-value').val()) || 0;

            // Get payment amounts and due/cashback amounts
            let payAmountDollar = parseFloat($('#pay-amount-dollar').val()) || 0;
            let payAmountRiel = parseFloat($('#pay-amount-riel').val()) || 0;
            let dueAmountDollar = parseFloat($('#due-amount').val()) || 0;
            let dueAmountRiel = parseFloat($('#due-amount-riel').val()) || 0;
            let cashbackDollar = dueAmountDollar < 0 ? Math.abs(dueAmountDollar) : 0;
            let cashbackRiel = dueAmountRiel < 0 ? Math.abs(dueAmountRiel) : 0;
            if (dueAmountDollar < 0) {
                dueAmountDollar = 0;
            }
            if (dueAmountRiel < 0) {
                dueAmountRiel = 0;
            }

            // Get payment method
            let paymentMethod = $('#payment-method').val();

            // Send the cart data to the server
            $.ajax({
                url: '{{ route('sales.store') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    cart: cartData,
                    discount_type: discountType,
                    discount_value: discountValue,
                    pay_amount_dollar: payAmountDollar,
                    pay_amount_riel: payAmountRiel,
                    due_amount_dollar: dueAmountDollar,
                    due_amount_riel: dueAmountRiel,
                    cashback_dollar: cashbackDollar,
                    cashback_riel: cashbackRiel,
                    payment_method: paymentMethod,
                },
                success: function(response) {
                    if (response.success) {
                        // Clear the cart
                        cart = {};
                        renderCart();

                        // Ask if the user wants to print the receipt
                        if (confirm('Do you want to print the receipt?')) {
                            // Open the bare receipt page in a new tab
                            let receiptWindow = window.open(`/admin/sales/bare-receipt/${response.invoice_id}`, '_blank');

                            // Check if the receipt window is closed and reload the sale create page
                            let checkReceiptWindowClosed = setInterval(function() {
                                if (receiptWindow.closed) {
                                    clearInterval(checkReceiptWindowClosed);
                                    window.location.reload();
                                }
                            }, 1000);
                        } else {
                            // Reload the sale create page
                            window.location.reload();
                        }
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'An error occurred while processing the sale.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                }
            });
        });

        // Add event listener for the Payment button
        $('#payment-btn').click(function() {
            let totalPrice = parseFloat($('#total-price').text()) || 0;
            let discountAmount = parseFloat($('#discount-amount').val()) || 0;
            let discountPercentage = parseFloat($('#discount-percentage').val()) || 0;
            let discount = discountAmount + (discountPercentage / 100) * totalPrice;
            let netTotal = totalPrice - discount;
            let payAmount = parseFloat($('#pay-amount').val()) || 0;
            let dueAmount = netTotal - payAmount;
            let cashBack = payAmount - netTotal;

            $('#modal-total-price').text(totalPrice.toFixed(2));
            $('#modal-total-price-riel').text((totalPrice * 4100).toFixed(0) + '៛');


            if (payAmount < netTotal) {
                $('#due-label').text('Due Amount:');
                $('#due-amount').val(dueAmount.toFixed(2));
                $('#modal-due-amount').text(dueAmount.toFixed(2));
            } else {
                $('#due-label').text('Cash Back:');
                $('#due-amount').val(cashBack.toFixed(2));
                $('#modal-due-amount').text(cashBack.toFixed(2));
            }

            $('#paymentSummaryModal').modal('show');
        });

        // Confirm payment button
        $('#confirm-payment-btn').click(function() {
            $('form').submit();
        });

        let totalPrice = 0;

        // Open payment modal
        $('#payment-btn').click(function() {
            totalPrice = parseFloat($('#total-price').text()) || 0;
            $('#discount-type').val('percentage');
            $('#discount-value').val(0);
            $('#pay-amount').val(0);
            $('#due-amount').val(0);
            $('#due-label').text('Due Amount:');
            $('#modal-total-price').text(totalPrice.toFixed(2));
            $('#modal-total-price-riel').text((totalPrice * 4100).toFixed(0) + '៛');
            updatePaymentSummary();
            $('#paymentSummaryModal').modal('show');
        });

        // Update payment summary function
        function updatePaymentSummary() {
            let totalPrice = parseFloat($('#total-price').text().replace(/,/g, '')) || 0;
            let discountType = $('#discount-type').val();
            let discountValue = parseFloat($('#discount-value').val()) || 0;
            let payAmountDollar = parseFloat($('#pay-amount-dollar').val()) || 0;
            let payAmountRiel = parseFloat($('#pay-amount-riel').val()) || 0;
            let discount = 0;
            let exchangeRate = 4100;

            if (discountType === 'percentage') {
                discount = (discountValue / 100) * totalPrice;
            } else if (discountType === 'fixed-dollar') {
                discount = discountValue;
            } else if (discountType === 'fixed-riel') {
                discount = discountValue / exchangeRate;
            }

            let netTotal = totalPrice - discount;
            let netTotalRiel = netTotal * exchangeRate;
            let payTotal = payAmountDollar + (payAmountRiel / exchangeRate);
            let dueAmount = netTotal - payTotal;
            let dueAmountRiel = dueAmount * exchangeRate;

            $('#modal-total-price').text(formatNumber(netTotal.toFixed(2)) + ' $');
            $('#modal-total-price-riel').text(formatNumber(netTotalRiel.toFixed(0)) + ' ៛');

            if (dueAmount > 0) {
                $('#due-label').text('Due Amount:');
                $('#due-amount').val(formatNumber(dueAmount.toFixed(2)) + ' $ / ' + formatNumber(dueAmountRiel.toFixed(0)) + ' ៛');
            } else {
                let cashBack = Math.abs(dueAmount);
                let cashBackRiel = Math.abs(dueAmountRiel);
                $('#due-label').text('Cash Back:');
                $('#due-amount').val(formatNumber(cashBack.toFixed(2)) + ' $ / ' + formatNumber(cashBackRiel.toFixed(0)) + ' ៛');
            }

            // Show or hide the confirm payment button based on due amount
            if (dueAmount.toFixed(2) === '0.00' || dueAmount < 0) {
                $('#confirmPaymentBtn').show();
            } else {
                $('#confirmPaymentBtn').hide();
            }
        }

        // Update summary on input change
        $('#discount-type, #discount-value, #pay-amount-dollar, #pay-amount-riel').on('input change', function() {
            updatePaymentSummary();
        });

        // Confirm payment
        $('#confirm-payment-btn').click(function() {
            $('form').submit();        });    
        $('.product-card').click(function() {
            let card = $(this);
            if (card.hasClass('out-of-stock')) {
                return; // Don't proceed if out of stock
            }
            let productId = card.data('id');
            let categoryId = card.data('category');
            let medicineCategoryId = {{ $medicineCategoryId }};
            // ...existing code to handle adding product to cart...
        });

        // Show product details in modal when "See Stock" is clicked
        $('.see-stock').click(function(e) {
            e.preventDefault();
            let productId = $(this).data('id');
            let product = products.find(p => p.id == productId);
            if (product) {
                $('#product-detail-image').attr('src', `{{ asset('storage/purchases/') }}/${product.image}`);
                $('#product-detail-name').text(product.product_name);
                $('#product-detail-price').text(product.price.toFixed(2));
                $('#product-detail-price-riel').text((product.price * 4100).toFixed(0));
                $('#product-detail-shelf').text(product.shelf ?? 'N/A');
                $('#product-detail-box-stock').text(product.box_stock ?? 0);
                let totalPillStock = (product.box_stock ?? 0) * (product.pill_amount ?? 0) + (product.pill_stock ?? 0);
                $('#product-detail-pill-stock').text(totalPillStock);
                $('#productDetailModal').modal('show');
            }
        });

        // Use event delegation for 'See Stock' button
        $(document).on('click', '.see-stock-btn', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent event from bubbling up
            let productId = $(this).data('id');
            let product = products.find(p => p.id == productId);
            if (product) {
                // Show product details in modal
                $('#product-detail-image').attr('src', `{{ asset('storage/purchases/') }}/${product.image}`);
                $('#product-detail-name').text(product.product_name);
                $('#product-detail-price').text(product.price.toFixed(2));
                $('#product-detail-price-riel').text((product.price * 4100).toFixed(0));
                $('#product-detail-shelf').text(product.shelf ?? 'N/A');
                $('#product-detail-box-stock').text(product.box_stock ?? 0);
                let totalPillStock = (product.box_stock ?? 0) * (product.pill_amount ?? 0) + (product.pill_stock ?? 0);
                $('#product-detail-pill-stock').text(totalPillStock);
                $('#productDetailModal').modal('show');
            }
        });

        // Function to show the product detail modal
        function showProductDetailModal(product) {
            console.log('showProductDetailModal called with product:', product); // Add this line
            $('#product-detail-name').text(product.product_name);
            $('#product-detail-price').text(parseFloat(product.price).toFixed(2)); // Ensure price is a number
            $('#product-detail-price-riel').text((product.price * 4100).toFixed(0));
            $('#product-detail-shelf').text(product.shelf ?? 'N/A');
            $('#product-detail-box-stock').text(product.box_stock ?? 0);
            $('#product-detail-pill-stock').text(product.pill_stock ?? 0);
            $('#product-detail-leftover-pill').text(product.purchases?.total_leftover_pills ?? 0);
            $('#product-detail-image')
                .attr('src', '{{ asset("storage/purchases/") }}/' + product.image)
                .css('max-width', '50%');
            $('#productDetailModal').modal('show');
        }

        // Add event listener for the View Stock button
        $(document).on('click', '.view-stock-btn', function(e) {
            e.preventDefault();
            console.log('View Stock button clicked'); // Add this line
            let productId = $(this).data('id');
            let selectedProductItem = products.find(p => p.id == productId);
            if (selectedProductItem) {
                showProductDetailModal(selectedProductItem);
            }
        });

        let exchangeRate = 4100;

        function updatePaymentSummary() {
            let totalPrice = parseFloat($('#total-price').text().replace(/,/g, '')) || 0;
            let discountType = $('#discount-type').val();
            let discountValue = parseFloat($('#discount-value').val()) || 0;
            let discount = 0;

            // Calculate discount
            if (discountType === 'percentage') {
                discount = (discountValue / 100) * totalPrice;
            } else if (discountType === 'fixed-dollar') {
                discount = discountValue;
            } else if (discountType === 'fixed-riel') {
                discount = discountValue / exchangeRate;
            }

            // Calculate net total
            let netTotal = totalPrice - discount;

            // Since Pay ($) and Pay (៛) represent the same amount, use only one for calculation
            let payAmountDollar = parseFloat($('#pay-amount-dollar').val()) || 0;

            let dueAmount = netTotal - payAmountDollar;

            // Update the due amount or cash back
            if (dueAmount > 0) {
                $('#due-label').text('Due Amount:');
                $('#due-amount').val(dueAmount.toFixed(2));

                // Update Riel due amount based on Dollar due amount
                let dueAmountRiel = dueAmount * exchangeRate;
                $('#due-amount-riel').val(dueAmountRiel.toFixed(0));
            } else {
                let cashBack = Math.abs(dueAmount);
                $('#due-label').text('Cash Back:');
                $('#due-amount').val(cashBack.toFixed(2));

                // Update Riel cash back amount based on Dollar cash back amount
                let cashBackRiel = cashBack * exchangeRate;
                $('#due-amount-riel').val(cashBackRiel.toFixed(0));
            }

            // Update total price displays
            $('#modal-total-price').text(formatNumber(netTotal.toFixed(2)) + ' $');
            $('#modal-total-price-riel').text(formatNumber((netTotal * exchangeRate).toFixed(0)) + ' ៛');

            // Show or hide the confirm payment button based on due amount or cash back
            if (dueAmount.toFixed(2) === '0.00' || dueAmount < 0) {
                $('#confirmPaymentBtn').show();
            } else {
                $('#confirmPaymentBtn').hide();
            }
        }

        // Update Pay Riel when Pay Dollar changes
        $('#pay-amount-dollar').on('input', function() {
            if (updatingPayAmount) return;
            updatingPayAmount = true;

            let payAmountDollar = parseFloat($(this).val()) || 0;
            $('#pay-amount-riel').val((payAmountDollar * exchangeRate).toFixed(0));

            updatingPayAmount = false;
            updatePaymentSummary();
        });

        // Update Pay Dollar when Pay Riel changes
        $('#pay-amount-riel').on('input', function() {
            if (updatingPayAmount) return;
            updatingPayAmount = true;

            let payAmountRiel = parseFloat($(this).val()) || 0;
            $('#pay-amount-dollar').val((payAmountRiel / exchangeRate).toFixed(2));

            updatingPayAmount = false;
            updatePaymentSummary();
        });

        // Update summary when discount changes
        $('#discount-type, #discount-value').on('input', function() {
            updateSymbol(); // Update the symbol
            updatePaymentSummary();

            // Reset Pay Amounts
            $('#pay-amount-dollar').val('0');
            $('#pay-amount-riel').val('0');
        });

        // Manually trigger updatePaymentSummary when payment button is clicked
        $('#payment-btn').click(function() {
            updatePaymentSummary();
            $('#paymentSummaryModal').modal('show');
        });

        // Initial calculation of totals
        updatePaymentSummary();

        // Modify the payment button click handler to auto-fill pay inputs
        $('#payment-btn').off('click').on('click', function() {
            let totalPrice = parseFloat($('#total-price').text().replace(/,/g, '')) || 0;
            let discountType = $('#discount-type').val();
            let discountValue = parseFloat($('#discount-value').val()) || 0;
            let exchangeRate = 4100; // Ensure exchangeRate is defined

            // Calculate discount
            let discount = 0;
            if (discountType === 'percentage') {
                discount = (discountValue / 100) * totalPrice;
            } else if (discountType === 'fixed-dollar') {
                discount = discountValue;
            } else if (discountType === 'fixed-riel') {
                discount = discountValue / exchangeRate;
            }

            // Calculate net total
            let netTotal = totalPrice - discount;

            // Auto-fill pay inputs with net total
            $('#pay-amount-dollar').val(netTotal.toFixed(2));
            $('#pay-amount-riel').val((netTotal * exchangeRate).toFixed(0));

            // Update payment summary to reflect the auto-filled pay amounts
            updatePaymentSummary();

            // Show the payment summary modal
            $('#paymentSummaryModal').modal('show');
        });

        // Add event listener for the View Stock button
        $(document).on('click', '.view-stock-btn', function(e) {
            e.preventDefault();
            let productId = $(this).data('id');
            let selectedProductItem = products.find(p => p.id == productId);
            if (selectedProductItem) {
                console.log('Product Data:', selectedProductItem); // Debugging line
                // Correct totalPillStock calculation
                let totalPillStock = selectedProductItem.pill_stock ?? 0;
                // Calculate total leftover pills from individual purchases
                let totalLeftoverPills = selectedProductItem.purchases.reduce((sum, purchase) => sum + (purchase.leftover_pills ?? 0), 0);
                $('#product-detail-image').attr('src', '{{ asset("storage/purchases/") }}/' + selectedProductItem.image);
                $('#product-detail-name').text(selectedProductItem.product_name);
                $('#product-detail-box-stock').text(selectedProductItem.box_stock ?? 0);
                $('#product-detail-pill-stock').text(totalPillStock);
                $('#product-detail-leftover-pill').text(totalLeftoverPills);
                // ...existing code...
                $('#productDetailModal').modal('show');
            }
        });

        // Function to update the discount symbol
        function updateSymbol() {
            let discountType = $('#discount-type').val();
            let symbol = '%';
            if (discountType === 'fixed-dollar') {
                symbol = '$';
            } else if (discountType === 'fixed-riel') {
                symbol = '៛';
            }
            $('#discount-symbol').text(symbol);
        }

        $('#sales-form').submit(function(event) {
            event.preventDefault();
            // ...existing code...
            $.ajax({
                // ...existing code...
                success: function(response) {
                    if(response.success) {
                        console.log('Invoice ID:', response.invoiceId); // Add this line to log the invoiceId
                        window.open("/admin/sales/view/"+response.invoiceId, "_blank");
                    }
                    // ...existing code...
                },
                // ...existing code...
            });
        });

        // ...existing code...
        $('#barcode-input').off('keypress').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                let barcode = $(this).val().trim();
                let productImg = $(`.product-card[data-barcode="${barcode}"] img`);
                if (productImg.length) {
                    productImg.trigger('click');
                } else {
                    alert('Product not found');
                }
                $(this).val('');
            }
        });
        // ...existing code...

    });
</script>