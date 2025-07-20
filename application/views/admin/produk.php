<div class="container-fluid py-4">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Produk</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahProduk"><i class="bi bi-plus"></i> Tambah Produk</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelProduk" class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Kategori</th>
                            <th>Foto</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Produk -->
<div class="modal fade" id="modalTambahProduk" tabindex="-1" aria-labelledby="modalTambahProdukLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="formTambahProduk" method="post">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTambahProdukLabel">Tambah Produk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nama Produk</label>
          <input type="text" class="form-control" name="name" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Kategori</label>
          <select class="form-select" name="category_id" required>
            <option value="">Pilih Kategori</option>
            <?php if(isset($categories)) foreach($categories as $cat): ?>
              <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Harga</label>
          <input type="number" class="form-control" name="price" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Stok</label>
          <input type="number" class="form-control" name="stock" required>
        </div>
        <div class="mb-3">
            <label for="productPhoto" class="form-label">Foto Produk (Opsional)</label>
            <input class="form-control" type="file" id="productPhoto" name="photo">
            <small class="form-text text-muted">Ukuran maksimal 500KB, format JPG/PNG/JPEG.</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit Produk -->
<div class="modal fade" id="modalEditProduk" tabindex="-1" aria-labelledby="modalEditProdukLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="formEditProduk" method="post">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditProdukLabel">Edit Produk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="edit_id">
        <div class="mb-3">
          <label class="form-label">Nama Produk</label>
          <input type="text" class="form-control" name="name" id="edit_name" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Kategori</label>
          <select class="form-select" name="category_id" id="edit_category_id" required>
            <option value="">Pilih Kategori</option>
            <?php if(isset($categories)) foreach($categories as $cat): ?>
              <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Harga</label>
          <input type="number" class="form-control" name="price" id="edit_price" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Stok</label>
          <input type="number" class="form-control" name="stock" id="edit_stock" required>
        </div>
        <div class="mb-3">
            <label for="edit_productPhoto" class="form-label">Foto Produk (Biarkan kosong jika tidak ingin mengubah)</label>
            <input class="form-control" type="file" id="edit_productPhoto" name="photo">
            <small class="form-text text-muted">Ukuran maksimal 500KB, format JPG/PNG/JPEG.</small>
            <div id="current_photo_preview" class="mt-2"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Konfirmasi Hapus Produk -->
<div class="modal fade" id="modalHapusProduk" tabindex="-1" aria-labelledby="modalHapusProdukLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalHapusProdukLabel">Konfirmasi Hapus Produk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menghapus produk <strong id="hapus_nama_produk"></strong>?</p>
        <input type="hidden" id="hapus_id_produk">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="btnKonfirmasiHapus">Hapus</button>
      </div>
    </div>
  </div>
</div> 