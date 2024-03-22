<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashfree Checkout Integration by Sarthak</title>
    <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
</head>
<body>
<script>
    const paymentSessionId = "{{ $payment_session_id }}";
    const testMode = {{ $test_mode ? 'true' : 'false' }};

    const checkoutOptions = {
        paymentSessionId: paymentSessionId,
        returnUrl: "{{ route('clients.invoice.show', $invoiceId) }}"
    };
    
    const cashfree = Cashfree({ mode: testMode ? "sandbox" : "production" });
    cashfree.checkout(checkoutOptions).then(function(result) {
        if (result.error) {
            alert(result.error.message);
        }
        if (result.redirect) {
            console.log("Redirection");
        }
    });
</script>
</body>
</html>
