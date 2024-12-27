<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Receipt</title>
</head>
<body>
    <div id="receipt-content">
        {!! $receiptContent !!}
    </div>
    <button onclick="printReceipt()">Print Receipt</button>

    <script>
        function printReceipt() {
            let printContents = document.getElementById('receipt-content').innerHTML;
            let originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        }
    </script>
</body>
</html>
