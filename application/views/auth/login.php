<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mama Bakery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            background: #fffbe8;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 370px;
            margin: 0 auto;
            border: 1px solid #f3cfcf;
            border-radius: 14px;
            background: #fff;
            padding: 2.2rem 2rem 1.5rem 2rem;
            box-shadow: 0 2px 16px 0 rgba(220,53,69,0.06);
        }
        .login-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .login-logo img.logo {
            max-width: 110px;
            margin-bottom: 0.5rem;
            border-radius: 50%;
            box-shadow: 0 2px 12px 0 rgba(184,134,11,0.13);
            background: #fffbe8;
            border: 2px solid #b8860b;
            padding: 8px;
        }
        .login-logo span {
            font-weight: 700;
            font-size: 1.35rem;
            color: #b8860b;
            letter-spacing: 1px;
        }
        .form-label {
            font-weight: 500;
            color: #b8860b;
        }
        .form-control {
            border-radius: 8px;
            padding: 0.7rem 1rem;
            font-size: 1rem;
        }
        .btn-primary {
            background: #b8860b;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            padding: 0.7rem 0;
            font-size: 1.08rem;
            letter-spacing: 0.5px;
        }
        .btn-primary:hover {
            background: #a0760a;
        }
        .input-group-text {
            background: #fff;
            border-left: 0;
            cursor: pointer;
        }
        .footer-text {
            text-align: center;
            color: #b8860b;
            font-size: 0.93rem;
            margin-top: 2.2rem;
        }
        @media (max-width: 480px) {
            .login-card { padding: 1.2rem 0.7rem 1.2rem 0.7rem; }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <img src="<?= base_url('assets/img/logo-mama-bakery.png') ?>" class="logo" alt="Logo Mama Bakery">
            <span>Mama Bakery</span>
        </div>
        <p class="text-center text-muted mb-4" style="font-size:1.01rem;">Silakan login untuk melanjutkan</p>
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success py-2" role="alert">
                <?= $this->session->flashdata('success') ?>
            </div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger py-2" role="alert">
                <?= $this->session->flashdata('error') ?>
            </div>
        <?php endif; ?>
        <?php echo form_open('auth/login', ['id' => 'loginForm', 'class' => 'needs-validation', 'novalidate' => '']); ?>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control <?= (form_error('username')) ? 'is-invalid' : '' ?>" id="username" name="username" value="<?= set_value('username') ?>" placeholder="Masukkan username" required autofocus>
                <?= form_error('username', '<div class="invalid-feedback">', '</div>') ?>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control <?= (form_error('password')) ? 'is-invalid' : '' ?>" id="password" name="password" placeholder="Masukkan password" required>
                    <span class="input-group-text" id="togglePassword"><i class="bi bi-eye"></i></span>
                </div>
                <?= form_error('password', '<div class="invalid-feedback">', '</div>') ?>
            </div>
            <div class="d-grid mb-2 mt-4">
                <button type="submit" class="btn btn-primary">Masuk <i class="bi bi-box-arrow-in-right ms-1"></i></button>
            </div>
        <?php echo form_close(); ?>
        <div class="footer-text">
            &copy; <?= date('Y') ?> Mama Bakery
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Show/hide password
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });
    }
    // Bootstrap validation
    (function () {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>
</body>
</html>
