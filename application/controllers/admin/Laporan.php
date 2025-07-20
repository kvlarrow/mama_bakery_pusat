<?php
class Laporan extends CI_Controller {
    public function index() {
        $this->load->view('admin/laporan');
    }

    public function data() {
        header('Content-Type: application/json');
        $bulan = $this->input->post('bulan') ?: date('n');
        $tahun = $this->input->post('tahun') ?: date('Y');
        $this->load->model('Laporan_model');
        $list = $this->Laporan_model->get_datatables($bulan, $tahun);
        $data = [];
        foreach ($list as $l) {
            $row = [];
            $row['tanggal'] = date('d-m-Y', strtotime($l->created_at));
            $row['no_transaksi'] = $l->invoice_code;
            $row['kasir'] = $l->kasir;
            $row['jenis_pembayaran'] = $l->jenis_pembayaran;
            $row['total'] = 'Rp ' . number_format($l->total_amount, 0, ',', '.');
            $data[] = $row;
        }
        $draw = intval($this->input->post('draw'));
        if ($draw < 1) $draw = 1;
        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $this->Laporan_model->count_all($bulan, $tahun),
            "recordsFiltered" => $this->Laporan_model->count_filtered($bulan, $tahun),
            "data" => $data
        ]);
    }

    public function print_pdf() {
        $bulan = $this->input->get('bulan') ?: date('n');
        $tahun = $this->input->get('tahun') ?: date('Y');
        $this->load->model('Laporan_model');
        $data['list'] = $this->Laporan_model->get_datatables($bulan, $tahun);
        $data['bulan'] = $bulan;
        $data['tahun'] = $tahun;
        $data['logo'] = base_url('assets/img/logo-mama-bakery.png');
        $data['info'] = [
            'nama' => 'Mama Bakery',
            'alamat' => 'Bandara Internasional Kalimarau Berau',
            'telp' => '081347576996, 08115441993',
            'ig' => 'mamabakery_berau'
        ];
        // Load view ke string
        $html = $this->load->view('admin/laporan_pdf', $data, true);
        // Load mPDF
        require_once(APPPATH.'../vendor/autoload.php');
        $mpdf = new \Mpdf\Mpdf(['format' => 'A4']);
        $mpdf->SetTitle('Laporan Penjualan Mama Bakery');
        $mpdf->WriteHTML($html);
        $mpdf->Output('Laporan-Penjualan-Mama-Bakery.pdf', 'I');
    }
    public function export_excel() {
        $bulan = $this->input->get('bulan') ?: date('n');
        $tahun = $this->input->get('tahun') ?: date('Y');
        $this->load->model('Laporan_model');
        $list = $this->Laporan_model->get_datatables($bulan, $tahun);
        require_once APPPATH.'../vendor/autoload.php';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Header toko
        $sheet->setCellValue('A1', 'Mama Bakery');
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        $sheet->mergeCells('A3:E3');
        $sheet->mergeCells('A4:E4');
        $sheet->getStyle('A1:A4')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        // Judul laporan
        $sheet->setCellValue('A6', 'LAPORAN PENJUALAN');
        $sheet->mergeCells('A6:E6');
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
        $sheet->setCellValue('A7', 'Periode: '.date('F', mktime(0,0,0,$bulan,1)).' '.$tahun);
        $sheet->mergeCells('A7:E7');
        $sheet->getStyle('A7')->getAlignment()->setHorizontal('center');
        // Header tabel
        $sheet->setCellValue('A9', 'Tanggal');
        $sheet->setCellValue('B9', 'No. Transaksi');
        $sheet->setCellValue('C9', 'Kasir');
        $sheet->setCellValue('D9', 'Jenis Pembayaran');
        $sheet->setCellValue('E9', 'Total');
        $sheet->getStyle('A9:E9')->getFont()->setBold(true);
        $sheet->getStyle('A9:E9')->getFill()->setFillType('solid')->getStartColor()->setRGB('F7E7B5');
        $sheet->getStyle('A9:E9')->getAlignment()->setHorizontal('center');
        // Data
        $row = 10; $grand = 0;
        foreach($list as $l) {
            $sheet->setCellValue('A'.$row, date('d-m-Y', strtotime($l->created_at)));
            $sheet->setCellValue('B'.$row, $l->invoice_code);
            $sheet->setCellValue('C'.$row, $l->kasir);
            $sheet->setCellValue('D'.$row, $l->jenis_pembayaran);
            $sheet->setCellValue('E'.$row, $l->total_amount);
            $sheet->getStyle('E'.$row)->getNumberFormat()->setFormatCode('#,##0');
            $grand += $l->total_amount;
            $row++;
        }
        // Total
        $sheet->setCellValue('D'.$row, 'Total');
        $sheet->setCellValue('E'.$row, $grand);
        $sheet->getStyle('D'.$row.':E'.$row)->getFont()->setBold(true);
        $sheet->getStyle('E'.$row)->getNumberFormat()->setFormatCode('#,##0');
        // Border
        $sheet->getStyle('A9:E'.($row))->getBorders()->getAllBorders()->setBorderStyle('thin');
        // Lebar kolom
        foreach(['A'=>12,'B'=>22,'C'=>18,'D'=>18,'E'=>16] as $col=>$w) $sheet->getColumnDimension($col)->setWidth($w);
        // Download
        $filename = 'Laporan-Penjualan-'.date('F-Y', mktime(0,0,0,$bulan,1,$tahun)).'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}