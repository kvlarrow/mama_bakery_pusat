<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kasir extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in') || strtolower($this->session->userdata('role')) !== 'kasir') {
            redirect('auth');
        }
    }

    public function dashboard() {
        $data['title'] = 'Dashboard Kasir';
        $data['user'] = $this->session->userdata();
        $this->load->view('templates/header', $data);
        $this->load->view('kasir/dashboard', $data);
        $this->load->view('templates/footer');
    }

    public function simpan_draft() {
        header('Content-Type: application/json');
        if (!$this->input->is_ajax_request()) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            return;
        }
        $user_id = $this->session->userdata('user_id');
        $cart = json_decode($this->input->post('cart'), true);
        $total = $this->input->post('total');
        if (!$user_id || !is_array($cart) || !$total) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
            return;
        }
        $this->load->model('Transaksi_model');
        $transaction_id = $this->Transaksi_model->simpan_draft($user_id, $cart, $total);
        if ($transaction_id) {
            echo json_encode(['status' => 'success', 'message' => 'Draft transaksi berhasil disimpan', 'transaction_id' => $transaction_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan draft transaksi']);
        }
    }

    public function daftar_draft() {
        header('Content-Type: application/json');
        $user_id = $this->session->userdata('user_id');
        $this->load->model('Transaksi_model');
        $drafts = $this->Transaksi_model->get_all_draft($user_id);
        echo json_encode(['status' => 'success', 'data' => $drafts]);
    }

    public function detail_draft() {
        header('Content-Type: application/json');
        $transaction_id = $this->input->get('id');
        if (!$transaction_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID draft tidak ditemukan']);
            return;
        }
        $this->load->model('Transaksi_model');
        $items = $this->Transaksi_model->get_draft_items($transaction_id);
        echo json_encode(['status' => 'success', 'data' => $items]);
    }

    public function hapus_draft() {
        header('Content-Type: application/json');
        if (!$this->input->is_ajax_request()) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            return;
        }
        $id = $this->input->post('id');
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID draft tidak ditemukan']);
            return;
        }
        $this->load->model('Transaksi_model');
        $this->db->trans_start();
        $this->db->where('transaction_id', $id)->delete('transaction_items');
        $this->db->where('id', $id)->delete('transactions');
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus draft']);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Draft berhasil dihapus']);
        }
    }

        public function checkout() {
            header('Content-Type: application/json');
            if (!$this->input->is_ajax_request()) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
                return;
            }
            $transaction_id = $this->input->post('transaction_id');
            $total = $this->input->post('total');
            $bayar = $this->input->post('bayar');
            $metode = $this->input->post('metode');
            if (!$transaction_id || !$total || !$bayar || !$metode) {
                echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
                return;
            }
            $this->load->model('Transaksi_model');
            $result = $this->Transaksi_model->checkout($transaction_id, $metode, $bayar, $total);
            echo json_encode($result);
        }

    public function detail_transaksi() {
        header('Content-Type: application/json');
        $transaction_id = $this->input->get('id');
        if (!$transaction_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID transaksi tidak ditemukan']);
            return;
        }
        $this->load->model('Transaksi_model');
        $trx = $this->db->select('t.*, u.name as kasir')
            ->from('transactions t')
            ->join('users u', 'u.id = t.user_id', 'left')
            ->where('t.id', $transaction_id)
            ->get()->row_array();
        $items = $this->Transaksi_model->get_draft_items($transaction_id);
        if ($trx) {
            $trx['items'] = $items;
            echo json_encode(['status' => 'success', 'data' => $trx]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Transaksi tidak ditemukan']);
        }
    }

    public function update_display() {
        header('Content-Type: application/json');
        if (!$this->input->is_ajax_request()) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            return;
        }
        $cart = $this->input->post('cart');
        $total = $this->input->post('total');
        $data = json_encode(['cart' => $cart, 'total' => $total]);
        $this->db->replace('customer_display', [
            'id' => 1,
            'data' => $data,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        echo json_encode(['status' => 'success']);
    }

    public function get_display_db() {
        $row = $this->db->get_where('customer_display', ['id' => 1])->row();
        $data = $row ? json_decode($row->data, true) : ['cart' => '[]', 'total' => 0];
        echo json_encode(['status' => 'success', 'data' => $data]);
    }

    public function set_display() {
        $cart = $this->input->post('cart');
        $total = $this->input->post('total');
        $this->session->set_userdata('customer_display_cart', $cart);
        $this->session->set_userdata('customer_display_total', $total);
        echo json_encode(['status' => 'success']);
    }
    public function get_display() {
        $cart = $this->session->userdata('customer_display_cart') ?: '[]';
        $total = $this->session->userdata('customer_display_total') ?: 0;
        echo json_encode([
            'status' => 'success',
            'data' => [
                'cart' => $cart,
                'total' => $total
            ]
        ]);
    }

    public function customer_display() {
        $this->load->view('kasir/customer_display');
    }

    public function riwayat()
    {
        $this->load->model('Transaksi_model');
        $tanggal = $this->input->get('tanggal');
        $kasir_id = $this->session->userdata('user_id');
        $data['title'] = 'Riwayat Transaksi';
        $data['tanggal'] = $tanggal;
        $data['riwayat'] = $this->Transaksi_model->get_riwayat($kasir_id, $tanggal);
        $this->load->view('templates/header', $data);
        $this->load->view('kasir/riwayat', $data);
        $this->load->view('templates/footer');
    }

    public function riwayat_data() {
        header('Content-Type: application/json');
        $this->load->model('Transaksi_model');

        $tanggal = $this->input->post('tanggal');
        $kasir_id = $this->session->userdata('user_id');

        $list = $this->Transaksi_model->get_datatables_riwayat($kasir_id, $tanggal);
        $data = [];
        $no = $this->input->post('start');

        foreach ($list as $trx) {
            $no++;
            $row = [];
            $row[] = $no;
            $row[] = date('d/m/Y H:i', strtotime($trx->created_at));
            $row[] = $trx->invoice_code;
            $row[] = 'Rp ' . number_format($trx->total_amount, 0, ',', '.');
            $row[] = $trx->payment_method_name;
            $row[] = '<button type="button" class="btn btn-sm btn-info btn-detail-transaksi" data-id="' . $trx->id . '"><i class="bi bi-eye"></i> Detail</button>';
            $data[] = $row;
        }

        $output = [
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->Transaksi_model->count_all_riwayat($kasir_id, $tanggal),
            "recordsFiltered" => $this->Transaksi_model->count_filtered_riwayat($kasir_id, $tanggal),
            "data" => $data,
        ];

        echo json_encode($output);
    }

    public function get_transaction_details() {
        header('Content-Type: application/json');
        $transaction_id = $this->input->post('id');

        if (!$transaction_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID transaksi tidak ditemukan.']);
            return;
        }

        $this->load->model('Transaksi_model');
        $transaction_details = $this->Transaksi_model->get_transaction_with_items($transaction_id);

        if ($transaction_details) {
            echo json_encode(['status' => 'success', 'data' => $transaction_details]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Transaksi tidak ditemukan.']);
        }
    }

    public function struk($transaction_id = null) {
        if (!$transaction_id) show_404();
        $this->load->model('Transaksi_model');
        $trx = $this->Transaksi_model->get_transaction($transaction_id);
        if (!$trx) show_404();
        $items = $this->Transaksi_model->get_transaction_items($transaction_id);
        $data = [
            'trx' => $trx,
            'items' => $items
        ];
        $this->load->view('kasir/struk', $data);
    }

    public function struk_data($transaction_id = null) {
        if (!$transaction_id) {
            echo json_encode(['status' => 'error', 'message' => 'ID transaksi tidak ditemukan']);
            return;
        }
        $this->load->model('Transaksi_model');
        $trx = $this->Transaksi_model->get_transaction($transaction_id);
        if (!$trx) {
            echo json_encode(['status' => 'error', 'message' => 'Transaksi tidak ditemukan']);
            return;
        }
        $items = $this->Transaksi_model->get_transaction_items($transaction_id);
        $data = [
            'invoice_code' => $trx->invoice_code ?? '-',
            'tanggal' => date('d-m-Y H:i', strtotime($trx->created_at ?? '')),
            'kasir' => $trx->kasir ?? '-',
            'metode' => $trx->payment_method ?? '-',
            'items' => array_map(function($item) {
                return [
                    'name' => $item->product_name,
                    'qty' => $item->quantity, // tambahkan qty
                    'subtotal' => $item->subtotal
                ];
            }, $items),
            'total' => $trx->total_amount ?? 0,
            'bayar' => $trx->paid_amount ?? 0,
            'kembali' => $trx->change_amount ?? 0
        ];
        echo json_encode(['status' => 'success', 'data' => $data]);
    }
} 