<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Urea Inventory Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .welcome-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 50px;
            max-width: 600px;
        }
        .welcome-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .welcome-header h1 {
            color: #1e3c72;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .welcome-header p {
            color: #666;
            font-size: 16px;
        }
        .feature-list {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .feature-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .feature-icon {
            width: 40px;
            height: 40px;
            background: #2a5298;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
        }
        .feature-text h5 {
            margin: 0;
            color: #333;
            font-weight: 600;
        }
        .feature-text p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .btn-custom {
            flex: 1;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-login {
            background: #2a5298;
            color: white;
        }
        .btn-login:hover {
            background: #1e3c72;
            color: white;
            transform: translateY(-2px);
        }
        .btn-register {
            background: #fff;
            color: #2a5298;
            border: 2px solid #2a5298;
        }
        .btn-register:hover {
            background: #f0f5ff;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-container mx-auto">
            <div class="welcome-header">
                <h1>🌾 Urea Inventory Management</h1>
                <p>Professional Fertilizer Inventory Control System</p>
            </div>

            <div class="feature-list">
                <div class="feature-item">
                    <div class="feature-icon">📦</div>
                    <div class="feature-text">
                        <h5>Smart Inventory Tracking</h5>
                        <p>Real-time stock management across multiple warehouses</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">📊</div>
                    <div class="feature-text">
                        <h5>Advanced Analytics</h5>
                        <p>Comprehensive reports and insights for better decision-making</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">🔒</div>
                    <div class="feature-text">
                        <h5>Secure & Reliable</h5>
                        <p>Enterprise-grade security with role-based access control</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">🚚</div>
                    <div class="feature-text">
                        <h5>Purchase & Sales Management</h5>
                        <p>Complete workflow from ordering to delivery</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">💼</div>
                    <div class="feature-text">
                        <h5>Multi-Warehouse Support</h5>
                        <p>Manage inventory across multiple locations seamlessly</p>
                    </div>
                </div>
            </div>

            <div class="button-group">
                <a href="{{ route('login') }}" class="btn btn-custom btn-login">Login</a>
                <a href="{{ route('register') }}" class="btn btn-custom btn-register">Create Account</a>
            </div>

            <hr style="margin: 30px 0; color: #ddd;">
            
            <div style="text-align: center; color: #999; font-size: 14px;">
                <p>🔐 Secure • 🚀 Fast • 📈 Scalable</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
