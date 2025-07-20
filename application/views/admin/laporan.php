<?php $this->load->view('templates/header', [ 'title' => 'Laporan Penjualan' ]); ?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">        
        <div>
            <form class="d-flex align-items-center gap-2" id="filterLaporan">
                <select class="form-select" id="filterBulan" name="bulan" style="width:auto;">
                    <?php for($b=1;$b<=12;$b++): ?>
                        <option value="<?= $b ?>" <?= $b == date('n') ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$b,1)) ?></option>
                    <?php endfor; ?>
                </select>
                <select class="form-select" id="filterTahun" name="tahun" style="width:auto;">
                    <?php for($t=date('Y')-3;$t<=date('Y');$t++): ?>
                        <option value="<?= $t ?>" <?= $t == date('Y') ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-outline-primary">Tampilkan</button>
                <button type="button" class="btn btn-danger" id="btnPrintPDF"><i class="bi bi-file-earmark-pdf"></i> Print PDF</button>
                <button type="button" class="btn btn-success" id="btnExportExcel"><i class="bi bi-file-earmark-excel"></i> Export Excel</button>
            </form>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabelLaporan" class="table table-bordered table-hover table-striped w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>No. Transaksi</th>
                            <th>Kasir</th>
                            <th>Jenis Pembayaran</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($data) && is_array($data)): foreach($data as $row): ?>
                        <tr>
                            <td><?= $row['tanggal'] ?></td>
                            <td><?= $row['no_transaksi'] ?></td>
                            <td><?= $row['kasir'] ?></td>
                            <td><?= $row['jenis_pembayaran'] ?></td>
                            <td><?= $row['total'] ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('templates/footer'); ?>
<script>
    $(document).ready(function() {
        const tabelLaporan = $('#tabelLaporan').DataTable({
            "destroy": true,
            "processing": true,
            "serverSide": true,
            "order": [],
            "ajax": {
                "url": "<?= base_url('admin/laporan/data') ?>",
                "type": "POST",
                "data": function(d) {
                    d.bulan = $('#filterBulan').val();
                    d.tahun = $('#filterTahun').val();
                }
            },
            "columns": [
                { "data": "tanggal" },
                { "data": "no_transaksi" },
                { "data": "kasir" },
                { "data": "jenis_pembayaran" },
                { "data": "total" }
            ],
            "columnDefs": [{
                "targets": [0, 1, 2, 3, 4],
                "orderable": false,
            }],
        });

        $('#filterLaporan').on('submit', function(e) {
            e.preventDefault();
            tabelLaporan.ajax.reload();
        });

        $('#btnPrintPDF').on('click', function() {
            const bulan = $('#filterBulan').val();
            const tahun = $('#filterTahun').val();
            const url = `<?= base_url('admin/laporan/print_pdf') ?>?bulan=${bulan}&tahun=${tahun}`;
            window.open(url, '_blank');
        });

        $('#btnExportExcel').on('click', function() {
            const bulan = $('#filterBulan').val();
            const tahun = $('#filterTahun').val();
            const url = `<?= base_url('admin/laporan/export_excel') ?>?bulan=${bulan}&tahun=${tahun}`;
            window.location.href = url;
        });
    });
</script> 