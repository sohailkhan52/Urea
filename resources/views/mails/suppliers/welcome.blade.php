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
            <h1>🤝 Welcome to FMS!</h1>
            <p>Fertilizer Management System - Supplier Portal</p>
        </div>

        <div class="content">
            <h2>Hello {{ $supplier->name }}!</h2>

            <p>Welcome to the Fertilizer Management System (FMS) Supplier Portal! We're excited to partner with you. 
            Your supplier account has been successfully created and is ready to use.</p>

            <div class="info-box">
                <strong>Your Supplier Account Details:</strong>
                <p style="margin: 10px 0 0 0;">
                    <strong>Company Name:</strong> {{ $supplier->name }}<br>
                    <strong>Email:</strong> {{ $supplier->email }}<br>
                    <strong>Phone:</strong> {{ $supplier->phone }}<br>
                    <strong>Location:</strong> {{ $supplier->city ?? 'Not specified' }}
                </p>
            </div>

            <div class="feature-section">
                <h3>What You Can Do With Your Account:</h3>
                <ul class="feature-list">
                    <li>Receive and process purchase orders</li>
                    <li>View order status and delivery schedules</li>
                    <li>Access payment history and payment statements</li>
                    <li>Update your company profile and bank details</li>
                    <li>Receive notifications for new orders and payments</li>
                    <li>Download invoices and payment receipts</li>
                </ul>
            </div>

            <div class="feature-section">
                <h3>Getting Started:</h3>
                <ol style="margin: 10px 0;">
                    <li>Log in to your supplier account</li>
                    <li>Complete your profile with bank details</li>
                    <li>Review your account settings and preferences</li>
                    <li>Monitor incoming purchase orders</li>
                    <li>Track payment schedules</li>
                </ol>
            </div>

            <p style="text-align: center; margin: 30px 0;">
                <a href="#" class="cta-button">Access Your Account</a>
            </p>

            <div class="info-box">
                <strong>❓ Need Help?</strong><br>
                If you have any questions or need assistance setting up your account, our procurement team is here to help. 
                Please contact us at {{ config('mail.from.address') }}
            </div>

            <p>
                We look forward to a long and productive partnership with you!
            </p>

            <p>
                Best regards,<br>
                <strong>The FMS Procurement Team</strong>
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} FMS - Fertilizer Management System. All rights reserved.</p>
            <p>{{ config('app.name') }} | {{ config('mail.from.address') }}</p>
        </div>
    </div>
</body>
</html>
