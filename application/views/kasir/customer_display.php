<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Display - Mama Bakery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #fffbe8;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .display-container {
            max-width: 420px;
            margin: 0 auto;
            padding: 2.5rem 1rem 1.5rem 1rem;
        }
        .logo {
            max-width: 120px;
            margin-bottom: 0.5rem;
        }
        .toko {
            font-size: 1.5rem;
            font-weight: bold;
            color: #b8860b;
        }
        .alamat {
            font-size: 1.05rem;
            color: #555;
            margin-bottom: 0.7rem;
        }
        .table-items th, .table-items td {
            font-size: 1.25rem;
            padding: 0.5rem 0.3rem;
        }
        .table-items th {
            color: #b8860b;
            border-bottom: 2px solid #b8860b;
        }
        .total-row td {
            font-size: 1.5rem;
            font-weight: bold;
            color: #198754;
            border-top: 2px solid #b8860b;
        }
        .ucapan {
            margin-top: 2.2rem;
            font-size: 1.25rem;
            text-align: center;
            color: #b8860b;
            font-weight: 500;
        }
        .website {
            font-size: 1.1rem;
            text-align: center;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="display-container text-center">
        <img src="<?= base_url('assets/img/logo-mama-bakery.png') ?>" class="logo" alt="Logo Mama Bakery">
        <div class="toko">Mama Bakery & Cafe</div>
        <div class="alamat">Jl.Durian 1 no 37 Mama bakery samping gedung badminton</div>
        <table class="table table-items table-borderless w-100 mb-2">
            <thead>
                <tr>
                    <th class="text-start">Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody id="displayItems">
                <tr><td colspan="3" class="text-center text-muted">Belum ada transaksi</td></tr>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2" class="text-end">TOTAL</td>
                    <td class="text-end" id="displayTotal">Rp 0</td>
                </tr>
            </tfoot>
        </table>
        <div class="ucapan">Terima kasih atas kunjungan Anda!</div>
        <div class="website">www.mamabakery.com</div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function updateDisplay() {
        $.get('<?= base_url('kasir/kasir/get_display_db') ?>', function(res) {
            if (res.status === 'success') {
                let cart = [];
                if (res.data.cart) {
                    if (typeof res.data.cart === 'string') {
                        cart = JSON.parse(res.data.cart);
                    } else {
                        cart = res.data.cart;
                    }
                }
                const total = res.data.total || 0;
                const $tbody = $('#displayItems');
                $tbody.empty();
                if (cart.length === 0) {
                    $tbody.append('<tr><td colspan="3" class="text-center text-muted">Belum ada transaksi</td></tr>');
                } else {
                    cart.forEach(function(item) {
                        $tbody.append(`
                            <tr>
                                <td class="text-start">${item.name}</td>
                                <td class="text-center">${item.qty}</td>
                                <td class="text-end">Rp ${(item.subtotal||0).toLocaleString('id-ID')}</td>
                            </tr>
                        `);
                    });
                }
                $('#displayTotal').text('Rp ' + (total||0).toLocaleString('id-ID'));
            }
        }, 'json');
    }
    setInterval(updateDisplay, 1000);
    updateDisplay();
    </script>
</body>
</html> 