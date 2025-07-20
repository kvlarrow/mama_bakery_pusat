<div class="container-fluid d-flex flex-column h-100">
    <div class="row flex-grow-1 align-items-stretch">
        <!-- Daftar Produk -->
        <div class="col-md-9 d-flex flex-column">
            <div class="card shadow-sm flex-grow-1">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-normal">Daftar Produk</h5>
                    <div class="d-flex align-items-center">
                        <div class="me-2 d-flex align-items-center">
                            <button class="btn btn-outline-primary btn-sm me-1" id="btnRefreshProducts" title="Refresh stok produk">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                            <small class="text-muted" id="lastUpdateTime" title="Terakhir update"></small>
                        </div>
                        <form class="d-flex align-items-center" id="formCariProduk">
                            <input class="form-control form-control-sm me-2" type="search" placeholder="Cari produk / scan barcode..." aria-label="Search" id="inputCariProduk">
                            <button class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </form>
                    </div>
                </div>
                <div class="card-body py-3 overflow-auto">
                    <div class="row g-3 mb-2">
                    <?php foreach($products as $product): ?>
                        <div class="col-lg-4 col-md-6 col-12 mb-4">
                            <div class="card h-100 product-card-modern border-0 shadow-sm position-relative" data-id="<?= $product->id ?>" data-price="<?= $product->price ?>" data-stock="<?= $product->stock ?>">
                                <div class="card-body d-flex flex-column align-items-center justify-content-start p-3">
                                    <div class="product-image-modern mb-3 position-relative">
                                        <img src="<?= $product->photo ? base_url('uploads/products/'.$product->photo) : 'https://placehold.co/160x160?text='.urlencode($product->name) ?>" class="product-image-fill-modern rounded shadow-sm" alt="<?= htmlspecialchars($product->name) ?>">
                                        <button class="btn btn-primary btn-add-modern position-absolute" style="bottom:10px; right:10px;" title="Tambah ke keranjang" <?= (isset($product->stock) && $product->stock <= 0) ? 'disabled' : '' ?>>
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                    <h6 class="card-title text-center fw-semibold mb-1" style="font-size:1.08rem;line-height:1.2;min-height:2.2em;overflow:hidden;"><?= htmlspecialchars($product->name) ?></h6>
                                    <div class="mb-2 text-center text-muted fw-light" style="font-size:1.01rem;">Rp.<?= number_format($product->price, 0, ',', '.') ?></div>
                                    <div class="mb-1">
                                        <?php if (isset($product->stock)): ?>
                                            <?php if ($product->stock > 0): ?>
                                                <span class="badge bg-success">Tersedia</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Stok Habis</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="col-lg-4 col-md-6 col-12 d-none" id="dummyProdukFlex"></div>
                </div>
            </div>
        </div>
    </div>
        <!-- Keranjang Transaksi -->
        <div class="col-md-3 d-flex flex-column">
            <button class="btn btn-outline-primary mb-2 w-100" id="btnLihatDraft" type="button">
                <i class="bi bi-archive"></i> Lihat Draft
            </button>
            <div class="card shadow-sm d-flex flex-column flex-grow-1">
                <div class="card-header bg-white d-flex align-items-center">
                    <h5 class="mb-0">Keranjang Transaksi</h5>
            </div>
                <div class="card-body p-0 d-flex flex-column overflow-hidden">
                    <div class="overflow-y-auto flex-grow-1">
                        <table class="table align-middle mb-0 table-cart">
                            <thead>
                            <tr>
                                <th class="text-start fs-sm">Produk</th>
                                <th class="text-center fs-sm">Qty</th>
                                <th class="text-end fs-sm">Subtotal</th>
                                <th class="w-48px"></th>
                            </tr>
                        </thead>
                            <tbody>
                                <!-- Akan diisi oleh JavaScript -->
                        </tbody>
                    </table>
                    </div>
                    </div>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold fs-5 text-primary">Rp 0</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary w-50" disabled id="btnSimpanDraft">Simpan Draft</button>
                        <button class="btn btn-success w-50" disabled>Bayar <i class="bi bi-cash-coin ms-1"></i></button>
                    </div>
                </div>
            </div>
            <!-- Template Struk (hidden, siap print) -->
            <div id="strukArea" style="display:none;">
              <div id="strukPrint" style="width:210px; font-family:monospace; margin:0; padding:0;">
                <div style="text-align:center;">
                  <img src="<?= base_url('assets/img/logo-mama-bakery-grayscale.png') ?>" alt="Logo Mama Bakery" style="width:120px; max-width:100%; margin-bottom:4px; background:#fff; padding:2px; border-radius:6px; filter: brightness(1.2) drop-shadow(0 0 2px #fff);">
                  <div style="font-size:1.1em;font-weight:bold;">Mama Modern Bakery & Cafe</div>
                  <div style="font-size:0.95em;">Bandara Internasional Kalimarau Berau</div>
                  <div style="font-size:0.95em;">Telp: 081347576996, 08115441993</div>
                  <div style="font-size:0.95em;">IG: mamabakery_berau</div>
                  <hr style="margin:6px 0;">
                </div>
                <div style="font-size:0.95em;">
                  <div><b>Invoice:</b> <span id="strukInvoice"></span></div>
                  <div><b>Tanggal:</b> <span id="strukTanggal"></span></div>
                  <div><b>Kasir:</b> <span id="strukKasir"></span></div>
                  <div><b>Metode:</b> <span id="strukMetode"></span></div>
                </div>
                <hr style="margin:6px 0;">
                <table style="width:100%;font-size:0.95em;">
                  <thead>
                    <tr><th style="text-align:left;">Item</th><th style="text-align:right;">Sub</th></tr>
                  </thead>
                  <tbody id="strukProduk"></tbody>
                </table>
                <hr style="margin:6px 0;">
                <div style="font-size:1em;">
                  <div><b>Total:</b> <span id="strukTotal"></span></div>
                  <div><b>Bayar:</b> <span id="strukBayar"></span></div>
                  <div><b>Kembali:</b> <span id="strukKembali"></span></div>
                </div>
                <hr style="margin:6px 0;">
                <div style="text-align:center;font-size:0.95em;">Terima kasih atas kunjungan Anda!<br>www.mamabakery.com</div>
              </div>
            </div>
            <!-- Tombol Cetak Struk (hidden, muncul setelah pembayaran sukses) -->
            <button id="btnCetakStruk" class="btn btn-warning w-100 mt-2" style="display:none;"><i class="bi bi-printer"></i> Cetak Struk</button>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="toastNotif" class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Customer Display (Hidden by default, will be shown on second screen) -->
