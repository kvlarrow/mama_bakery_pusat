<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kasir extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('transaksi_model');
        // Disable browser caching for this API
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
    }

    public function transaksi_aktif() {
        // In a real implementation, you would get the user_id from the session
        // For demo purposes, we'll use a hardcoded user_id
        $user_id = 1; // This should come from session in production
        
        $transaksi = $this->transaksi_model->get_transaksi_aktif($user_id);
        
        if ($transaksi) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($this->_format_display_data($transaksi)));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'no_transaction']));
        }
    }
    
    private function _format_display_data($transaksi) {
        $html = '\
            <div class="text-center p-4">
                <h3 class="logo">Mama Bakery</h3>
                <p>Jl. Contoh No. 123, Kota</p>
                <p>'.date('d/m/Y H:i:s').'</p>
                <hr>
                <div class="text-start">';
        
        if (isset($transaksi->transaction_items) && !empty($transaksi->transaction_items)) {
            foreach ($transaksi->transaction_items as $item) {
                $html .= '\
                    <div class="d-flex justify-content-between mb-2">
                        <span>'.$item->product_name.' x'.$item->quantity.'</span>
                        <span>Rp '.number_format($item->total_price, 0, ',', '.').'</span>
                    </div>';
            }
            
            $html .= '\
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total:</span>
                        <span>Rp '.number_format($transaksi->total_amount, 0, ',', '.').'</span>
                    </div>';
        } else {
            $html .= '<p>Belum ada item dalam transaksi</p>';
        }
        
        $html .= '\
                </div>
            </div>';
        
        return ['html' => $html];
    }

    public function selesaikan_pembayaran() {
        $this->output->set_content_type('application/json');

        // Ambil data dari POST request
        $transaction_id = $this->input->post('transaction_id');
        $payment_id = $this->input->post('payment_id');
        $paid_amount = $this->input->post('paid_amount');
        $total_amount = $this->input->post('total_amount'); // Total dari transaksi

        // Validasi input
        if (empty($transaction_id) || empty($payment_id) || !is_numeric($paid_amount) || !is_numeric($total_amount)) {
            $this->output->set_output(json_encode(['status' => 'error', 'message' => 'Data tidak lengkap atau tidak valid.']));
            return;
        }

        // Panggil model untuk checkout
        $result = $this->transaksi_model->checkout($transaction_id, $payment_id, $paid_amount, $total_amount);

        // Kirim hasil kembali ke client
        $this->output->set_output(json_encode($result));
    }
}
