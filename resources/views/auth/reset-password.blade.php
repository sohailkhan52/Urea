<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Reset Password - {{ config('app.name', 'Fertilizer Management System') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <!-- Bootstrap CSS -->
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

        .reset-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }

        .reset-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .reset-header h4 {
            margin: 0 0 10px;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .reset-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .reset-body {
            padding: 40px 30px;
        }

        .form-control {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        .btn-submit {
            padding: 12px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: transform 0.2s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .alert {
            border: none;
            border-radius: 8px;
        }

        .password-requirements {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <div class="reset-header">
            <h4><i class="bi bi-shield-lock me-2"></i>Reset Password</h4>
            <p>Enter your new password</p>
        </div>

        <div class="reset-body">
            @if($errors->any())
                <div class="alert alert-danger mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    @if(count($errors) == 1)
                        {{ $errors->first() }}
                    @else
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email" 
                               class="form-control border-start-0 @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $email) }}" 
                               required 
                               autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" 
                               class="form-control border-start-0 @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               required>
                    </div>
                    <div class="password-requirements">
                        <i class="bi bi-info-circle me-1"></i>
                        Password must be at least 8 characters
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                        <input type="password" 
                               class="form-control border-start-0" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-submit w-100">
                    <i class="bi bi-check-circle me-2"></i>
                    Reset Password
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
