<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header {
            width: 100%;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            padding: 0;
            vertical-align: top;
        }
        .logo-container {
            width: 100px;
        }
        .logo {
            width: 95px;
            height: auto;
        }
        .info-container {
            text-align: right;
        }
        .info-container h1 {
            margin: 0;
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .info-container p {
            margin: 4px 0 0 0;
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 12px;
            color: #666;
            line-height: 1.5;
        }
        .judul {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 18px 0 8px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: none; /* Hapus semua border default */
        }
        th {
            background: #f7e7b5;
            color: #7c5c00;
            border-bottom: 2px solid #e0c068; /* Garis bawah tebal untuk header */
        }
        tbody td {
            border-bottom: 1px solid #f0f0f0; /* Garis bawah tipis untuk setiap sel data */
        }
        .total-row td {
            font-weight: bold;
            background: #f9f9f9;
            border-bottom: none; /* Hapus garis bawah untuk baris total */
            border-top: 2px solid #e0c068; /* Garis atas tebal untuk pemisah */
        }
        .footer {
            text-align: right;
            font-size: 11px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-container">
                    <img src="<?= $logo ?>" class="logo" />
                </td>
                <td class="info-container">
                    <h1><?= htmlspecialchars($info['nama']) ?></h1>
                    <p>
                        <?= htmlspecialchars($info['alamat']) ?><br>
                        Telp: <?= htmlspecialchars($info['telp']) ?> | Instagram: <?= htmlspecialchars($info['ig']) ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>
    <div class="judul">
        LAPORAN PENJUALAN<br>
        Periode: <?= date('F', mktime(0,0,0,$bulan,1)) ?> <?= $tahun ?>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:90px">Tanggal</th>
                <th>No. Transaksi</th>
                <th>Kasir</th>
                <th>Jenis Pembayaran</th>
                <th style="width:110px">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $grand = 0; foreach($list as $l): $grand += $l->total_amount; ?>
            <tr>
                <td><?= date('d-m-Y', strtotime($l->created_at)) ?></td>
                <td><?= htmlspecialchars($l->invoice_code) ?></td>
                <td><?= htmlspecialchars($l->kasir) ?></td>
                <td><?= htmlspecialchars($l->jenis_pembayaran) ?></td>
                <td style="text-align:right">Rp <?= number_format($l->total_amount,0,',','.') ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="4" style="text-align:right">Total</td>
                <td style="text-align:right">Rp <?= number_format($grand,0,',','.') ?></td>
            </tr>
        </tbody>
    </table>
    <div class="footer">
        Dicetak pada: <?= date('d-m-Y H:i') ?>
    </div>
</body>
</html> 