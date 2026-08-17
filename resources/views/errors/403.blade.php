<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Access Denied</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-container {
            text-align: center;
            background: white;
            padding: 60px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
        }
        .error-icon {
            font-size: 100px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        .error-code {
            font-size: 72px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .error-title {
            font-size: 28px;
            font-weight: 600;
            color: #34495e;
            margin-bottom: 15px;
        }
        .error-message {
            color: #7f8c8d;
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .btn-home {
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="bi bi-shield-lock"></i>
        </div>
        <div class="error-code">403</div>
        <div class="error-title">Access Denied</div>
        <div class="error-message">
            {{ $exception->getMessage() ?: 'You do not have permission to access this resource.' }}
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn-home">
            <i class="bi bi-house-door me-2"></i>
            Go to Dashboard
        </a>
    </div>
</body>
</html>
