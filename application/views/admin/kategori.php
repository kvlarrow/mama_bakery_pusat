<?php $this->load->view('templates/header', [ 'title' => 'Kategori' ]); ?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Manajemen Kategori</h4>
        <a href="#" class="btn btn-primary" id="btnTambahKategori"><i class="bi bi-plus"></i> Tambah Kategori</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelKategori" class="table table-bordered table-hover table-striped w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Kategori</th>
                            <th style="width:120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Modal Tambah/Edit Kategori -->
<div class="modal fade" id="modalKategori" tabindex="-1" aria-labelledby="modalKategoriLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="formKategori" method="post">
      <div class="modal-header">
        <h5 class="modal-title" id="modalKategoriLabel">Tambah Kategori</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="kategori_id">
        <div class="mb-3">
          <label class="form-label">Nama Kategori</label>
          <input type="text" class="form-control" name="name" id="kategori_name" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="btnSimpanKategori">Simpan</button>
      </div>
    </form>
  </div>
</div>
<!-- Modal Konfirmasi Hapus Kategori -->
<div class="modal fade" id="modalHapusKategori" tabindex="-1" aria-labelledby="modalHapusKategoriLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalHapusKategoriLabel">Konfirmasi Hapus Kategori</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menghapus kategori <strong id="hapus_nama_kategori"></strong>?</p>
        <input type="hidden" id="hapus_id_kategori">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="btnKonfirmasiHapusKategori">Hapus</button>
      </div>
    </div>
  </div>
</div>
<?php $this->load->view('templates/footer'); ?> 