<div id="customerDisplay" style="display: none;">
    <!-- Content will be populated by JavaScript -->
</div>

<!-- Modal Daftar Draft -->
<div class="modal fade" id="modalDraft" tabindex="-1" aria-labelledby="modalDraftLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalDraftLabel">Daftar Draft Transaksi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover mb-0" id="tableDraft">
            <thead>
              <tr>
                <th>Invoice</th>
                <th>Tanggal</th>
                <th>Total</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <!-- Diisi via JS -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Pembayaran -->
<div class="modal fade" id="modalBayar" tabindex="-1" aria-labelledby="modalBayarLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalBayarLabel">Pembayaran</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formBayar">
          <div class="mb-3">
            <label class="form-label">Total Belanja</label>
            <input type="text" class="form-control" id="bayarTotal" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Nominal Bayar</label>
            <input type="number" class="form-control" id="bayarNominal" min="0" required autocomplete="off">
          </div>
          <div class="mb-3">
            <label class="form-label">Metode Pembayaran</label>
            <select class="form-select" id="bayarMetode" required>
              <option value="">Pilih Metode</option>
              <?php if(isset($payment_methods) && is_array($payment_methods)): ?>
                <?php foreach($payment_methods as $payment): ?>
                  <option value="<?= $payment->id ?>"><?= htmlspecialchars($payment->name) ?></option>
                <?php endforeach; ?>
              <?php else: ?>
                <!-- Fallback options if data not loaded -->
                <option value="1">Cash</option>
                <option value="2">QRIS</option>
                <option value="3">Transfer Bank</option>
                <option value="4">GoFood</option>
              <?php endif; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Kembalian</label>
            <input type="text" class="form-control" id="bayarKembalian" readonly>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success" id="btnProsesBayar">Proses Pembayaran</button>
      </div>
    </div>
  </div>
