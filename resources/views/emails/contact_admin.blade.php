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
            <h1>A Wonderful Customer is contacting PMFC LTD as usual attend to him!</h1>
        </div>
    
    <div class="content">
        <p>Dear Manager,</p>

        <p><strong>New Contact Message Received</strong></p>
        <p><strong>Name:</strong> {{ $details['name'] }}</p>
        <p><strong>Email:</strong> {{ $details['email'] }}</p>
        <p><strong>Message:</strong></p>
        <p>{{ $details['message'] }}</p>
     
    </div>
   

        <div class="footer">
            &copy; {{ date('Y') }} Prince M Furnishing Concept LTD. All Rights Reserved. | <a href="{{ url('PMFC LTD.com') }}">Visit Our Website</a>
        </div>
    </div>
</body>
</html>
