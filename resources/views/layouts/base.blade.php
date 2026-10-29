<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-menu-color="dark" data-topbar-color="light">
<head>
    <meta charset="utf-8" />
    <title>StockGX</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="GESTION-USP" name="description" />
    <meta content="MyraStudio" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ url('assets/images/logo-sm.png') }}">

    <!-- Vendor CSS -->
    <link href="{{ url('assets/libs/morris.js/morris.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ url('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
    <link href="{{ url('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}" rel="stylesheet" />
    <link href="{{ url('assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}" rel="stylesheet" />
    <link href="{{ url('assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}" rel="stylesheet" />

    <!-- App CSS -->
    <link href="{{ url('assets/css/style.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ url('assets/css/style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ url('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    {{-- Modern Design Override --}}
    <link href="{{ url('assets/css/modern-override.css') }}?v={{ filemtime(public_path('assets/css/modern-override.css')) }}" rel="stylesheet" type="text/css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- App Config -->
    <script src="{{ url('assets/js/config.js') }}"></script>
    <script>
        (function() {
            try {
                var savedTheme = localStorage.getItem('themeMode') || sessionStorage.getItem('themeMode');
                var mode = savedTheme === 'dark' ? 'dark' : 'light';
                document.documentElement.setAttribute('data-bs-theme', mode);
                document.documentElement.setAttribute('data-topbar-color', mode === 'dark' ? 'dark' : 'light');
                if (typeof Chart !== 'undefined') {
                    Chart.defaults.color = mode === 'dark' ? '#cbd5e1' : '#64748b';
                    Chart.defaults.borderColor = mode === 'dark' ? 'rgba(148, 163, 184, 0.12)' : 'rgba(0, 0, 0, 0.06)';
                }
            } catch (e) {}
        })();
    </script>
</head>
<body>
    <div class="layout-wrapper">
        @include('layouts.sidebar')
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <div class="page-content">
            @include('layouts.top-bar')

            <div class="main-content-container py-3 px-2 px-md-3 flex-grow-1">
                <div class="container-fluid px-1 px-md-2">
                    @if (session('success'))
                        <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                            <i class="fas fa-check-circle"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>

            <footer class="footer mt-auto">
                <div class="container-fluid px-3 px-md-4">
                    <div class="row align-items-center">
                        <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                            <div>
                                <script>document.write(new Date().getFullYear())</script> © StockGX
                            </div>
                        </div>
                        <div class="col-md-6 text-center text-md-end">
                            <div class="d-flex justify-content-center justify-content-md-end gap-2">
                                <p class="mb-0">Designé & développé par <a href="https://www.attouco.com/" target="_blank" class="fw-bold">Attouco</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- JS Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ url('assets/js/vendor.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.btn-delete').on('click', function () {
                const formId = $(this).data('form-id');
                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Cette action est irréversible !",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#1a237e',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: 'Oui, supprimer !',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#' + formId).submit();
                    }
                });
            });
        });
    </script>
    <script src="{{ url('assets/libs/jquery-sparkline/jquery.sparkline.min.js') }}"></script>
    <script src="{{ url('assets/libs/jquery-knob/jquery.knob.min.js') }}"></script>
    <script src="{{ url('assets/libs/morris.js/morris.min.js') }}"></script>
    <script src="{{ url('assets/libs/raphael/raphael.min.js') }}"></script>
    <script src="{{ url('assets/js/app.js') }}?v={{ filemtime(public_path('assets/js/app.js')) }}"></script>
    <script src="{{ url('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ url('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ url('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ url('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ url('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ url('assets/libs/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js') }}"></script>
    <script src="{{ url('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ url('assets/libs/datatables.net-buttons/js/buttons.flash.min.js') }}"></script>
    <script src="{{ url('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ url('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ url('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
    <script src="{{ url('assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ url('assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>

    <script>
        // Ensure dropdowns and responsive sidebar work flawlessly
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Dropdowns
            try {
                if (typeof bootstrap !== 'undefined') {
                    var dropdownElements = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
                    dropdownElements.map(function (dropdownToggleEl) {
                        new bootstrap.Dropdown(dropdownToggleEl);
                    });
                }
            } catch (e) {
                console.error('Dropdown init error:', e);
            }

            // 2. Responsive Mobile Sidebar
            const toggleBtns = document.querySelectorAll('.button-toggle-menu');
            const closeBtn = document.getElementById('sidebarCloseBtn');
            const backdrop = document.getElementById('sidebarBackdrop');
            const htmlEl = document.documentElement;
            const bodyEl = document.body;

            function openSidebar() {
                htmlEl.classList.add('sidebar-enable');
                bodyEl.classList.add('sidebar-enable');
            }

            function closeSidebar() {
                htmlEl.classList.remove('sidebar-enable');
                bodyEl.classList.remove('sidebar-enable');
                const customBackdrop = document.getElementById('custom-backdrop');
                if (customBackdrop) {
                    customBackdrop.remove();
                }
            }

            function toggleSidebar() {
                if (window.innerWidth < 992) {
                    if (htmlEl.classList.contains('sidebar-enable')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                }
            }

            toggleBtns.forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleSidebar();
                });
            });

            if (closeBtn) {
                closeBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    closeSidebar();
                });
            }

            if (backdrop) {
                backdrop.addEventListener('click', function () {
                    closeSidebar();
                });
            }

            // Close with Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && htmlEl.classList.contains('sidebar-enable')) {
                    closeSidebar();
                }
            });

            // Cleanly handle window resize across responsive breakpoints
            let lastWindowWidth = window.innerWidth;
            window.addEventListener('resize', function () {
                const currentWidth = window.innerWidth;
                // If crossing the 992px breakpoint in either direction, always reset sidebar state
                if ((lastWindowWidth < 992 && currentWidth >= 992) || (lastWindowWidth >= 992 && currentWidth < 992)) {
                    closeSidebar();
                }
                lastWindowWidth = currentWidth;
            });

            // Close mobile sidebar when clicking a direct menu link (not a collapse trigger)
            const menuLinks = document.querySelectorAll('.app-menu .menu-link:not([data-bs-toggle="collapse"])');
            menuLinks.forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 992) {
                        closeSidebar();
                    }
                });
            });

            // 3. Client-side form validation handler (Bootstrap 5)
            const validationForms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(validationForms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        });
    </script>
    @yield('scripts')
</body>
</html>
