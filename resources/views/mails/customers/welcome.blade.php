<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .header p { margin: 10px 0 0 0; font-size: 16px; opacity: 0.9; }
        .content { background: white; padding: 30px; margin-top: 20px; border-radius: 5px; }
        .feature-section { margin: 25px 0; }
        .feature-section h3 { color: #667eea; margin-top: 0; }
        .feature-list { list-style: none; padding-left: 0; }
        .feature-list li { padding: 8px 0; padding-left: 25px; position: relative; }
        .feature-list li:before { content: "✓"; position: absolute; left: 0; color: #27ae60; font-weight: bold; }
        .cta-button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 15px 0; font-weight: bold; }
        .cta-button:hover { background: #764ba2; }
        .info-box { background: #f0f4ff; padding: 15px; border-left: 4px solid #667eea; margin: 15px 0; border-radius: 3px; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #7f8c8d; margin-top: 20px; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Welcome to FMS!</h1>
            <p>Fertilizer Management System</p>
        </div>

        <div class="content">
            <h2>Hello {{ $customer->name }}!</h2>

            <p>Welcome to the Fertilizer Management System (FMS)! We're excited to have you as a valued customer. 
            Your account has been successfully created and is ready to use.</p>

            <div class="info-box">
                <strong>Your Customer Account Details:</strong>
                <p style="margin: 10px 0 0 0;">
                    <strong>Name:</strong> {{ $customer->name }}<br>
                    <strong>Email:</strong> {{ $customer->email }}<br>
                    <strong>Phone:</strong> {{ $customer->phone }}<br>
                    <strong>Location:</strong> {{ $customer->city ?? 'Not specified' }}
                </p>
            </div>

            <div class="feature-section">
                <h3>What You Can Do With Your Account:</h3>
                <ul class="feature-list">
                    <li>Place and track fertilizer orders</li>
                    <li>View detailed invoices and payment history</li>
                    <li>Manage your account information</li>
                    <li>Track payment status and outstanding balances</li>
                    <li>Receive automatic email notifications for orders and payments</li>
                    <li>Access exclusive customer pricing</li>
                </ul>
            </div>

            <div class="feature-section">
                <h3>Getting Started:</h3>
                <ol style="margin: 10px 0;">
                    <li>Log in to your account</li>
                    <li>Update your profile information if needed</li>
                    <li>Browse our available products</li>
                    <li>Place your first order</li>
                    <li>Track your order status in real-time</li>
                </ol>
            </div>

            <p style="text-align: center; margin: 30px 0;">
                <a href="#" class="cta-button">Access Your Account</a>
            </p>

            <div class="info-box">
                <strong>❓ Need Help?</strong><br>
                If you have any questions or need assistance, our support team is here to help. 
                Please don't hesitate to contact us at {{ config('mail.from.address') }}
            </div>

            <p>
                Thank you for choosing FMS. We look forward to serving you!
            </p>

            <p>
                Best regards,<br>
                <strong>The FMS Team</strong>
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} FMS - Fertilizer Management System. All rights reserved.</p>
            <p>{{ config('app.name') }} | {{ config('mail.from.address') }}</p>
        </div>
    </div>
</body>
</html>
