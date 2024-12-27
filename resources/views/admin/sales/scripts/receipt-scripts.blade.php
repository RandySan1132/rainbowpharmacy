<script>
    document.getElementById('confirmPaymentBtn').addEventListener('click', function() {
        // Generate receipt content
        let receiptContent = '<h4>Receipt</h4>';
        receiptContent += '<p>Date: ' + new Date().toLocaleString() + '</p>';
        receiptContent += '<ul>';
        document.querySelectorAll('#cart-items .cart-item').forEach(function(item) {
            let productName = item.querySelector('.cart-item-details strong').innerText;
            let quantity = item.querySelector('.quantity-controls input').value;
            let price = item.querySelector('.ml-auto span').innerText;
            receiptContent += '<li>' + productName + ' - ' + quantity + ' x ' + price + '</li>';
        });
        receiptContent += '</ul>';
        receiptContent += '<p>Total: ' + document.getElementById('total-price').innerText + '</p>';
        receiptContent += '<p>Total (៛): ' + document.getElementById('total-price-riel').innerText + '</p>';

        document.getElementById('receipt-content').innerHTML = receiptContent;

        // Show receipt modal
        $('#receiptModal').modal('show');

        // Store receipt in the database
        let saleId = document.getElementById('sales-form').dataset.saleId; // Assuming sale ID is stored in a data attribute
        $.ajax({
            url: '{{ route('receipts.store') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                sale_id: saleId
            },
            success: function(response) {
                console.log('Receipt stored successfully:', response);
            },
            error: function(xhr, status, error) {
                console.error('Error storing receipt:', error);
            }
        });
    });

    document.getElementById('print-receipt-btn').addEventListener('click', function() {
        let printContents = document.getElementById('receipt-content').innerHTML;
        let originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload();
    });
</script>
