<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Quotation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            border: 1px solid #e6e6e6;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: #000;
            color: #FFD700;
            padding: 20px;
            text-align: center;
        }
        .header img {
            width: 80px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 24px;
            margin: 0;
        }
        .content {
            padding: 20px;
        }
        .content h2 {
            color: #FFD700;
            margin-top: 0;
        }
        .content p {
            margin: 10px 0;
        }
        .content .quote-details {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
        }
        .footer {
            text-align: center;
            padding: 10px;
            background: #000;
            color: #FFD700;
            font-size: 12px;
        }
        .footer a {
            color: #FFD700;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="{{ asset('images/PMFC LTD-logo.png') }}" alt="PMFC LTD Logo">
            <h1>PMFC LTD Quotation</h1>
        </div>
        <div class="content">
            <h2>Hello {{ optional($quote)->email ?? 'Customer' }},</h2>
            <p>Thank you for reaching out to us. Here are the details of your quotation:</p>
            <div class="quote-details">
                @if ($quote && $quote->details)
                    @foreach (json_decode($quote->details, true) as $detail)
                        <strong>Service:</strong> {{ $detail['title'] }}<br>
                        <strong>Price:</strong> N{{ number_format($detail['estimated_price'], 2) }}<br>
                        <br>
                    @endforeach
                    <strong>Total:</strong> N{{ number_format(json_decode($quote->quote, true)['total'] ?? 0, 2) }}
                @else
                    <p>No details available for this quotation.</p>
                @endif
            </div>
            <p>We look forward to serving you. Please feel free to contact us for more information.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} PMFC LTD. All Rights Reserved. | <a href="{{ url('/') }}">Visit Our Website</a>
        </div>
    </div>
</body>
</html>
