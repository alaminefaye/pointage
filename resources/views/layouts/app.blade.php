<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('assets/') }}" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    <meta name="description" content="@yield('description', '')" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/favicon/favicon.svg') }}" />
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    
    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <!-- Custom Brand CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom-brand.css') }}" />
    
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    @stack('vendor-css')
    
    <!-- Page CSS -->
    @stack('page-css')
    
    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="{{ route('dashboard') }}" class="app-brand-link">
                        <div class="app-brand-logo demo" style="background-color: #074136; padding: 8px 12px; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                            <div style="text-align: center; color: white;">
                                <div style="font-family: 'Georgia', 'Times New Roman', serif; font-size: 18px; font-weight: bold; letter-spacing: 1px; line-height: 1.2;">GASPARD</div>
                                <div style="font-family: 'Arial', sans-serif; font-size: 9px; font-weight: normal; letter-spacing: 2px; margin-top: 2px; opacity: 0.95;">SIGNATURE</div>
                            </div>
                        </div>
                    </a>
                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                        <i class="bx bx-chevron-left bx-sm align-middle"></i>
                    </a>
                </div>
                
                <div class="menu-inner-shadow"></div>
                
                <ul class="menu-inner py-1">
                    <!-- Dashboard -->
                    <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <a href="{{ route('dashboard') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                            <div data-i18n="Analytics">Dashboard</div>
                        </a>
                    </li>
                    
                    <!-- Employees -->
                    <li class="menu-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                        <a href="{{ route('employees.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-user"></i>
                            <div data-i18n="Employees">Employés</div>
                        </a>
                    </li>
                    
                    <!-- Sites -->
                    <li class="menu-item {{ request()->routeIs('sites.*') ? 'active' : '' }}">
                        <a href="{{ route('sites.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-map"></i>
                            <div data-i18n="Sites">Sites</div>
                        </a>
                    </li>
                    
                    <!-- Departments -->
                    <li class="menu-item {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                        <a href="{{ route('departments.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-building"></i>
                            <div data-i18n="Departments">Départements</div>
                        </a>
                    </li>
                    
                    <!-- Today's Attendance -->
                    <li class="menu-item {{ request()->routeIs('attendance.today') ? 'active' : '' }}">
                        <a href="{{ route('attendance.today') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                            <div data-i18n="Today Attendance">Pointages du Jour</div>
                        </a>
                    </li>
                    
                    <!-- Attendance -->
                    <li class="menu-item {{ request()->routeIs('attendance.index') ? 'active' : '' }}">
                        <a href="{{ route('attendance.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-time"></i>
                            <div data-i18n="Attendance">Pointage</div>
                        </a>
                    </li>
                    
                    <!-- Overtime -->
                    <li class="menu-item {{ request()->routeIs('overtime.*') ? 'active' : '' }}">
                        <a href="{{ route('overtime.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-time-five"></i>
                            <div data-i18n="Overtime">Heures Supplémentaires</div>
                        </a>
                    </li>
                    
                    <!-- QR Code -->
                    <li class="menu-item {{ request()->routeIs('qr-code.*') ? 'active' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle" onclick="showQrCodeModal()">
                            <i class="menu-icon tf-icons bx bx-qr-scan"></i>
                            <div data-i18n="QR Code">QR Code</div>
                        </a>
                    </li>
                    
                    <!-- Badges -->
                    <li class="menu-item {{ request()->routeIs('badges.*') ? 'active' : '' }}">
                        <a href="{{ route('badges.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-id-card"></i>
                            <div data-i18n="Badges">Badges</div>
                        </a>
                    </li>
                    
                    <!-- Badge Scanner -->
                    <li class="menu-item {{ request()->routeIs('attendance.badge-scanner') ? 'active' : '' }}">
                        <a href="{{ route('attendance.badge-scanner') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-qr-scan"></i>
                            <div data-i18n="Scanner Badges">Scanner Badges</div>
                        </a>
                    </li>
                    
                    <!-- Reports -->
                    <li class="menu-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <a href="{{ route('reports.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-file"></i>
                            <div data-i18n="Reports">Rapports</div>
                        </a>
                    </li>
                    
                    <!-- Alerts -->
                    <li class="menu-item {{ request()->routeIs('alerts.*') ? 'active' : '' }}">
                        <a href="{{ route('alerts.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-bell"></i>
                            <div data-i18n="Alerts">Alertes</div>
                            @php
                                $unreadCount = \App\Models\Alert::where('is_read', false)->count();
                            @endphp
                            <span class="badge bg-label-danger ms-auto" id="unread-alerts-count" style="display: {{ $unreadCount > 0 ? 'inline-block' : 'none' }};">{{ $unreadCount }}</span>
                        </a>
                    </li>
                    
                    <!-- Users (Administration) -->
                    <li class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-user-circle"></i>
                            <div data-i18n="Users">Utilisateurs</div>
                        </a>
                    </li>
                    
                    <!-- Settings -->
                    <li class="menu-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <a href="{{ route('settings.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-cog"></i>
                            <div data-i18n="Settings">Paramètres</div>
                        </a>
                    </li>
                    
                    @yield('menu-items')
                </ul>
            </aside>
            <!-- / Menu -->
            
            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                            <i class="bx bx-menu bx-sm"></i>
                        </a>
                    </div>
                    
                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                        <!-- Search -->
                        <div class="navbar-nav align-items-center">
                            <div class="nav-item d-flex align-items-center">
                                <i class="bx bx-search fs-4 lh-0"></i>
                                <input type="text" class="form-control border-0 shadow-none" placeholder="Search..." aria-label="Search..." />
                            </div>
                        </div>
                        <!-- /Search -->
                        
                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <!-- User -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar avatar-online">
                                                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <span class="fw-semibold d-block">{{ Auth::check() ? Auth::user()->name : 'User' }}</span>
                                                    <small class="text-muted">Admin</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <i class="bx bx-user me-2"></i>
                                            <span class="align-middle">My Profile</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <i class="bx bx-cog me-2"></i>
                                            <span class="align-middle">Settings</span>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="bx bx-power-off me-2"></i>
                                                <span class="align-middle">Log Out</span>
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                            <!--/ User -->
                        </ul>
                    </div>
                </nav>
                <!-- / Navbar -->
                
                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @yield('content')
                    </div>
                    <!-- / Content -->
                    
                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                            <div class="mb-2 mb-md-0">
                                © <script>document.write(new Date().getFullYear());</script>, made with ❤️ by
                                <a href="https://themeselection.com" target="_blank" class="footer-link fw-bolder">ThemeSelection</a>
                            </div>
                            <div>
                                <a href="https://themeselection.com/license/" class="footer-link me-4" target="_blank">License</a>
                                <a href="https://themeselection.com/" target="_blank" class="footer-link me-4">More Themes</a>
                                <a href="https://themeselection.com/demo/sneat-bootstrap-html-admin-template/documentation/" target="_blank" class="footer-link me-4">Documentation</a>
                                <a href="https://github.com/themeselection/sneat-html-admin-template-free/issues" target="_blank" class="footer-link me-4">Support</a>
                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->
                    
                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>
        
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->
    
    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    
    <!-- Vendors JS -->
    @stack('vendor-js')
    
    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    
    <!-- Page JS -->
    @stack('page-js')
    
    <!-- Update unread alerts count -->
    <script>
    // Update unread alerts count
    async function updateUnreadAlerts() {
        try {
            const response = await fetch('{{ route("alerts.unread-count") }}');
            const data = await response.json();
            const countEl = document.getElementById('unread-alerts-count');
            if (countEl) {
                countEl.textContent = data.count;
                if (data.count > 0) {
                    countEl.style.display = 'inline-block';
                } else {
                    countEl.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Error updating alerts:', error);
        }
    }

    // Update on page load
    updateUnreadAlerts();
    
    // Update every minute
    setInterval(updateUnreadAlerts, 60000);
    </script>
</body>
</html>

