<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Mama Bakery' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .sidebar {
            min-width: 220px;
            max-width: 240px;
            height: 100vh;
            background: #343a40;
            color: white;
            overflow-y: auto; /* Tambahkan ini */
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: 0.75rem 1rem;
            margin: 0.25rem 0;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,.1);
            border-radius: 5px;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .main-content {
            background: #f8f9fa;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .navbar {
            flex-shrink: 0;
        }
        .navbar-brand {
            font-weight: 700;
            color: #e74c3c !important;
        }
        @media (max-width: 768px) {
            .sidebar { min-width: 100px; max-width: 100px; font-size: 0.95rem; }
            .main-content { padding: 1rem; }
        }
    </style>
</head>
<body>
    <div class="d-flex" style="min-height:100vh;">
        <!-- Sidebar -->
        <nav class="sidebar d-flex flex-column">
            <div class="p-2 text-center">
                <img src="<?php echo base_url(); ?>assets/img/logo-mama-bakery.png" alt="Mama Bakery" width="100%">
            </div>
            <ul class="nav flex-column px-3">
                <?php if($this->session->userdata('role') === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos(current_url(), 'admin/dashboard') !== FALSE ? 'active' : '' ?>" href="<?= site_url('admin/dashboard') ?>">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos(current_url(), 'admin/produk') !== FALSE ? 'active' : '' ?>" href="<?= site_url('admin/produk') ?>">
                            <i class="bi bi-box-seam"></i> Produk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos(current_url(), 'admin/kategori') !== FALSE ? 'active' : '' ?>" href="<?= site_url('admin/kategori') ?>">
                            <i class="bi bi-tags"></i> Kategori
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos(current_url(), 'admin/pengguna') !== FALSE ? 'active' : '' ?>" href="<?= site_url('admin/pengguna') ?>">
                            <i class="bi bi-people"></i> Pengguna
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos(current_url(), 'admin/laporan') !== FALSE ? 'active' : '' ?>" href="<?= site_url('admin/laporan') ?>">
                            <i class="bi bi-graph-up"></i> Laporan
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos(current_url(), 'kasir/transaksi') !== FALSE ? 'active' : '' ?>" href="<?= site_url('kasir/transaksi') ?>">
                            <i class="bi bi-cart"></i> Transaksi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos(current_url(), 'kasir/riwayat') !== FALSE ? 'active' : '' ?>" href="<?= site_url('kasir/kasir/riwayat') ?>">
                            <i class="bi bi-clock-history"></i> Riwayat
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item mt-4">
                    <a class="nav-link text-danger logout-trigger" href="<?= site_url('auth/logout') ?>">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
            
        </nav>
        <!-- Main Content -->
        <main class="main-content flex-grow-1" style="margin-left: 0;">
            <nav class="navbar navbar-expand-lg navbar-light bg-white rounded-3 mb-4 shadow-sm">
                <div class="container-fluid">
                    <span class="navbar-brand"><?= $title ?? 'Dashboard' ?></span>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-outline-secondary me-2" id="fullscreenToggle">
                            <i class="bi bi-arrows-fullscreen"></i>
                        </button>
                        <span class="me-3"><?= $this->session->userdata('name') ?></span>
                        <div class="dropdown">
                            <button class="btn btn-light rounded-circle p-2" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-fill"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="#">Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger logout-trigger" href="<?= site_url('auth/logout') ?>">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

<!-- Modal Konfirmasi Logout -->
<div class="modal fade" id="modalLogout" tabindex="-1" aria-labelledby="modalLogoutLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLogoutLabel">Konfirmasi Logout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Apakah Anda yakin ingin logout?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <a href="<?= site_url('auth/logout') ?>" class="btn btn-danger" id="btnConfirmLogout">Logout</a>
      </div>
    </div>
  </div>
</div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Handle logout confirmation modal
                var logoutModal = new bootstrap.Modal(document.getElementById('modalLogout'));
                document.querySelectorAll('.logout-trigger').forEach(item => {
                    item.addEventListener('click', function (event) {
                        event.preventDefault();
                        logoutModal.show();
                    });
                });

                // Fullscreen toggle functionality
                const fullscreenToggle = document.getElementById('fullscreenToggle');
                const fullscreenIcon = fullscreenToggle.querySelector('i');

                fullscreenToggle.addEventListener('click', function() {
                    if (document.fullscreenElement) {
                        document.exitFullscreen();
                    } else {
                        document.documentElement.requestFullscreen();
                    }
                });

                document.addEventListener('fullscreenchange', function() {
                    const sidebar = document.querySelector('.sidebar');
                    if (document.fullscreenElement) {
                        fullscreenIcon.classList.remove('bi-arrows-fullscreen');
                        fullscreenIcon.classList.add('bi-fullscreen-exit');
                        if (sidebar) {
                            sidebar.classList.add('d-none');
                        }
                    } else {
                        fullscreenIcon.classList.remove('bi-fullscreen-exit');
                        fullscreenIcon.classList.add('bi-arrows-fullscreen');
                        if (sidebar) {
                            sidebar.classList.remove('d-none');
                        }
                    }
                });

                // Optional: Adjust main content margin based on sidebar width for mobile
                // This part might need more sophisticated JS if the sidebar is collapsible.
                // For now, assuming it's a fixed sidebar.
            });
        </script>
    </body>
</html>