</div>

<script>
// HAPUS baris: let cart = [];

// ========================================
// FITUR REALTIME STOK PRODUK
// ========================================
// Fitur ini memungkinkan stok produk terupdate secara otomatis tanpa refresh halaman
// - Update otomatis setiap 3 detik
// - Validasi stok realtime sebelum menambahkan ke keranjang
// - Tombol refresh manual dengan notifikasi
// - Pause update saat halaman tidak aktif
// - Indikator waktu terakhir update

// Variabel global untuk fitur realtime
let updateInterval;
let lastUpdateTime = 0;

// Fungsi untuk menampilkan toast notification
function showToast(msg, type = 'primary') {
    const $toast = $('#toastNotif');
    $toast.removeClass('bg-primary bg-success bg-danger bg-warning').addClass('bg-' + type);
    $toast.find('.toast-body').html(msg);
    const toast = new bootstrap.Toast($toast[0]);
    toast.show();
}

// Fungsi untuk format currency
function formatCurrency(amount) {
    return 'Rp ' + (amount || 0).toLocaleString('id-ID');
}

// Fungsi untuk reset keranjang
function resetKeranjang() {
    // Reset state keranjang (asumsi ada state.cart)
    if (typeof state !== 'undefined') {
        state.cart = [];
    }
    // Kosongkan tabel keranjang
    const tbody = document.querySelector('.table-cart tbody');
    if (tbody) tbody.innerHTML = '';
    // Reset total
    const totalEl = document.querySelector('.fw-bold.fs-5.text-primary');
    if (totalEl) totalEl.textContent = 'Rp 0';
    // Nonaktifkan tombol
    const btnDraft = document.getElementById('btnSimpanDraft');
    if (btnDraft) btnDraft.disabled = true;
    const btnBayar = document.querySelector('.btn-success.w-50');
    if (btnBayar) btnBayar.disabled = true;
}

