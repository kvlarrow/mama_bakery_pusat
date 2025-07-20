</div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var base_url = "<?= base_url(); ?>";
        var cart = [];
        var lastTransactionId = null;

        function showToast(msg, type = 'primary') {
            const $toast = $('#toastNotif');
            $toast.removeClass('bg-primary bg-success bg-danger').addClass('bg-' + type);
            $toast.find('.toast-body').html(msg);
            const toast = new bootstrap.Toast($toast[0]);
            toast.show();
        }

        // Auto-hide alerts after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
            
            // Enable tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            // Sembunyikan tombol cetak struk saat awal dan setelah renderCart jika keranjang kosong
            $('#btnCetakStruk').hide();
        });

        // Helper function untuk memastikan nilai numerik yang valid
        function safeNumber(value) {
            const num = parseInt(value) || 0;
            return isNaN(num) ? 0 : num;
        }
        
        // Helper function untuk format currency
        function formatCurrency(amount) {
            return 'Rp ' + (amount || 0).toLocaleString('id-ID');
        }
        
        // Render isi keranjang - definisikan di scope global
        window.renderCart = function() {
            const $tbody = $('.card-body table tbody');
            $tbody.empty();
            let total = 0;
            
            if (cart.length === 0) {
                $tbody.append('<tr><td colspan="4" class="text-center text-muted py-4">Belum ada item</td></tr>');
                $('.card-footer .btn-success, .card-footer .btn-outline-secondary').prop('disabled', true);
                $('#btnCetakStruk').hide();
            } else {
                cart.forEach((item, idx) => {
                    // Pastikan nilai valid
                    const qty = safeNumber(item.qty);
                    const price = safeNumber(item.price);
                    const subtotal = qty * price;
                    
                    // Update item dengan nilai yang valid
                    item.qty = qty;
                    item.price = price;
                    item.subtotal = subtotal;
                    
                    total += subtotal;
                    
                    $tbody.append(`
                        <tr>
                            <td>${item.name}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center gap-1">
                                    <button class="btn btn-sm btn-light border qty-minus" data-idx="${idx}">-</button>
                                    <span class="mx-1">${qty}</span>
                                    <button class="btn btn-sm btn-light border qty-plus" data-idx="${idx}">+</button>
                                </div>
                            </td>
                            <td class="text-end">${formatCurrency(subtotal)}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-danger btn-remove" data-idx="${idx}"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    `);
                });
                $('.card-footer .btn-success, .card-footer .btn-outline-secondary').prop('disabled', false);
                // $('#btnCetakStruk').show(); // HAPUS baris ini agar tombol tidak muncul saat keranjang diisi
            }
            
            $('.card-footer .fs-5.text-primary').text(formatCurrency(total));
            syncCustomerDisplay();
        };
        
        // Script keranjang transaksi kasir
        $(document).ready(function() {
            
            // Tambah produk ke keranjang (dengan validasi stok realtime)
            // Event handler ini akan di-override oleh transaksi.php untuk validasi stok
            // Jika tidak ada validasi stok, gunakan logic default ini
            if (window.location.pathname.indexOf('/kasir/transaksi') === -1) {
                $(document).on('click', '.product-card .btn-primary', function() {
                    const $card = $(this).closest('.product-card');
                    const productId = safeNumber($card.data('id'));
                    const productName = $card.find('.card-title').text().trim() || 'Produk';
                    const productPrice = safeNumber($card.data('price'));
                    const productStock = safeNumber($card.data('stock'));
                    
                    // Validasi data produk
                    if (productId === 0) {
                        console.error('ID produk tidak valid');
                        return;
                    }
                    
                    if (productPrice === 0) {
                        console.error('Harga produk tidak valid');
                        return;
                    }
                    
                    // Validasi stok dasar (jika tidak ada validasi realtime)
                    if (productStock <= 0) {
                        showToast('Stok produk habis!', 'danger');
                        return;
                    }
                    
                    // Cek qty di keranjang
                    let cartQty = 0;
                    const existing = cart.find(item => parseInt(item.id) === productId);
                    if (existing) {
                        cartQty = existing.qty;
                    }
                    
                    if (cartQty >= productStock) {
                        showToast('Qty di keranjang sudah mencapai batas stok produk!', 'warning');
                        return;
                    }
                    
                    // Cek jika produk sudah ada di keranjang
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
                });
            }
            
            // Event: tambah qty (dengan validasi stok)
            $(document).on('click', '.qty-plus', function() {
                const idx = $(this).data('idx');
                if (cart[idx]) {
                    const productId = cart[idx].id;
                    const currentQty = safeNumber(cart[idx].qty);
                    
                    // Cek stok produk dari card
                    const $card = $('.product-card[data-id="' + productId + '"]');
                    const productStock = safeNumber($card.data('stock'));
                    
                    if (currentQty >= productStock) {
                        showToast('Qty sudah mencapai batas stok produk!', 'warning');
                        return;
                    }
                    
                    cart[idx].qty = currentQty + 1;
                    cart[idx].subtotal = cart[idx].qty * safeNumber(cart[idx].price);
                    renderCart();
                }
            });
            
            // Event: kurang qty
            $(document).on('click', '.qty-minus', function() {
                const idx = $(this).data('idx');
                if (cart[idx]) {
                    const currentQty = safeNumber(cart[idx].qty);
                    if (currentQty > 1) {
                        cart[idx].qty = currentQty - 1;
                        cart[idx].subtotal = cart[idx].qty * safeNumber(cart[idx].price);
                    } else {
                        cart.splice(idx, 1);
                    }
                    renderCart();
                }
            });
            
            // Event: hapus item
            $(document).on('click', '.btn-remove', function() {
                const idx = $(this).data('idx');
                if (cart[idx]) {
                    cart.splice(idx, 1);
                    renderCart();
                }
            });
            
            // Simpan transaction_id aktif di JS
            // State global untuk transaksi
            window.state = {
                transaction_id: null,
                cart: []
            };

            // Event: Lanjutkan Draft dari Modal
            $(document).on('click', '.btn-lanjutkan-draft', function() {
                const transactionId = $(this).data('id');
                const $button = $(this);
                $button.prop('disabled', true).text('Memuat...');

                // 1. Ubah status draft menjadi 'pending'
                $.post(base_url + 'kasir/transaksi/activate_draft_action/' + transactionId, function(response) {
                    if (response.status === 'success') {
                        state.transaction_id = transactionId;

                        // 2. Ambil item dari draft via AJAX
                        $.get(base_url + 'kasir/transaksi/get_draft_items/' + transactionId, function(res) {
                            if (res.status === 'success') {
                                cart = res.items.map(item => ({
                                    id: item.product_id,
                                    name: item.product_name,
                                    price: parseFloat(item.unit_price),
                                    qty: parseInt(item.quantity),
                                    subtotal: parseFloat(item.total_price)
                                }));
                                renderCart();
                                $('#modalDraft').modal('hide');
                                showToast('Draft berhasil dimuat.', 'success');
                                // Tambahkan: refresh daftar draft agar draft yang sudah dilanjutkan hilang
                                setTimeout(function() { $('#btnLihatDraft').trigger('click'); }, 500);
                            } else {
                                showToast('Gagal memuat item draft.', 'danger');
                            }
                        }, 'json');

                    } else {
                        showToast(response.message || 'Gagal mengaktifkan draft.', 'danger');
                        $button.prop('disabled', false).text('Lanjutkan');
                    }
                }, 'json').fail(function() {
                    showToast('Terjadi kesalahan server.', 'danger');
                    $button.prop('disabled', false).text('Lanjutkan');
                });
            });

            // Event: Simpan Draft (event delegation + debug)
            $(document).off('click', '#btnSimpanDraft').on('click', '#btnSimpanDraft', function() {
                console.log('Simpan draft diklik');
                if (cart.length === 0) {
                    showToast('Keranjang masih kosong!', 'danger');
                    return;
                }
                const total = cart.reduce((sum, item) => sum + (parseInt(item.subtotal) || 0), 0);
                $.ajax({
                    url: base_url + 'kasir/kasir/simpan_draft',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        cart: JSON.stringify(cart),
                        total: total
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            showToast('Draft transaksi berhasil disimpan!', 'success');
                            cart = [];
                            renderCart();
                            state.transaction_id = res.transaction_id;
                        } else {
                            showToast('Gagal menyimpan draft: ' + res.message, 'danger');
                        }
                    },
                    error: function(xhr) {
                        showToast('Terjadi kesalahan saat menyimpan draft!', 'danger');
                        console.log(xhr.responseText);
                    }
                });
            });

            // Fungsi untuk simpan draft otomatis sebelum checkout
            function simpanDraftOtomatis(callback) {
                if (cart.length === 0) {
                    showToast('Keranjang masih kosong!', 'danger');
                    return;
                }
                const total = cart.reduce((sum, item) => sum + (parseInt(item.subtotal) || 0), 0);
                $.ajax({
                    url: base_url + 'kasir/kasir/simpan_draft',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        cart: JSON.stringify(cart),
                        total: total
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            state.transaction_id = res.transaction_id;
                            if (typeof callback === 'function') callback();
                        } else {
                            showToast('Gagal menyimpan draft: ' + res.message, 'danger');
                        }
                    },
                    error: function(xhr) {
                        showToast('Terjadi kesalahan saat menyimpan draft!', 'danger');
                        console.log(xhr.responseText);
                    }
                });
            }

            // Modifikasi event pembayaran: jika belum ada draft, simpan draft otomatis lalu checkout
            $(document).off('click', '#btnProsesBayar').on('click', '#btnProsesBayar', function() {
                console.log('Tombol Proses Pembayaran diklik');
                const total = cart.reduce((sum, item) => sum + (parseInt(item.subtotal) || 0), 0);
                const bayar = parseInt($('#bayarNominal').val()) || 0;
                const metode = $('#bayarMetode').val();
                if (cart.length === 0) {
                    showToast('Keranjang masih kosong!', 'danger');
                    return;
                }
                if (!bayar || bayar < total) {
                    showToast('Nominal bayar kurang dari total!', 'danger');
                    return;
                }
                if (!metode) {
                    showToast('Pilih metode pembayaran!', 'danger');
                    return;
                }
                // Jika belum ada draft, simpan draft otomatis lalu lanjutkan checkout
                if (!state.transaction_id) {
                    simpanDraftOtomatis(function() {
                        // Lanjutkan ke proses checkout setelah draft tersimpan
                        lanjutCheckout(total, bayar, metode);
                    });
                } else {
                    // Jika sudah ada draft, langsung lanjutkan checkout
                    lanjutCheckout(total, bayar, metode);
                }
            });

            function lanjutCheckout(total, bayar, metode) {
                $.ajax({
                    url: base_url + 'kasir/kasir/checkout',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        transaction_id: state.transaction_id,
                        total: total,
                        bayar: bayar,      // perbaiki dari 'paid'
                        metode: metode,    // perbaiki dari 'method'
                        cart: JSON.stringify(cart)
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            showToast('Pembayaran berhasil!', 'success');
                            setTimeout(function() {
                                var $toast = $('#toastNotif');
                                var toast = bootstrap.Toast.getOrCreateInstance($toast[0]);
                                toast.hide();
                            }, 1000); // Toast akan hilang setelah 2 detik
                            $('#modalBayar').modal('hide'); // perbaiki id modal
                            $('#bayarNominal').val('');
                            $('#bayarMetode').val('');
                            $('#bayarKembalian').val(''); // reset kembalian
                            cart = [];
                            renderCart();
                            state.transaction_id = null; // Reset transaksi aktif
                            lastTransactionId = res.transaction_id;
                            $('#btnCetakStruk').show(); // TOMBOL HANYA MUNCUL SETELAH PEMBAYARAN

                        } else {
                            showToast('Gagal memproses pembayaran: ' + res.message, 'danger');
                        }
                    },
                    error: function(xhr) {
                        showToast('Terjadi kesalahan saat memproses pembayaran!', 'danger');
                        console.log(xhr.responseText);
                    }
                });
            }

            // Event listener untuk tombol riwayat transaksi
            $(document).on('click', '.btn-riwayat', function() {
                window.location.href = base_url + 'kasir/transaksi/riwayat';
            });

            // Skrip DataTables untuk Riwayat Transaksi
            if ($('#tabelRiwayat').length) {
                var tableRiwayat = $('#tabelRiwayat').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "order": [],
                    "ajax": {
                        "url": base_url + "kasir/kasir/riwayat_data",
                        "type": "POST",
                        "data": function (d) {
                            d.min_date = $('#min_date').val();
                            d.max_date = $('#max_date').val();
                        }
                    },
                    "columnDefs": [
                        {
                            "targets": [0, 5], // Kolom pertama (ID) dan kolom terakhir (Aksi) tidak dapat diurutkan
                            "orderable": false,
                        },
                        {
                            "targets": [0, 1, 2, 3, 4, 5], // All columns center aligned
                            "className": "text-center"
                        },
                        {
                            "targets": [3], // Kolom Total
                            "className": "text-end"
                        }
                    ],
                    "language": {
                        
                    }
                });

                $('#filter_tanggal').click(function() {
                    tableRiwayat.ajax.reload();
                });

                // Handle Detail button click
                $(document).on('click', '.btn-detail-transaksi', function() {
                    const transactionId = $(this).data('id');
                $.ajax({
                        url: base_url + 'kasir/kasir/get_transaction_details',
                        method: 'POST',
                    dataType: 'json',
                        data: { id: transactionId },
                        success: function(response) {
                            if (response.status === 'success') {
                                const trx = response.data;
                                $('#detailInvoiceCode').text(trx.invoice_code || '-');
                                $('#detailTanggal').text(trx.created_at ? new Date(trx.created_at).toLocaleString('id-ID') : '-');
                                $('#detailKasir').text(trx.kasir_name || '-');
                                $('#detailMetodePembayaran').text(trx.payment_method_name || '-');
                                $('#detailTotalBelanja').text('Rp ' + (trx.total_amount ? parseInt(trx.total_amount).toLocaleString('id-ID') : '0'));
                                $('#detailBayar').text('Rp ' + (trx.paid_amount ? parseInt(trx.paid_amount).toLocaleString('id-ID') : '0'));
                                $('#detailKembalian').text('Rp ' + (trx.change_amount ? parseInt(trx.change_amount).toLocaleString('id-ID') : '0'));
                                let itemsHtml = '';
                                if (trx.items && trx.items.length) {
                                    trx.items.forEach(function(item) {
                                        itemsHtml += `<tr>
                                            <td>${item.product_name || '-'}</td>
                                            <td class="text-center">${item.quantity || 0}</td>
                                            <td class="text-end">Rp ${item.unit_price ? parseInt(item.unit_price).toLocaleString('id-ID') : '0'}</td>
                                            <td class="text-end">Rp ${item.total_price ? parseInt(item.total_price).toLocaleString('id-ID') : '0'}</td>
                                        </tr>`;
                                    });
                                } else {
                                    itemsHtml = '<tr><td colspan="4" class="text-center">Tidak ada item</td></tr>';
                                }
                                $('#detailItemsTableBody').html(itemsHtml);
                                $('#modalDetailTransaksi').modal('show');
                        } else {
                                showToast('Gagal memuat detail transaksi.', 'danger');
                            }
                        },
                        error: function() {
                            showToast('Terjadi kesalahan saat memuat detail transaksi.', 'danger');
                    }
                });
            });
            }
            // Admin Fullscreen Toggle
            $(document).on('click', '#fullscreenToggle', function() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().then(() => {
                        $('#fullscreenToggle i').removeClass('bi-arrows-fullscreen').addClass('bi-fullscreen-exit');
                    });
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen().then(() => {
                            $('#fullscreenToggle i').removeClass('bi-fullscreen-exit').addClass('bi-arrows-fullscreen');
                        });
                    }
                }
            });

            document.addEventListener('fullscreenchange', () => {
                const sidebar = $('#sidebar');
                if (document.fullscreenElement) {
                    sidebar.addClass('d-none');
                } else {
                    sidebar.removeClass('d-none');
                }
            });

            // Skrip DataTables untuk Produk (Admin)
            if ($('#tabelProduk').length) {
                // Check if DataTable is already initialized
                if ($.fn.DataTable.isDataTable('#tabelProduk')) {
                    $('#tabelProduk').DataTable().destroy();
                }

                const tabelProduk = $('#tabelProduk').DataTable({
                    "processing": true,
                    "serverSide": true,
                    "order": [[1, 'desc']], // Urutkan berdasarkan tanggal secara default
                    "ajax": {
                        "url": base_url + "admin/produk/data",
                        "type": "POST"
                    },
                    "columns": [
                        { "data": "name" },
                        { "data": "price" },
                        { "data": "stock" },
                        { "data": "category" },
                        { "data": "photo" },
                        { "data": "aksi" }
                    ],
                    "columnDefs": [
                        {
                            "targets": [0], // Nama Produk
                            "className": "text-start"
                        },
                        {
                            "targets": [1, 2, 3], // Harga, Stok, Kategori
                            "className": "text-center"
                        },
                        {
                            "targets": [4, 5], // Foto, Aksi
                            "orderable": false,
                            "className": "text-center"
                        }
                    ],
                    "language": {
                        
                    },
                    "pageLength": 10,
                    "lengthMenu": [5, 10, 25, 50, 100]
                });

                // Handle Tambah Produk
                $('#formTambahProduk').on('submit', function(e) {
            e.preventDefault();
                    const formData = new FormData(this);

                    $.ajax({
                        url: base_url + 'admin/produk/tambah',
                        type: 'POST',
                        data: formData,
                        processData: false, // Don't process the files
                        contentType: false, // Set content type to false as jQuery will tell the server its a multipart request
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                showToast('Produk berhasil ditambahkan!', 'success');
                                $('#modalTambahProduk').modal('hide');
                                $('#formTambahProduk')[0].reset();
                                tabelProduk.ajax.reload();
            } else {
                                showToast('Gagal menambahkan produk: ' + response.message, 'danger');
                            }
                    },
                    error: function(xhr) {
                            showToast('Terjadi kesalahan saat menambahkan produk!', 'danger');
                            console.log(xhr.responseText);
                        }
                    });
                });

                // Handle Edit Produk (Load Data)
                $(document).on('click', '.btn-edit-produk', function() {
                    const id = $(this).data('id');
                    $.ajax({
                        url: base_url + 'admin/produk/get_produk/' + id,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            if (data) {
                                $('#editProductId').val(data.id);
                                $('#editProductName').val(data.name);
                                $('#editProductCategory').val(data.category_id);
                                $('#editProductPrice').val(data.price);
                                $('#editProductStock').val(data.stock);
                                // Display current photo
                                const photoPath = data.photo ? base_url + 'uploads/products/' + data.photo : '';
                                $('#currentProductPhoto').attr('src', photoPath).toggle(!!photoPath);
                                $('#modalEditProduk').modal('show');
                            } else {
                                showToast('Produk tidak ditemukan!', 'danger');
                            }
                        },
                        error: function(xhr) {
                            showToast('Terjadi kesalahan saat mengambil data produk!', 'danger');
                            console.log(xhr.responseText);
                        }
                    });
                });

                // Handle Update Produk (Submit)
                $('#formEditProduk').on('submit', function(e) {
            e.preventDefault();
                    const formData = new FormData(this);
                    $.ajax({
                        url: base_url + 'admin/produk/update',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                showToast('Produk berhasil diperbarui!', 'success');
                                $('#modalEditProduk').modal('hide');
                                tabelProduk.ajax.reload();
                } else {
                                showToast('Gagal memperbarui produk: ' + response.message, 'danger');
                            }
                        },
                        error: function(xhr) {
                            showToast('Terjadi kesalahan saat memperbarui produk!', 'danger');
                            console.log(xhr.responseText);
                        }
                    });
                });

                // Handle Hapus Produk (Show confirmation modal)
                $(document).on('click', '.btn-hapus-produk', function() {
                    const id = $(this).data('id');
                    const nama = $(this).data('nama');
                    $('#hapusProductId').val(id);
                    $('#namaProdukHapus').text(nama);
                    $('#modalHapusProduk').modal('show');
                });

                // Handle Hapus Produk (Delete action)
                $('#btnConfirmHapusProduk').on('click', function() {
                    const id = $('#hapusProductId').val();
                $.ajax({
                        url: base_url + 'admin/produk/hapus/' + id,
                        type: 'POST',
                    dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                showToast('Produk berhasil dihapus!', 'success');
                                $('#modalHapusProduk').modal('hide');
                                tabelProduk.ajax.reload();
                } else {
                                showToast('Gagal menghapus produk: ' + response.message, 'danger');
                            }
                        },
                        error: function(xhr) {
                            showToast('Terjadi kesalahan saat menghapus produk!', 'danger');
                            console.log(xhr.responseText);
                        }
                    });
                });

                // Pastikan modal backdrop hilang setelah modal tambah produk ditutup
                $('#modalTambahProduk').on('hidden.bs.modal', function () {
                    $('.modal-backdrop').remove();
                });

                // Pastikan modal backdrop hilang setelah modal edit produk ditutup
                $('#modalEditProduk').on('hidden.bs.modal', function () {
                    $('.modal-backdrop').remove();
        });

        // Tampilkan modal konfirmasi hapus produk
        $(document).on('click', '.btn-hapus-produk', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var row = $(this).closest('tr');
            var nama = row.find('td').eq(0).text();
            $('#hapus_id_produk').val(id);
            $('#hapus_nama_produk').text(nama);
            $('#modalHapusProduk').modal('show');
        });
        // Proses konfirmasi hapus
        $('#btnKonfirmasiHapus').on('click', function() {
            var id = $('#hapus_id_produk').val();
                    if (!id) {
                showToast('ID produk tidak ditemukan!', 'danger');
                        return;
                    }
                    $.ajax({
                url: base_url + 'admin/produk/hapus/' + id,
                        type: 'POST',
                        dataType: 'json',
                        success: function(res) {
                            if (res.status === 'success') {
                        $('#modalHapusProduk').modal('hide');
                        $('#tabelProduk').DataTable().ajax.reload();
                        showToast('Produk berhasil dihapus', 'success');
                            } else {
                        showToast(res.message || 'Gagal menghapus produk', 'danger');
                            }
                        },
                        error: function(xhr) {
                    showToast('Gagal menghapus produk: ' + xhr.responseText, 'danger');
                }
            });
        });
            }

            // Inisialisasi DataTables untuk tabel Kategori
        if ($('#tabelKategori').length) {
            var tabelKategori = $('#tabelKategori').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: base_url + 'admin/kategori/data',
                    type: 'POST'
                },
                columns: [
                    { data: 'name' },
                        { data: 'aksi', orderable: false, searchable: false }
                    ],
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50, 100]
                });

                // Event handler untuk tombol tambah kategori
                $(document).on('click', '#btnTambahKategori', function(e) {
                e.preventDefault();
                    $('#formKategori')[0].reset(); // Reset form
                    $('#kategori_id').val(''); // Kosongkan ID untuk mode tambah
                $('#modalKategoriLabel').text('Tambah Kategori');
                $('#modalKategori').modal('show');
            });

                // Submit form Tambah/Edit Kategori via AJAX
                $(document).on('submit', '#formKategori', function(e) {
                    e.preventDefault();
                    var url = $('#kategori_id').val() ? base_url + 'admin/kategori/update' : base_url + 'admin/kategori/tambah';
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: $(this).serialize(),
                        dataType: 'json',
                        success: function(res) {
                            if (res.status === 'success') {
                                showToast('Operasi kategori berhasil!', 'success');
                                $('#modalKategori').modal('hide');
                                tabelKategori.ajax.reload();
                            } else {
                                showToast(res.message || 'Gagal melakukan operasi kategori', 'danger');
                            }
                        },
                        error: function(xhr) {
                            showToast('Terjadi kesalahan saat melakukan operasi kategori!', 'danger');
                            console.log(xhr.responseText);
                        }
                    });
                });

                // Event handler untuk tombol edit kategori
            $(document).on('click', '.btn-edit-kategori', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                $.get(base_url + 'admin/kategori/get_kategori/' + id, function(res) {
                    if (res && res.id) {
                        $('#kategori_id').val(res.id);
                        $('#kategori_name').val(res.name);
                        $('#modalKategoriLabel').text('Edit Kategori');
                        $('#modalKategori').modal('show');
                    } else {
                            showToast('Kategori tidak ditemukan!', 'danger');
                    }
                }, 'json');
            });

                // Event handler untuk tombol hapus kategori (tampilkan modal konfirmasi)
            $(document).on('click', '.btn-hapus-kategori', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                    var nama = $(this).closest('tr').find('td:first').text();
                $('#hapus_id_kategori').val(id);
                $('#hapus_nama_kategori').text(nama);
                $('#modalHapusKategori').modal('show');
            });

            // Proses konfirmasi hapus kategori
            $('#btnKonfirmasiHapusKategori').on('click', function() {
                var id = $('#hapus_id_kategori').val();
                if (!id) {
                    showToast('ID kategori tidak ditemukan!', 'danger');
                    return;
                }
                $.ajax({
                    url: base_url + 'admin/kategori/hapus/' + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                                showToast('Kategori berhasil dihapus!', 'success');
                            $('#modalHapusKategori').modal('hide');
                            tabelKategori.ajax.reload();
                        } else {
                            showToast(res.message || 'Gagal menghapus kategori', 'danger');
                        }
                    },
                    error: function(xhr) {
                            showToast('Terjadi kesalahan saat menghapus kategori!', 'danger');
                            console.log(xhr.responseText);
                    }
                });
            });

                // Pastikan modal backdrop hilang setelah modal kategori (tambah/edit) ditutup
                $('#modalKategori').on('hidden.bs.modal', function () {
                    $('.modal-backdrop').remove();
                });

                // Pastikan modal backdrop hilang setelah modal hapus kategori ditutup
                $('#modalHapusKategori').on('hidden.bs.modal', function () {
                    $('.modal-backdrop').remove();
                });
            }

        });

        // Tambahkan CSS print khusus agar hanya #strukArea yang dicetak
        $('head').append(`
        <style>
        @media print {
          body * { visibility: hidden !important; }
          #strukArea, #strukArea * { visibility: visible !important; }
          #strukArea { display: block !important; position: absolute; left: 0; top: 0; width: 100% !important; background: #fff; }
          #btnCetakStruk { display: none !important; }
        }
        @media screen {
          #strukArea { display: none; }
        }
        </style>
        `);

        $(document).on('click', 'a.logout-trigger', function(e) {
                    e.preventDefault();
            $('#modalLogout').modal('show');
        });

        // Fitur filter produk real-time di daftar produk
        function cekProdukKosong() {
            const visibleProduk = $('.col-lg-4:visible, .col-md-6:visible, .col-12:visible').not('#dummyProdukFlex').length;
            if (visibleProduk === 0) {
                $('#pesanKosong').remove();
                $('#dummyProdukFlex').removeClass('d-none');
                $('.row.g-3').append(
                    '<div id="pesanKosong" class="text-center text-muted" style="width:100%;padding:2rem 0;">Tidak ada produk ditemukan</div>'
                );
            } else {
                $('#pesanKosong').remove();
                $('#dummyProdukFlex').addClass('d-none');
            }
        }

        $('#inputCariProduk').on('input', function() {
            const keyword = $(this).val().toLowerCase();
            $('.col-lg-4, .col-md-6, .col-12').each(function() {
                const nama = $(this).find('.card-title').text().toLowerCase();
                if (keyword === '' || nama.includes(keyword)) {
                    $(this).removeClass('d-none');
                } else {
                    $(this).addClass('d-none');
                }
            });
            cekProdukKosong();
        });

        // Submit form tambah produk via AJAX
        $(document).on('submit', '#formTambahProduk', function(e) {
                    e.preventDefault();
            $.post(base_url + 'admin/produk/tambah', $(this).serialize(), function(res) {
                if (res.status === 'success') {
                    $('#modalTambahProduk').modal('hide');
                    $('#tabelProduk').DataTable().ajax.reload();
                    showToast('Produk berhasil ditambahkan', 'success');
                } else {
                    showToast(res.message || 'Gagal menambah produk', 'danger');
                }
            }, 'json');
        });

        // Pastikan modal backdrop hilang setelah modal tambah produk ditutup
        $('#modalTambahProduk').on('hidden.bs.modal', function () {
            $('.modal-backdrop').remove();
        });

        // Tampilkan modal tambah produk
        $(document).on('click', '.btn-primary.mb-3', function(e) {
            e.preventDefault();
            $('#modalTambahProduk').modal('show');
        });

        // Tampilkan modal edit produk dan load data
        $(document).on('click', '.btn-warning', function(e) {
            e.preventDefault();
            var id = $(this).closest('tr').data('id') || $(this).data('id');
            if (!id) {
                // Coba ambil dari atribut data-id tombol
                id = $(this).attr('data-id');
            }
            if (!id) {
                // Coba ambil dari kolom tersembunyi jika ada
                id = $(this).parents('tr').find('td:first').text();
            }
            if (!id) return;
            $.get(base_url + 'admin/produk/get_produk/' + id, function(res) {
                        if (res && res.id) {
                    $('#edit_id').val(res.id);
                    $('#edit_name').val(res.name);
                    $('#edit_category_id').val(res.category_id);
                    $('#edit_price').val(res.price);
                    $('#edit_stock').val(res.stock);
                    $('#modalEditProduk').modal('show');
                        } else {
                    showToast('Gagal mengambil data produk', 'danger');
                        }
                    }, 'json');
                });

        // Submit form edit produk via AJAX
        $(document).on('submit', '#formEditProduk', function(e) {
                    e.preventDefault();
            $.post(base_url + 'admin/produk/update', $(this).serialize(), function(res) {
                        if (res.status === 'success') {
                    $('#modalEditProduk').modal('hide');
                    $('#tabelProduk').DataTable().ajax.reload();
                    showToast('Produk berhasil diupdate', 'success');
                        } else {
                    showToast(res.message || 'Gagal update produk', 'danger');
                        }
                    }, 'json');
                });

        // Pastikan modal backdrop hilang setelah modal edit produk ditutup
        $('#modalEditProduk').on('hidden.bs.modal', function () {
            $('.modal-backdrop').remove();
        });

        // Event: Cetak Struk
        $(document).on('click', '#btnCetakStruk', function() {
            if (!lastTransactionId) {
                showToast('ID transaksi tidak ditemukan untuk dicetak!', 'danger');
                return;
            }

            $.ajax({
                url: base_url + 'kasir/kasir/struk_data/' + lastTransactionId,
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        const data = res.data;
                        // Isi data ke template struk
                        $('#strukInvoice').text(data.invoice_code);
                        $('#strukTanggal').text(data.tanggal);
                        $('#strukKasir').text(data.kasir);
                        $('#strukMetode').text(data.metode);
                        
                        let itemsHtml = '';
                        data.items.forEach(item => {
                            // Tampilkan nama, qty, dan subtotal (dengan Rp)
                            itemsHtml += `<tr><td style="text-align:left;">${item.name} x${item.qty}</td><td style="text-align:right;">Rp ${(item.subtotal || 0).toLocaleString('id-ID')}</td></tr>`;
                        });
                        $('#strukProduk').html(itemsHtml);
                        
                        $('#strukTotal').text('Rp ' + (data.total || 0).toLocaleString('id-ID'));
                        $('#strukBayar').text('Rp ' + (data.bayar || 0).toLocaleString('id-ID'));
                        $('#strukKembali').text('Rp ' + (data.kembali || 0).toLocaleString('id-ID'));

                        // Tampilkan area struk, print, lalu sembunyikan lagi
                        $('#strukArea').show();
                        window.print();
                        $('#strukArea').hide();

                    } else {
                        showToast('Gagal mengambil data struk: ' + res.message, 'danger');
                    }
                },
                error: function() {
                    showToast('Terjadi kesalahan saat mengambil data struk.', 'danger');
                }
            });
        });

        // Tampilkan modal konfirmasi hapus produk
        $(document).on('click', '.btn-hapus-produk', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    var row = $(this).closest('tr');
                    var nama = row.find('td').eq(0).text();
            $('#hapus_id_produk').val(id);
            $('#hapus_nama_produk').text(nama);
            $('#modalHapusProduk').modal('show');
        });
        // Proses konfirmasi hapus
        $('#btnKonfirmasiHapus').on('click', function() {
            var id = $('#hapus_id_produk').val();
                    if (!id) {
                showToast('ID produk tidak ditemukan!', 'danger');
                        return;
                    }
                    $.ajax({
                url: base_url + 'admin/produk/hapus/' + id,
                        type: 'POST',
                        dataType: 'json',
                        success: function(res) {
                            if (res.status === 'success') {
                        $('#modalHapusProduk').modal('hide');
                        $('#tabelProduk').DataTable().ajax.reload();
                        showToast('Produk berhasil dihapus', 'success');
                            } else {
                        showToast(res.message || 'Gagal menghapus produk', 'danger');
                            }
                        },
                        error: function(xhr) {
                    showToast('Gagal menghapus produk: ' + xhr.responseText, 'danger');
                }
            });
        });

        // Handler tombol Lihat Draft (kasir/transaksi)
        $(document).off('click', '#btnLihatDraft').on('click', '#btnLihatDraft', function() {
            $.get(base_url + 'kasir/kasir/daftar_draft', function(res) {
                let html = '';
                if (res.status === 'success' && res.data.length > 0) {
                    res.data.forEach(function(draft) {
                        html += `
                            <tr>
                                <td>${draft.invoice_code || '-'}</td>
                                <td>${draft.created_at || '-'}</td>
                                <td>Rp ${parseInt(draft.total_amount || 0).toLocaleString('id-ID')}</td>
                                <td>
                                    <button class="btn btn-sm btn-success btn-lanjut-draft" data-id="${draft.id}">Lanjutkan</button>
                                    <button class="btn btn-sm btn-danger btn-hapus-draft" data-id="${draft.id}">Hapus</button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="4" class="text-center text-muted">Belum ada draft transaksi</td></tr>';
                }
                $('#tableDraft tbody').html(html);
                $('#modalDraft').modal('show');
            }, 'json');
        });

        // Handler tombol Lanjutkan Draft (pakai modal Bootstrap)
        let selectedDraftId = null;
        $(document).on('click', '.btn-lanjut-draft', function() {
            selectedDraftId = $(this).data('id');
            $('#modalKonfirmasiDraft').modal('show');
        });

        // Konfirmasi lanjutkan draft
        $(document).on('click', '#btnKonfirmasiLanjutDraft', function() {
            if (!selectedDraftId) return;
            $.get(base_url + 'kasir/kasir/detail_draft?id=' + selectedDraftId, function(res) {
                if (res.status === 'success' && res.data) {
                    cart = res.data.map(function(item) {
                        return {
                            id: parseInt(item.product_id),
                            name: item.product_name,
                            price: parseInt(item.unit_price),
                            qty: parseInt(item.quantity),
                            subtotal: parseInt(item.unit_price) * parseInt(item.quantity)
                        };
                    });
                    // Pastikan fungsi renderCart tersedia dalam scope ini
                    if (typeof window.renderCart === 'function') {
                        window.renderCart();
                    } else {
                        // Gunakan fungsi renderCart yang didefinisikan dalam document.ready
                        const $tbody = $('.card-body table tbody');
                        $tbody.empty();
                        let total = 0;
                        
                        if (cart.length === 0) {
                            $tbody.append('<tr><td colspan="4" class="text-center text-muted py-4">Belum ada item</td></tr>');
                            $('.card-footer .btn-success, .card-footer .btn-outline-secondary').prop('disabled', true);
                            $('#btnCetakStruk').hide();
                        } else {
                            cart.forEach((item, idx) => {
                                // Pastikan nilai valid
                                const qty = parseInt(item.qty) || 0;
                                const price = parseInt(item.price) || 0;
                                const subtotal = qty * price;
                                
                                // Update item dengan nilai yang valid
                                item.qty = qty;
                                item.price = price;
                                item.subtotal = subtotal;
                                
                                total += subtotal;
                                
                                $tbody.append(`
                                    <tr>
                                        <td>${item.name}</td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center align-items-center gap-1">
                                                <button class="btn btn-sm btn-light border qty-minus" data-idx="${idx}">-</button>
                                                <span class="mx-1">${qty}</span>
                                                <button class="btn btn-sm btn-light border qty-plus" data-idx="${idx}">+</button>
                                            </div>
                                        </td>
                                        <td class="text-end">Rp ${(subtotal || 0).toLocaleString('id-ID')}</td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-danger btn-remove" data-idx="${idx}"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                `);
                            });
                            $('.card-footer .btn-success, .card-footer .btn-outline-secondary').prop('disabled', false);
                        }
                        
                        $('.card-footer .fs-5.text-primary').text('Rp ' + (total || 0).toLocaleString('id-ID'));
                        if (typeof syncCustomerDisplay === 'function') {
                            syncCustomerDisplay();
                        }
                    }
                    state.transaction_id = selectedDraftId;
                    $('#modalDraft').modal('hide');
                    $('#modalKonfirmasiDraft').modal('hide');
                    showToast('Draft berhasil dimuat ke keranjang', 'success');
                } else {
                    showToast(res.message || 'Gagal memuat draft', 'danger');
                }
            }, 'json');
        });

        // Handler tombol Hapus Draft (pakai modal Bootstrap)
        $(document).on('click', '.btn-hapus-draft', function() {
            selectedDraftId = $(this).data('id');
            $('#modalHapusDraft').modal('show');
        });

        // Konfirmasi hapus draft
        $(document).on('click', '#btnKonfirmasiHapusDraft', function() {
            if (!selectedDraftId) return;
            $.post(base_url + 'kasir/kasir/hapus_draft/' + selectedDraftId, function(res) {
                if (res.status === 'success') {
                    showToast('Draft berhasil dihapus', 'success');
                    // Reload daftar draft
                    $('#btnLihatDraft').trigger('click');
                } else {
                    showToast(res.message || 'Gagal menghapus draft', 'danger');
                }
                $('#modalHapusDraft').modal('hide');
            }, 'json');
        });
        // Handler tombol Bayar (kasir/transaksi)
        $(document).on('click', '.btn-success.w-50:not([disabled])', function() {
            $('#modalBayar').modal('show');
        });

        // Function to load payment methods
        function loadPaymentMethods() {
            $.ajax({
                url: base_url + 'kasir/transaksi/get_payment_methods',
                method: 'GET',
                dataType: 'json',
                success: function(payments) {
                    const $select = $('#bayarMetode');
                    $select.find('option:not(:first)').remove(); // Remove all options except first
                    
                    payments.forEach(function(payment) {
                        $select.append(`<option value="${payment.id}">${payment.name}</option>`);
                    });
                },
                error: function() {
                    console.log('Failed to load payment methods');
                }
            });
        }
        
        // Load payment methods when page loads
        $(document).ready(function() {
            loadPaymentMethods();
        });

        // Isi total belanja dan reset field bayar/kembalian saat modal pembayaran dibuka
        $(document).on('show.bs.modal', '#modalBayar', function() {
            const total = (typeof cart !== 'undefined') ? cart.reduce((sum, item) => sum + (parseInt(item.subtotal) || 0), 0) : 0;
            $('#bayarTotal').val('Rp ' + total.toLocaleString('id-ID'));
            $('#bayarNominal').val('');
            $('#bayarKembalian').val('');
            
            // Load payment methods dynamically
            loadPaymentMethods();
        });
        // Hitung kembalian saat nominal bayar diinput
        $(document).on('input', '#bayarNominal', function() {
            const total = (typeof cart !== 'undefined') ? cart.reduce((sum, item) => sum + (parseInt(item.subtotal) || 0), 0) : 0;
            const bayar = parseInt($(this).val()) || 0;
            const kembalian = bayar - total;
            $('#bayarKembalian').val(kembalian >= 0 ? 'Rp ' + kembalian.toLocaleString('id-ID') : '');
        });

        function syncCustomerDisplay() {
            if (window._syncDisplayLock) return; // Guard agar tidak overlap
            window._syncDisplayLock = true;
            $.post(base_url + 'kasir/kasir/update_display', {
                cart: JSON.stringify(cart),
                total: cart.reduce((sum, item) => sum + (parseInt(item.subtotal) || 0), 0)
            }).always(function() {
                window._syncDisplayLock = false;
            });
        }

        function cetakStruk(transactionId) {
            console.log('Fungsi cetakStruk dipanggil', transactionId);
            // Ambil data transaksi terakhir dari backend
            if (!transactionId) {
                showToast('ID transaksi tidak ditemukan!', 'danger');
                return;
            }
            $.get(base_url + 'kasir/kasir/struk_data/' + transactionId, function(res) {
                if (res.status === 'success') {
                    // Isi data struk
                    $('#strukInvoice').text(res.data.invoice_code || '-');
                    $('#strukTanggal').text(res.data.tanggal || '-');
                    $('#strukKasir').text(res.data.kasir || '-');
                    $('#strukMetode').text(res.data.metode || '-');
                    let produkHtml = '';
                    if (res.data.items && res.data.items.length > 0) {
                        res.data.items.forEach(function(item) {
                            produkHtml += `<tr><td>${item.name}</td><td class="text-end">Rp ${(item.subtotal||0).toLocaleString('id-ID')}</td></tr>`;
                        });
                    }
                    $('#strukProduk').html(produkHtml);
                    $('#strukTotal').text('Rp ' + (res.data.total || 0).toLocaleString('id-ID'));
                    $('#strukBayar').text('Rp ' + (res.data.bayar || 0).toLocaleString('id-ID'));
                    $('#strukKembali').text('Rp ' + (res.data.kembali || 0).toLocaleString('id-ID'));
                    // Tampilkan dan print struk
                    $('#strukArea').show();
                    window.print();
                    $('#strukArea').hide();
                } else {
                    showToast('Gagal mengambil data struk', 'danger');
                }
            }, 'json');
        }
    </script>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
      <div id="toastNotif" class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body"></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    </div>
</body>
</html>
