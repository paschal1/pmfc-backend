<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Contacting PMFC LTD</title>
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
        .content .user-message {
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
            <h1>Thank You for Contacting PMFC LTD</h1>
        </div>
       
    <div class="content">
        <p>Dear {{ $details['name'] }},</p>
     
        <p>Thank you for reaching out to PMFC LTD! We appreciate you taking the time to contact us. Below is a summary of your request:</p>
        <div class="user-message">
            <strong>Your Message:</strong><br>
            <p>{{ $details['message'] }}</p>
            <p></p>
        </div>
        <p>Our team has received your inquiry and is currently reviewing it. We will get back to you within <strong>24-48 hours</strong> with the information or assistance you need.</p>
        <p>If you have any additional details or questions in the meantime, feel free to reply to this email or contact us directly at <a href="mailto:support@PMFC LTD.com">support@PMFC LTD.com</a>.</p>
        <p>Thank you for choosing PMFC LTD. We look forward to assisting you further.</p>
    </div>
   

        <div class="footer">
            &copy; {{ date('Y') }} Prince M Furnishing Concept LTD. All Rights Reserved. | <a href="{{ url('PMFC LTD.com') }}">Visit Our Website</a>
        </div>
    </div>
</body>
</html>
