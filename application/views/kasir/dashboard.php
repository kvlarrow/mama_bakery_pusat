<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3>Selamat datang, <?php echo htmlspecialchars($user['name']); ?>!</h3>
            <p>Ini adalah dashboard kasir. Silakan mulai transaksi penjualan.</p>
        </div>
    </div>
</div>
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
  <div id="toastLogin" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body"></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showLoginToast(msg, type = 'success') {
    var $toast = $('#toastLogin');
    $toast.removeClass('bg-success bg-danger').addClass('bg-' + type);
    $toast.find('.toast-body').html(msg);
    var toast = new bootstrap.Toast($toast[0]);
    toast.show();
}
<?php if ($this->session->flashdata('login_success')): ?>
$(document).ready(function() {
    showLoginToast('<?= $this->session->flashdata('login_success') ?>', 'success');
});
<?php endif; ?>
</script> 