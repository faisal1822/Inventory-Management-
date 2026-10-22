<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') - {{ \App\Models\Setting::get('business_name', 'StockMaster Pro') }}</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #1E293B;
        }

        .auth-card {
            background: #FFFFFF;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .auth-header {
            padding: 2.25rem 2rem 1.25rem 2rem;
            text-align: center;
        }

        .auth-logo {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #3B82F6, #1D4ED8);
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.75rem;
            box-shadow: 0 8px 16px rgba(59, 130, 246, 0.35);
            margin-bottom: 1rem;
        }

        .auth-body {
            padding: 1rem 2rem 2.25rem 2rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border-color: #E2E8F0;
        }

        .form-control:focus {
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .btn-primary-custom {
            background: #2563EB;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            color: #fff;
            width: 100%;
            transition: all 0.2s ease;
        }

        .btn-primary-custom:hover {
            background: #1D4ED8;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            color: #fff;
        }
    </style>
</head>
<body>
    @yield('content')

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
