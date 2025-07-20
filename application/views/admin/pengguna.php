<?php $this->load->view('templates/header', [ 'title' => 'Pengguna' ]); ?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Manajemen Pengguna</h4>
        <a href="#" class="btn btn-primary" id="btnTambahPengguna"><i class="bi bi-plus"></i> Tambah Pengguna</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelPengguna" class="table table-bordered table-hover table-striped w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th style="width:120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Modal Tambah/Edit Pengguna -->
<div class="modal fade" id="modalPengguna" tabindex="-1" aria-labelledby="modalPenggunaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="formPengguna" method="post">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPenggunaLabel">Tambah Pengguna</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="pengguna_id">
        <div class="mb-3">
          <label class="form-label">Nama</label>
          <input type="text" class="form-control" name="name" id="pengguna_name" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" class="form-control" name="username" id="pengguna_username" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" name="password" id="pengguna_password">
          <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah password.</small>
        </div>
        <div class="mb-3">
          <label class="form-label">Role</label>
          <select class="form-select" name="role_id" id="pengguna_role_id" required>
            <option value="">Pilih Role</option>
            <option value="1">Admin</option>
            <option value="2">Kasir</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Status</label>
          <select class="form-select" name="is_active" id="pengguna_is_active" required>
            <option value="1">Aktif</option>
            <option value="0">Nonaktif</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="btnSimpanPengguna">Simpan</button>
      </div>
    </form>
  </div>
</div>
<!-- Modal Konfirmasi Hapus Pengguna -->
<div class="modal fade" id="modalHapusPengguna" tabindex="-1" aria-labelledby="modalHapusPenggunaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalHapusPenggunaLabel">Konfirmasi Hapus Pengguna</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menghapus pengguna <strong id="hapus_nama_pengguna"></strong>?</p>
        <input type="hidden" id="hapus_id_pengguna">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="btnKonfirmasiHapusPengguna">Hapus</button>
      </div>
    </div>
  </div>
</div>
<?php $this->load->view('templates/footer'); ?> 

<script>
$(document).ready(function() {
    var table = $('#tabelPengguna').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            "url": "<?php echo base_url('admin/pengguna/get_ajax_pengguna'); ?>",
            "type": "POST"
        },
        "columns": [
            { "data": "name" },
            { "data": "username" },
            { "data": "role" },
            { "data": "status" },
            { "data": "aksi" }
        ],
        "columnDefs": [
            { "targets": [4], "orderable": false },
        ],
    });

    $('#btnTambahPengguna').click(function() {
        $('#formPengguna')[0].reset();
        $('#pengguna_id').val('');
        $('#modalPenggunaLabel').text('Tambah Pengguna');
        $('#modalPengguna').modal('show');
    });

    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $.ajax({
            url: "<?php echo base_url('admin/pengguna/get_pengguna_by_id/'); ?>" + id,
            type: "GET",
            dataType: "JSON",
            success: function(data) {
                $('#pengguna_id').val(data.id);
                $('#pengguna_name').val(data.name);
                $('#pengguna_username').val(data.username);
                $('#pengguna_role_id').val(data.role_id);
                $('#pengguna_is_active').val(data.is_active);
                $('#modalPenggunaLabel').text('Edit Pengguna');
                $('#modalPengguna').modal('show');
            }
        });
    });

    $('#formPengguna').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var url = $('#pengguna_id').val() ? "<?php echo base_url('admin/pengguna/update'); ?>" : "<?php echo base_url('admin/pengguna/store'); ?>";
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            dataType: "JSON",
            success: function(data) {
                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    $('#modalPengguna').modal('hide');
                    table.ajax.reload();
                } else {
                    showToast(data.message, 'danger');
                }
            }
        });
    });

    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        $('#hapus_id_pengguna').val(id);
        $('#hapus_nama_pengguna').text(name);
        $('#modalHapusPengguna').modal('show');
    });

    $('#btnKonfirmasiHapusPengguna').click(function() {
        var id = $('#hapus_id_pengguna').val();
        $.ajax({
            url: "<?php echo base_url('admin/pengguna/destroy/'); ?>" + id,
            type: "POST",
            dataType: "JSON",
            success: function(data) {
                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    $('#modalHapusPengguna').modal('hide');
                    table.ajax.reload();
                } else {
                    showToast(data.message, 'danger');
                }
            }
        });
    });
});
</script> 