// Tunggu sampai DOM dan jQuery siap
$(document).ready(function() {
    // Pastikan handler default di footer dimatikan
    $(document).off('click', '.product-card .btn-primary');
    console.log('DOM ready, initializing realtime features...');
    
    // Inisialisasi timestamp
    updateLastUpdateTime();
    
    // Event listener untuk menampilkan modal draft dan memuat datanya
    document.getElementById('btnLihatDraft').addEventListener('click', function() {
        const tableBody = document.querySelector('#tableDraft tbody');
        tableBody.innerHTML = '<tr><td colspan="4" class="text-center">Memuat...</td></tr>';

        fetch('<?= base_url('kasir/transaksi/get_drafts') ?>')
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = ''; // Kosongkan tabel
                if (data.length > 0) {
                    data.forEach(draft => {
                        const row = `<tr>
                            <td>${draft.invoice_code}</td>
                            <td>${new Date(draft.created_at).toLocaleString('id-ID')}</td>
                            <td>${formatCurrency(draft.total_amount)}</td>
                            <td>
                                <button class="btn btn-sm btn-primary btn-lanjutkan-draft" data-id="${draft.id}">Lanjutkan</button>
                            </td>
                        </tr>`;
                        tableBody.innerHTML += row;
                    });
                } else { 
                    tableBody.innerHTML = '<tr><td colspan="4" class="text-center">Tidak ada draft.</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error fetching drafts:', error);
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center">Gagal memuat draft.</td></tr>';
            });
    });
    
    // Override event handler untuk validasi stok sebelum menambahkan ke keranjang
    // Hapus event handler lama dan ganti dengan yang baru
    $(document).off('click', '.product-card .btn-primary');
    
    // Event handler baru dengan validasi stok realtime
    $(document).on('click', '.product-card .btn-primary', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Product card clicked, checking stock...');
        
        const $card = $(this).closest('.product-card');
        const productId = parseInt($card.data('id'));
        const productName = $card.find('.card-title').text().trim() || 'Produk';
        const productPrice = parseInt($card.data('price'));
        
        console.log('Product ID:', productId, 'Name:', productName, 'Price:', productPrice);
        
        // Cek stok realtime dari database sebelum menambahkan
        $.ajax({
            url: '<?= base_url('kasir/transaksi/get_products_realtime') ?>',
            method: 'GET',
            dataType: 'json',
            success: function(products) {
                console.log('Stock check response:', products);
                const product = products.find(p => p.id == productId);
                if (!product) {
                    console.error('Product not found in response');
                    showToast('Produk tidak ditemukan!', 'danger');
                    return;
                }
                
                console.log('Product stock from DB:', product.stock);
                
                // Validasi stok dari database
                if (product.stock <= 0) {
                    console.log('Product out of stock, updating display...');
                    if (typeof showToast !== 'undefined') {
                        showToast('Stok produk habis!', 'danger');
                    } else {
                        alert('Stok produk habis!');
                    }
                    // Update tampilan card
                    updateSingleProductCard(product);
                    return;
                }
                
                // Cek qty di keranjang
                let cartQty = 0;
                if (typeof cart !== 'undefined' && cart.length > 0) {
                    const existing = cart.find(item => parseInt(item.id) === productId);
                    if (existing) {
                        cartQty = existing.qty;
                    }
                }
                
                console.log('Current cart qty:', cartQty, 'Available stock:', product.stock);
                
                if (cartQty >= product.stock) {
                    console.log('Cart qty exceeds stock limit');
                    if (typeof showToast !== 'undefined') {
                        showToast('Qty di keranjang sudah mencapai batas stok produk!', 'warning');
                    } else {
                        alert('Qty di keranjang sudah mencapai batas stok produk!');
                    }
                    return;
                }
                
                // Jika validasi berhasil, lanjutkan dengan logic asli
                if (productId === 0) {
                    console.error('ID produk tidak valid');
                    return;
                }
                
                if (productPrice === 0) {
                    console.error('Harga produk tidak valid');
                    return;
                }
                
                console.log('Adding product to cart...');
                
                // Cek jika produk sudah ada di keranjang
                const existing = cart.find(item => parseInt(item.id) === productId);
                if (existing) {
                    existing.qty += 1;
                    existing.subtotal = existing.qty * existing.price;
                } else {
                    cart.push({
                        id: productId,
                        name: productName,
                        price: productPrice,
                        qty: 1,
                        subtotal: productPrice
                    });
                }
                renderCart();
            },
            error: function(xhr, status, error) {
                console.error('Error checking stock:', error);
                console.error('Response:', xhr.responseText);
                if (typeof showToast !== 'undefined') {
                    showToast('Gagal mengecek stok produk. Silakan coba lagi.', 'danger');
                } else {
                    alert('Gagal mengecek stok produk. Silakan coba lagi.');
                }
            }
        });
    });
    
    // Fungsi untuk update single product card
    function updateSingleProductCard(product) {
        const $card = $('.product-card[data-id="' + product.id + '"]');
        if ($card.length > 0) {
            // Update data-stock
            $card.attr('data-stock', product.stock);
            
            // Update badge
            const $badge = $card.find('.badge');
            if (product.stock > 0) {
                $badge.text('Tersedia').removeClass('bg-danger').addClass('bg-success');
            } else {
                $badge.text('Stok Habis').removeClass('bg-success').addClass('bg-danger');
            }
            
            // Update tombol
            const $btn = $card.find('.btn-primary');
            if (product.stock <= 0) {
                $btn.prop('disabled', true);
            } else {
                $btn.prop('disabled', false);
            }
        }
    }
    
    // Override event handler untuk tombol plus dengan validasi stok realtime
    $(document).off('click', '.qty-plus').on('click', '.qty-plus', function() {
        const idx = $(this).data('idx');
        if (cart[idx]) {
            const productId = cart[idx].id;
            const currentQty = parseInt(cart[idx].qty);
            
            // Cek stok realtime dari database
            $.ajax({
                url: '<?= base_url('kasir/transaksi/get_products_realtime') ?>',
                method: 'GET',
                dataType: 'json',
                success: function(products) {
                    const product = products.find(p => p.id == productId);
                                    if (!product) {
                    showToast('Produk tidak ditemukan!', 'danger');
                    return;
                }
                    
                    if (currentQty >= product.stock) {
                        if (typeof showToast !== 'undefined') {
                            showToast('Qty sudah mencapai batas stok produk!', 'warning');
                        } else {
                            alert('Qty sudah mencapai batas stok produk!');
                        }
                        // Update tampilan card
                        updateSingleProductCard(product);
                        return;
                    }
                    
                    cart[idx].qty = currentQty + 1;
                    cart[idx].subtotal = cart[idx].qty * parseInt(cart[idx].price);
                    renderCart();
                },
                error: function(xhr, status, error) {
                    console.error('Error checking stock:', error);
                    if (typeof showToast !== 'undefined') {
                        showToast('Gagal mengecek stok produk. Silakan coba lagi.', 'danger');
                    } else {
                        alert('Gagal mengecek stok produk. Silakan coba lagi.');
                    }
                }
            });
        }
    });
    
    // Fungsi untuk update data produk secara realtime
    function updateProductsRealtime(showNotification = false) {
        console.log('Updating products realtime...');
        $.ajax({
            url: '<?= base_url('kasir/transaksi/get_products_realtime') ?>',
            method: 'GET',
            dataType: 'json',
            success: function(products) {
                console.log('Received products data:', products);
                let updatedCount = 0;
                products.forEach(function(product) {
                    const $card = $('.product-card[data-id="' + product.id + '"]');
                    if ($card.length > 0) {
                        const oldStock = parseInt($card.attr('data-stock')) || 0;
                        
                        // Update data-stock
                        $card.attr('data-stock', product.stock);
                        
                        // Update badge
                        const $badge = $card.find('.badge');
                        if (product.stock > 0) {
                            $badge.text('Tersedia').removeClass('bg-danger').addClass('bg-success');
                        } else {
                            $badge.text('Stok Habis').removeClass('bg-success').addClass('bg-danger');
                        }
                        
                        // Update tombol
                        const $btn = $card.find('.btn-primary');
                        if (product.stock <= 0) {
                            $btn.prop('disabled', true);
                        } else {
                            $btn.prop('disabled', false);
                        }
                        
                        // Hitung berapa produk yang berubah
                        if (oldStock !== product.stock) {
                            updatedCount++;
                            console.log('Product ' + product.name + ' stock changed from ' + oldStock + ' to ' + product.stock);
                        }
                    }
                });
                
                // Update timestamp
                updateLastUpdateTime();
                
                // Tampilkan notifikasi jika ada perubahan dan diminta
                if (showNotification && updatedCount > 0) {
                    if (typeof showToast !== 'undefined') {
                        showToast('Stok ' + updatedCount + ' produk telah diperbarui', 'info');
                    }
                }
                
                console.log('Realtime update completed. ' + updatedCount + ' products updated.');
            },
            error: function(xhr, status, error) {
                console.error('Error updating products:', error);
                console.error('Response:', xhr.responseText);
                if (showNotification) {
                    if (typeof showToast !== 'undefined') {
                        showToast('Gagal memperbarui stok produk', 'danger');
                    }
                }
            }
        });
    }
    
    // Fungsi untuk update timestamp terakhir update
    function updateLastUpdateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        });
        $('#lastUpdateTime').text('Update: ' + timeString);
    }
    
    // Update produk setiap 3 detik (lebih responsif)
    updateInterval = setInterval(updateProductsRealtime, 3000);
    
    // Debounce untuk menghindari terlalu banyak request
    function debouncedUpdate() {
        const now = Date.now();
        if (now - lastUpdateTime > 1000) { // Minimal 1 detik antar update
            lastUpdateTime = now;
            updateProductsRealtime();
        }
    }
    
    // Update produk setelah pembayaran berhasil
    function updateProductsAfterPayment() {
        setTimeout(updateProductsRealtime, 1000); // Update 1 detik setelah pembayaran
    }
    
    // Pause/resume update otomatis saat halaman tidak aktif
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(updateInterval);
        } else {
            updateInterval = setInterval(updateProductsRealtime, 3000);
            // Update segera saat halaman aktif kembali
            setTimeout(updateProductsRealtime, 500);
        }
    });
    
    // Event handler untuk tombol refresh produk
    $(document).on('click', '#btnRefreshProducts', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).find('i').addClass('fa-spin');
        
        updateProductsRealtime(true); // Tampilkan notifikasi
        
        setTimeout(function() {
            $btn.prop('disabled', false).find('i').removeClass('fa-spin');
        }, 1000);
    });
    
    console.log('Realtime features initialized successfully');
    
    // Setelah pembayaran berhasil, update stok produk di card
    function updateProductStockAfterCheckout(cart) {
        cart.forEach(function(item) {
            var card = document.querySelector('.product-card[data-id="' + item.id + '"]');
            if (card) {
                var stock = parseInt(card.getAttribute('data-stock')) || 0;
                var newStock = stock - item.qty;
                card.setAttribute('data-stock', newStock);
                // Update badge
                var badge = card.querySelector('.badge');
                if (badge) {
                    if (newStock > 0) {
                        badge.textContent = 'Tersedia';
                        badge.className = 'badge bg-success';
                    } else {
                        badge.textContent = 'Stok Habis';
                        badge.className = 'badge bg-danger';
                    }
                }
                // Disable tombol jika stok habis
                var btn = card.querySelector('.btn-primary');
                if (btn && newStock <= 0) {
                    btn.disabled = true;
                }
            }
        });
        
        // Update produk dari database untuk memastikan data akurat
        updateProductsAfterPayment();
    }
    
    // Event handler untuk tombol proses pembayaran
    document.getElementById('btnProsesBayar').addEventListener('click', function() {
        const transaction_id = state.transaction_id; // Pastikan state.transaction_id sudah di-set saat memulai/melanjutkan transaksi
        const payment_id = document.getElementById('bayarMetode').value;
        const paid_amount = document.getElementById('bayarNominal').value;
        const total_amount = parseFloat(document.getElementById('bayarTotal').value.replace(/[^0-9,-]+/g,""));

        if (!transaction_id || !payment_id || !paid_amount) {
            return;
        }

        const formData = new FormData();
        formData.append('transaction_id', transaction_id);
        formData.append('payment_id', payment_id);
        formData.append('paid_amount', paid_amount);
        formData.append('total_amount', total_amount);

        fetch('<?= base_url('api/kasir/selesaikan_pembayaran') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                showToast('Pembayaran berhasil!', 'success');
                // Ambil snapshot keranjang sebelum reset
                var cartSnapshot = (typeof state !== 'undefined' && state.cart) ? JSON.parse(JSON.stringify(state.cart)) : [];
                bootstrap.Modal.getInstance(document.getElementById('modalBayar')).hide();
                // Reset keranjang dan state
                resetKeranjang(); 
                // === Tambahan untuk cetak struk ===
                if (typeof lastTransactionId !== 'undefined') {
                    lastTransactionId = result.transaction_id || null;
                } else {
                    window.lastTransactionId = result.transaction_id || null;
                }
                $('#btnCetakStruk').show();
                // ================================
                state.transaction_id = null; // Reset transaksi aktif setelah pembayaran
                // Update stok di card menggunakan snapshot
                updateProductStockAfterCheckout(cartSnapshot);
            } else {
                showToast('Gagal memproses pembayaran: ' + result.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat menghubungi server.', 'danger');
        });
    });
});

