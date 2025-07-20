<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaksi extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'kasir') {
            redirect('auth');
        }
        $this->load->model('transaksi_model');
        $this->load->model('product_model');
    }

    public function index() {
        $data['title'] = 'Kasir';
        $data['products'] = $this->product_model->get_active_products();
        
        // Get payment methods directly from database
        $this->db->order_by('name', 'ASC');
        $data['payment_methods'] = $this->db->get('payments')->result();
        
        // Debug: Log payment methods count
        error_log('Payment methods loaded: ' . count($data['payment_methods']));
        
        $this->load->view('templates/header', $data);
        $this->load->view('kasir/transaksi', $data);
        $this->load->view('templates/footer');
    }

    public function get_transaksi_aktif() {
        $user_id = $this->session->userdata('user_id');
        $transaksi = $this->transaksi_model->get_transaksi_aktif($user_id);
        echo json_encode($transaksi);
    }

    public function activate_draft_action($transaction_id)
    {
        $this->output->set_content_type('application/json');
        $success = $this->transaksi_model->activate_draft($transaction_id);
        if ($success) {
            $this->output->set_output(json_encode(['status' => 'success']));
        } else {
            $this->output->set_output(json_encode(['status' => 'error', 'message' => 'Gagal mengaktifkan draft. Mungkin sudah diproses.']));
        }
    }

    public function get_drafts() {
        $user_id = $this->session->userdata('user_id');
        $drafts = $this->transaksi_model->get_all_draft($user_id);
        $result = array_map(function($draft) {
            return [
                'id' => $draft->id,
                'invoice_code' => $draft->invoice_code,
                'created_at' => $draft->created_at,
                'total_amount' => $draft->total_amount
            ];
        }, $drafts);
        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    public function create() {
        $product_id = $this->input->post('product_id');
        $qty = $this->input->post('qty');
        
        $product = $this->product_model->get_product($product_id);
        
        if ($product && $product->stock >= $qty) {
            $transaksi_id = $this->transaksi_model->add_to_cart($product_id, $qty, $product->price);
            echo json_encode(['status' => 'success', 'transaksi_id' => $transaksi_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Stok tidak mencukupi']);
        }
    }

    public function checkout() {
        $transaksi_id = $this->input->post('transaction_id');
        $payment_method = $this->input->post('metode');
        $paid_amount = $this->input->post('bayar');
        $total_amount = $this->input->post('total');
        $result = $this->transaksi_model->checkout($transaksi_id, $payment_method, $paid_amount, $total_amount);
        echo json_encode($result);
    }

    // Method untuk mengambil data produk realtime
    public function get_products_realtime() {
        $this->output->set_content_type('application/json');
        $products = $this->product_model->get_active_products();
        
        $result = array_map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'stock' => $product->stock,
                'photo' => $product->photo
            ];
        }, $products);
        
        $this->output->set_output(json_encode($result));
    }
    
    // Method untuk mengambil data payment methods
    public function get_payment_methods() {
        $this->output->set_content_type('application/json');
        $this->db->order_by('name', 'ASC');
        $payments = $this->db->get('payments')->result();
        $this->output->set_output(json_encode($payments));
    }
    

}
