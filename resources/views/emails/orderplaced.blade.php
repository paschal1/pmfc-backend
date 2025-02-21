<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
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
        .order-summary {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 4px;
        }
        .order-summary p {
            font-family: monospace;
            margin: 5px 0;
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
            <img src="{{ asset('images/company-logo.png') }}" alt="Company Logo">
            <h1>Order Confirmation</h1>
        </div>
       
        <div class="content">
            <p>Dear {{ $order['name'] }},</p>
            <p>Thank you for your order! We are excited to process your request. Below is a summary of your order order:</p>
            
            <div class="order-summary">
                <strong>Order Summary:</strong><br>
                <p>Order ID: {{ $order['order_id'] }}</p>
                <p>Total Price: ${{ number_format($order['total_price'], 2) }}</p>
                <p>Shipping Address: {{ $order['shipping_address'] }}</p>
                <p>Order Date: {{ $order['order_date'] }}</p>
                <p>Status: {{ $order['status'] }}</p>
            </div>
            
            <p>We will notify you once your order is shipped. For any questions, feel free to reply to this email or contact us directly at <a href="mailto:support@company.com">support@company.com</a>.</p>
            <p>Thank you for choosing us. We look forward to serving you!</p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Prince M Furnishing Concept. All Rights Reserved. | <a href="{{ url('company.com') }}">Visit Our Website</a>
        </div>
    </div>
</body>
</html>