// ========================
// Ganti event handler tombol tambah menjadi seluruh card
// ========================
$(document).off('click', '.product-card-modern').on('click', '.product-card-modern', function(e) {
    // Jika klik pada tombol tambah, biarkan event tetap berjalan (tidak dobel)
    if ($(e.target).closest('.btn-add-modern').length > 0) return;
    // Jika stok habis, jangan proses
    if ($(this).find('.btn-add-modern').is(':disabled')) return;
    // Simulasikan klik tombol tambah
    $(this).find('.btn-add-modern').trigger('click');
});
// Pastikan tombol tambah tetap bisa diklik manual (untuk aksesibilitas)
$(document).off('click', '.btn-add-modern').on('click', '.btn-add-modern', function(e) {
    e.preventDefault();
    e.stopPropagation();
    const $card = $(this).closest('.product-card-modern');
    const productId = parseInt($card.data('id'));
    const productName = $card.find('.card-title').text().trim() || 'Produk';
    const productPrice = parseInt($card.data('price'));
    // Cek stok realtime dari database sebelum menambahkan
    $.ajax({
        url: '<?= base_url('kasir/transaksi/get_products_realtime') ?>',
        method: 'GET',
        dataType: 'json',
        success: function(products) {
            const product = products.find(p => p.id == productId);
            if (!product) {
                showToast('Produk tidak ditemukan!', 'danger');
                return;
            }
            if (product.stock <= 0) {
                showToast('Stok produk habis!', 'danger');
                updateSingleProductCard(product);
                return;
            }
            let cartQty = 0;
            if (typeof cart !== 'undefined' && cart.length > 0) {
                const existing = cart.find(item => parseInt(item.id) === productId);
                if (existing) {
                    cartQty = existing.qty;
                }
            }
            if (cartQty >= product.stock) {
                showToast('Qty di keranjang sudah mencapai batas stok produk!', 'warning');
                return;
            }
            if (productId === 0 || productPrice === 0) {
                showToast('Data produk tidak valid!', 'danger');
                return;
            }
            // Tambahkan ke keranjang
            const existing = cart.find(item => parseInt(item.id) === productId);
            if (existing) {
                existing.qty += 1;
                existing.subtotal = existing.qty * existing.price;
            } else {
                cart.push({
                    id: productId,
                    name: productName,
                    price: productPrice,
                    qty: 1,
                    subtotal: productPrice
                });
            }
            renderCart();
        },
        error: function(xhr, status, error) {
            showToast('Gagal mengecek stok produk. Silakan coba lagi.', 'danger');
        }
    });
});

