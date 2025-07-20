<div class="container-fluid py-4">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">Selamat Datang, <?= html_escape($user['name']) ?></h5>
            <p class="text-muted">Anda login sebagai <?= ucfirst(html_escape($user['role'])) ?></p>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow h-100 py-2 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-uppercase mb-1">Total Produk</h6>
                            <h2 class="mb-0 fw-bold"><?= isset($total_products) ? $total_products : '0' ?></h2>
                        </div>
                        <i class="bi bi-box-seam opacity-50" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow h-100 py-2 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-uppercase mb-1">Total Transaksi Hari Ini</h6>
                            <h2 class="mb-0 fw-bold"><?= $transaction_count_today ?></h2>
                        </div>
                        <i class="bi bi-cart-check opacity-50" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow h-100 py-2 bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-uppercase mb-1">Pendapatan Hari Ini</h6>
                            <h2 class="mb-0 fw-bold">Rp <?= number_format($today_income ?? 0, 0, ',', '.') ?></h2>
                        </div>
                        <i class="bi bi-cash-coin opacity-50" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow h-100 py-2 bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-uppercase mb-1">Stok Hampir Habis</h6>
                            <h2 class="mb-0">8</h2>
                        </div>
                        <i class="bi bi-exclamation-triangle opacity-50" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold">Grafik Penjualan 7 Hari Terakhir</h6>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold">Produk Terlaris</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (!empty($top_products)): ?>
                            <?php foreach ($top_products as $prod): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($prod->name) ?>
                                    <span class="badge bg-primary rounded-pill"><?= $prod->total_sold ?? 0 ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-center">Belum ada data</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Data grafik penjualan 7 hari terakhir
    const incomeStats = <?= json_encode($income_stats ?? []) ?>;
    const labels = incomeStats.map(row => row.tanggal);
    const data = incomeStats.map(row => parseInt(row.total));
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: data,
                borderColor: '#1677ff',
                backgroundColor: 'rgba(22,119,255,0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
</script>
