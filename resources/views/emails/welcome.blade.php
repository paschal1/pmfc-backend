<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Our Platform</title>
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
        .cta-button {
            display: inline-block;
            background: #FFD700;
            color: #000;
            padding: 10px 20px;
            margin-top: 20px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        .cta-button:hover {
            background: #e5c100;
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
            <h1>Welcome to PMFC LTD</h1>
        </div>
       
        <div class="content">
            <p>Dear {{ $user['name'] }},</p>
            <p>We are thrilled to welcome you to [Prince M Furnishing Concept]! Thank you for signing up and joining our community.</p>
            
            <p>Here’s what you can do now:</p>
            <ul>
                <li>Explore our features and services.</li>
                <li>Access your dashboard to personalize your experience.</li>
                <li>Get support from our team whenever you need help.</li>
            </ul>
            
            <p>To get started, click the button below to log in:</p>
            <a href="{{ $user['login_url'] }}" class="cta-button">Log In Now</a>
            
            <p>If you have any questions, feel free to reach out to us at <a href="mailto:support@PMFC LTD.com">support@company.com</a>. We’re here to help!</p>
            <p>Thank you for choosing [PMFC LTD]. We’re excited to have you on board!</p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Prince M Furnishing Concept LTD. All Rights Reserved. | <a href="{{ url('PMFC LTD.com') }}">Visit Our Website</a>
        </div>
    </div>
</body>
</html>