</script>

<!-- Modal Konfirmasi Lanjutkan Draft -->
<div class="modal fade" id="modalKonfirmasiDraft" tabindex="-1" aria-labelledby="modalKonfirmasiDraftLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalKonfirmasiDraftLabel">Lanjutkan Draft?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Keranjang akan diganti dengan isi draft yang dipilih. Lanjutkan?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success" id="btnKonfirmasiLanjutDraft">Lanjutkan</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Konfirmasi Hapus Draft -->
<div class="modal fade" id="modalHapusDraft" tabindex="-1" aria-labelledby="modalHapusDraftLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalHapusDraftLabel">Hapus Draft?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Yakin ingin menghapus draft ini? Tindakan ini tidak dapat dibatalkan.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="btnKonfirmasiHapusDraft">Hapus</button>
      </div>
    </div>
  </div>
</div>

<style>
    /* Custom utility classes */
    .cursor-pointer {
        cursor: pointer;
    }
    .min-h-150px {
        min-height: 150px;
    }
    .mw-90px {
        max-width: 90px;
    }
    .mh-80px {
        max-height: 80px;
    }
    .object-fit-cover {
        object-fit: cover;
    }
    .w-48px {
      width: 48px;
    }
    .fs-sm {
        font-size: 0.92em;
    }
    /* Hover effects for product cards, not directly convertible to Bootstrap classes without custom CSS */
    .product-card:hover {
        transform: translateY(-3px) scale(1.03);
        box-shadow: 0 0.5rem 1rem rgba(22, 119, 255, 0.10);
        border-color: #1677ff;
    }
    /* Specific overrides for button colors */
    .btn-primary, .btn-primary:active, .btn-primary:focus {
        background: #1677ff !important;
        border-color: #1677ff !important;
    }
    .btn-primary:hover {
        background: #125ecc !important;
        border-color: #125ecc !important;
    }

    /* Animation for refresh button */
    .fa-spin {
        animation: fa-spin 1s infinite linear;
    }
    @keyframes fa-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Styling for last update time */
    #lastUpdateTime {
        font-size: 0.75rem;
        color: #6c757d;
        white-space: nowrap;
    }

    /* Struk Print Styles (DO NOT MODIFY) */
    @media print {
        body * { visibility: hidden !important; }
        #strukArea, #strukArea * { visibility: visible !important; }
        #strukArea { position: absolute; left: 0; top: 0; width: 100vw; background: #fff; }
        #strukPrint {
            width: 210px !important;
            min-width: 0 !important;
            max-width: 100vw !important;
            font-size: 11px !important;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            background: #fff !important;
            page-break-inside: avoid !important;
        }
        #strukPrint img { max-width: 100%; height: auto; }
        .no-print, .no-print * { display: none !important; }
        html, body { background: #fff !important; }
    }
    /* Gambar produk fill kotak */
    .product-image-fill {
        width: 140px;
        height: 140px;
        object-fit: cover;
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        display: block;
        margin-left: auto;
        margin-right: auto;
    }
    /* Modern Product Card */
    .product-card-modern {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 4px 24px rgba(22, 119, 255, 0.13), 0 2px 8px rgba(0,0,0,0.10);
        transition: transform 0.18s cubic-bezier(.4,2,.6,1), box-shadow 0.18s;
        min-height: 320px;
        padding-bottom: 0;
    }
    .product-card-modern:hover {
        transform: translateY(-8px) scale(1.035);
        box-shadow: 0 12px 36px rgba(22, 119, 255, 0.22), 0 4px 16px rgba(0,0,0,0.16);
        z-index: 2;
    }
    .product-image-modern {
        width: 160px;
        height: 160px;
        border-radius: 14px;
        overflow: hidden;
        background: #f8f9fa;
        box-shadow: 0 1.5px 6px rgba(0,0,0,0.06);
        position: relative;
        margin-bottom: 0.5rem;
    }
    .product-image-fill-modern {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        border-radius: 14px;
        background: #f8f9fa;
    }
    .btn-add-modern {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        box-shadow: 0 2px 8px rgba(22,119,255,0.10);
        border: none;
        transition: background 0.15s, box-shadow 0.15s, transform 0.15s;
        padding: 0;
    }
    .btn-add-modern:disabled {
        background: #e5e5e5 !important;
        color: #aaa !important;
        border: none;
        cursor: not-allowed;
    }
    .btn-add-modern:hover:not(:disabled) {
        background: #125ecc !important;
        color: #fff;
        transform: scale(1.12);
        box-shadow: 0 4px 16px rgba(22,119,255,0.18);
    }
    .product-card-modern .badge {
        font-size: 0.93em;
        border-radius: 8px;
        padding: 0.32em 0.8em;
    }
    .col-lg-4.col-md-6.col-12.mb-4 {
        padding-bottom: 1.2rem;
    }
    /* Responsive: 2 columns on md, 1 on xs */
    @media (max-width: 991px) {
        .product-image-modern { width: 120px; height: 120px; }
    }
    @media (max-width: 767px) {
        .product-image-modern { width: 100px; height: 100px; }
        .product-card-modern { min-height: 250px; }
    }
</style>
