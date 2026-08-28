<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Page Not Found | Urea Inventory Management</title>
    <link rel="icon" href="{{ \App\Helpers\CompanyHelper::getFaviconUrl() }}" sizes="any">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #27ae60;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
            --border-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-container {
            text-align: center;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 60px 40px;
            max-width: 600px;
            width: 90%;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-code {
            font-size: 120px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
            line-height: 1;
        }

        .error-icon {
            font-size: 80px;
            color: var(--danger-color);
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .error-title {
            font-size: 32px;
            font-weight: 600;
            color: var(--primary-color);
            margin: 20px 0 15px;
        }

        .error-description {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .error-text {
            font-size: 14px;
            color: #999;
            margin-bottom: 40px;
            font-style: italic;
        }

        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary-error {
            background: var(--secondary-color);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-primary-error:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
            color: white;
        }

        .btn-secondary-error {
            background: transparent;
            color: var(--secondary-color);
            padding: 12px 30px;
            border: 2px solid var(--secondary-color);
            border-radius: var(--border-radius);
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-secondary-error:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .error-details {
            background: #f8f9fa;
            border-left: 4px solid var(--danger-color);
            padding: 15px;
            border-radius: 4px;
            text-align: left;
            margin: 30px 0;
            font-size: 13px;
            color: #555;
        }

        .error-details strong {
            display: block;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .navbar {
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            padding: 15px 0;
            margin-bottom: 30px;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: 20px;
        }

        .navbar-brand i {
            font-size: 1.5rem;
            color: var(--secondary-color);
        }

        body.with-navbar {
            padding-top: 80px;
        }

        /* Animation for error icon */
        .error-icon {
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .error-container {
                padding: 40px 25px;
            }

            .error-code {
                font-size: 80px;
            }

            .error-icon {
                font-size: 60px;
            }

            .error-title {
                font-size: 24px;
            }

            .error-description {
                font-size: 14px;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn-primary-error,
            .btn-secondary-error {
                width: 100%;
            }

            .navbar {
                margin-bottom: 0;
            }

            body.with-navbar {
                padding-top: 70px;
            }
        }

        @media (max-width: 480px) {
            .error-container {
                padding: 30px 15px;
                width: 95%;
            }

            .error-code {
                font-size: 60px;
            }

            .error-icon {
                font-size: 50px;
            }

            .error-title {
                font-size: 20px;
            }

            .error-description {
                font-size: 13px;
            }

            .btn-primary-error,
            .btn-secondary-error {
                padding: 10px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body class="with-navbar">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container-lg">
            <a class="navbar-brand" href="/">
                <i class="bi bi-box-seam"></i>
                Urea Management
            </a>
        </div>
    </nav>

    <!-- Error Container -->
    <div class="error-container">
        <!-- Error Icon -->
        <div class="error-icon">
            <i class="bi bi-exclamation-triangle"></i>
        </div>

        <!-- Error Code -->
        <h1 class="error-code">404</h1>

        <!-- Error Title -->
        <h2 class="error-title">Page Not Found</h2>

        <!-- Error Description -->
        <p class="error-description">
            Sorry, the page you're looking for doesn't exist or has been moved.
        </p>

        <!-- Error Details -->
        <div class="error-details">
            <strong>What happened?</strong>
            The URL you entered may be incorrect, or the page may have been removed. Please check the URL and try again.
        </div>

        <!-- Error Text -->
        <p class="error-text">
            Error Code: 404 | Page Not Found
        </p>

        <!-- Action Buttons -->
        <div class="btn-group">
            <a href="/" class="btn-primary-error">
                <i class="bi bi-house me-2"></i>Back to Home
            </a>
            <a href="javascript:history.back()" class="btn-secondary-error">
                <i class="bi bi-arrow-left me-2"></i>Go Back
            </a>
        </div>

        <!-- Additional Help -->
        <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid #eee;">
            <p style="font-size: 13px; color: #999; margin: 0;">
                Need help? <a href="/" style="color: var(--secondary-color); text-decoration: none; font-weight: 600;">Return to homepage</a> or contact support.
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
