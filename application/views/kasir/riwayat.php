<div class="container-fluid">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Riwayat Transaksi</h5>
            <form class="d-flex align-items-center" id="formFilterTanggal">
                <input type="date" name="tanggal" class="form-control form-control-sm me-2" value="<?= htmlspecialchars($tanggal ?? '') ?>">
                <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-filter"></i> Filter</button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelRiwayat" class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Invoice</th>
                            <th>Total</th>
                            <th>Metode</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate this -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Transaksi -->
<div class="modal fade" id="modalDetailTransaksi" tabindex="-1" aria-labelledby="modalDetailTransaksiLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalDetailTransaksiLabel">Detail Transaksi <span id="detailInvoiceCode"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="mb-1"><strong>Tanggal:</strong> <span id="detailTanggal"></span></p>
            <p class="mb-1"><strong>Kasir:</strong> <span id="detailKasir"></span></p>
          </div>
          <div class="col-md-6">
            <p class="mb-1"><strong>Metode Pembayaran:</strong> <span id="detailMetodePembayaran"></span></p>
            <p class="mb-1"><strong>Total Belanja:</strong> <span id="detailTotalBelanja"></span></p>
          </div>
        </div>
        <h6>Item Transaksi:</h6>
        <div class="table-responsive">
          <table class="table table-bordered table-striped mb-0">
            <thead>
              <tr>
                <th>Produk</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Harga Satuan</th>
                <th class="text-end">Subtotal</th>
              </tr>
            </thead>
            <tbody id="detailItemsTableBody">
              <!-- Items will be loaded here via JavaScript -->
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <p class="mb-0"><strong>Bayar:</strong> <span id="detailBayar"></span></p>
            <p class="mb-0"><strong>Kembalian:</strong> <span id="detailKembalian"></span></p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div> 