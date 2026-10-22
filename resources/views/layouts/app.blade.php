<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ \App\Models\Setting::get('business_name', 'StockMaster Pro') }}</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #2563EB;
            --primary-hover: #1D4ED8;
            --sidebar-bg: #0F172A;
            --sidebar-hover: #1E293B;
            --sidebar-active: #3B82F6;
            --sidebar-text: #94A3B8;
            --sidebar-text-active: #FFFFFF;
            --bg-light: #F8FAFC;
            --card-border: #E2E8F0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-light);
            color: #1E293B;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Sidebar Styling */
        #sidebar-wrapper {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--sidebar-bg);
            transition: all 0.3s ease;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.06);
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            text-decoration: none;
            color: #FFFFFF;
        }

        .sidebar-brand-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #3B82F6, #1D4ED8);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #fff;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .sidebar-brand-text {
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .sidebar-nav {
            padding: 1rem 0.75rem;
            flex: 1;
            overflow-y: auto;
        }

        .sidebar-heading {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748B;
            padding: 0.75rem 0.75rem 0.35rem 0.75rem;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.85rem;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 2px;
        }

        .nav-link-custom i {
            font-size: 1.1rem;
            width: 22px;
            text-align: center;
        }

        .nav-link-custom:hover {
            color: var(--sidebar-text-active);
            background: var(--sidebar-hover);
        }

        .nav-link-custom.active {
            color: var(--sidebar-text-active);
            background: var(--primary-color);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .sidebar-user {
            padding: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #3B82F6;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Main Content Wrapper */
        #page-content-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* Top Navbar */
        .top-navbar {
            background: #FFFFFF;
            border-bottom: 1px solid var(--card-border);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        /* Card and Table styling */
        .card {
            border: 1px solid var(--card-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            background: #FFFFFF;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--card-border);
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        .table > :not(caption) > * > * {
            padding: 0.85rem 1rem;
            vertical-align: middle;
        }

        .table thead th {
            background-color: #F8FAFC;
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748B;
            border-bottom: 1px solid var(--card-border);
        }

        /* Subtle Badges */
        .badge-subtle-success {
            background-color: #DCFCE7;
            color: #166534;
            font-weight: 600;
        }
        .badge-subtle-warning {
            background-color: #FEF9C3;
            color: #854D0E;
            font-weight: 600;
        }
        .badge-subtle-danger {
            background-color: #FEE2E2;
            color: #991B1B;
            font-weight: 600;
        }
        .badge-subtle-primary {
            background-color: #DBEAFE;
            color: #1E40AF;
            font-weight: 600;
        }
        .badge-subtle-info {
            background-color: #E0F2FE;
            color: #075985;
            font-weight: 600;
        }
        .badge-subtle-secondary {
            background-color: #F1F5F9;
            color: #475569;
            font-weight: 600;
        }

        /* Responsive Mobile Toggle */
        @media (max-width: 991.98px) {
            #sidebar-wrapper {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            #sidebar-wrapper.toggled {
                margin-left: 0;
            }
            #page-content-wrapper {
                margin-left: 0;
            }
            .sidebar-backdrop {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(15, 23, 42, 0.5);
                z-index: 1030;
            }
            .sidebar-backdrop.show {
                display: block;
            }
        }

        /* Print styles */
        @media print {
            #sidebar-wrapper, .top-navbar, .no-print, .btn, .breadcrumb, footer {
                display: none !important;
            }
            #page-content-wrapper {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            body {
                background: #FFFFFF !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Mobile Sidebar Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- Sidebar Wrapper -->
    <aside id="sidebar-wrapper">
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div>
                <div class="sidebar-brand-text">{{ \App\Models\Setting::get('business_name', 'StockMaster Pro') }}</div>
                <small class="text-secondary" style="font-size: 0.7rem;">Inventory & Stock</small>
            </div>
        </a>

        <div class="sidebar-nav">
            <div class="sidebar-heading">Core</div>
            <a href="{{ route('dashboard') }}" class="nav-link-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>

            <div class="sidebar-heading">Inventory & Catalog</div>
            <a href="{{ route('products.index') }}" class="nav-link-custom {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <i class="bi bi-boxes"></i>
                <span>Products & Stock</span>
            </a>

            @if(Auth::user()->isAdmin())
            <a href="{{ route('categories.index') }}" class="nav-link-custom {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <i class="bi bi-tags-fill"></i>
                <span>Categories</span>
            </a>
            <a href="{{ route('suppliers.index') }}" class="nav-link-custom {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                <i class="bi bi-truck"></i>
                <span>Suppliers</span>
            </a>
            @endif

            <div class="sidebar-heading">Transactions</div>
            <a href="{{ route('sales.index') }}" class="nav-link-custom {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                <i class="bi bi-receipt-cutoff"></i>
                <span>Sales & POS</span>
            </a>

            @if(Auth::user()->isAdmin())
            <a href="{{ route('purchases.index') }}" class="nav-link-custom {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                <i class="bi bi-cart-check-fill"></i>
                <span>Purchases (Stock In)</span>
            </a>
            <a href="{{ route('adjustments.index') }}" class="nav-link-custom {{ request()->routeIs('adjustments.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-left-right"></i>
                <span>Stock Adjustments</span>
            </a>
            @endif

            <div class="sidebar-heading">Analytics & Reports</div>
            <a href="{{ route('reports.sales') }}" class="nav-link-custom {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i>
                <span>Sales Reports</span>
            </a>
            <a href="{{ route('reports.inventory') }}" class="nav-link-custom {{ request()->routeIs('reports.inventory') ? 'active' : '' }}">
                <i class="bi bi-pie-chart-fill"></i>
                <span>Stock Valuation</span>
            </a>
            <a href="{{ route('reports.movements') }}" class="nav-link-custom {{ request()->routeIs('reports.movements') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i>
                <span>Movement History</span>
            </a>
            <a href="{{ route('reports.best-selling') }}" class="nav-link-custom {{ request()->routeIs('reports.best-selling') ? 'active' : '' }}">
                <i class="bi bi-trophy-fill"></i>
                <span>Best Sellers</span>
            </a>
            @if(Auth::user()->isAdmin())
            <a href="{{ route('reports.purchases') }}" class="nav-link-custom {{ request()->routeIs('reports.purchases') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Purchase Reports</span>
            </a>
            @endif

            @if(Auth::user()->isAdmin())
            <div class="sidebar-heading">Administration</div>
            <a href="{{ route('users.index') }}" class="nav-link-custom {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i>
                <span>Staff & Roles</span>
            </a>
            <a href="{{ route('settings.index') }}" class="nav-link-custom {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear-fill"></i>
                <span>Business Settings</span>
            </a>
            @endif
        </div>

        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="overflow-hidden flex-grow-1">
                <div class="text-white text-truncate fw-semibold" style="font-size: 0.85rem;">{{ Auth::user()->name }}</div>
                <div class="d-flex align-items-center gap-1">
                    <span class="badge {{ Auth::user()->isAdmin() ? 'bg-primary' : 'bg-secondary' }}" style="font-size: 0.65rem;">
                        {{ strtoupper(Auth::user()->role) }}
                    </span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-1" title="Log Out">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Page Content Wrapper -->
    <div id="page-content-wrapper">
        <!-- Top Navbar -->
        <header class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light border d-lg-none" id="sidebarToggle" type="button">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-none d-sm-block">
                    <h5 class="mb-0 fw-bold">@yield('page_title', 'Dashboard')</h5>
                    <small class="text-muted">@yield('page_subtitle', 'Overview and key metrics')</small>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('sales.create') }}" class="btn btn-success btn-sm d-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-plus-circle"></i>
                    <span>New Sale / POS</span>
                </a>

                @if(Auth::user()->isAdmin())
                <a href="{{ route('purchases.create') }}" class="btn btn-outline-primary btn-sm d-none d-md-flex align-items-center gap-1">
                    <i class="bi bi-cart-plus"></i>
                    <span>New Purchase</span>
                </a>
                @endif

                <div class="dropdown ms-2">
                    <button class="btn btn-light btn-sm border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        <li>
                            <h6 class="dropdown-header">{{ Auth::user()->email }}</h6>
                        </li>
                        <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="bi bi-person me-2"></i>My Profile</a></li>
                        @if(Auth::user()->isAdmin())
                        <li><a class="dropdown-item" href="{{ route('settings.index') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Log Out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Body Content -->
        <main class="container-fluid p-4 flex-grow-1">
            <!-- Flash Notifications -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm border-0 mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <div class="flex-grow-1">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm border-0 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                <div class="flex-grow-1">{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm border-0 mb-4" role="alert">
                <i class="bi bi-info-circle-fill fs-5 text-info"></i>
                <div class="flex-grow-1">{{ session('info') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-x-circle-fill fs-5"></i>
                    <strong>Please review the following errors:</strong>
                </div>
                <ul class="mb-0 ps-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @yield('content')
        </main>

        <!-- Global Delete Confirmation Modal -->
        <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-danger d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill"></i> Confirm Action
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <p class="mb-0 text-muted" id="deleteModalMessage">Are you sure you want to proceed with this deletion? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <form id="deleteModalForm" method="POST" action="">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Confirm Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-white border-top py-3 px-4 text-center text-muted" style="font-size: 0.85rem;">
            &copy; {{ date('Y') }} {{ \App\Models\Setting::get('business_name', 'StockMaster Pro') }}. Built with Laravel 11 & Bootstrap 5.
        </footer>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js 4.4.2 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <script>
        // Sidebar Mobile Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar-wrapper');
        const backdrop = document.getElementById('sidebarBackdrop');

        if (sidebarToggle && sidebar && backdrop) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('toggled');
                backdrop.classList.toggle('show');
            });

            backdrop.addEventListener('click', () => {
                sidebar.classList.remove('toggled');
                backdrop.classList.remove('show');
            });
        }

        // Global Delete Confirmation Modal Handler
        function confirmDelete(actionUrl, message = null) {
            const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            const form = document.getElementById('deleteModalForm');
            const messageEl = document.getElementById('deleteModalMessage');

            form.action = actionUrl;
            if (message) {
                messageEl.textContent = message;
            } else {
                messageEl.textContent = 'Are you sure you want to proceed with this deletion? This action cannot be undone.';
            }

            modal.show();
        }
    </script>

    @stack('scripts')
</body>
</html>
