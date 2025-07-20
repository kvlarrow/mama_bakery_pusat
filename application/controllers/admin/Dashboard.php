<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {
    public function __construct() {
        parent::__construct();
        
        // Check if user is logged in
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        
        // Check if user is admin
        if ($this->session->userdata('role') !== 'admin') {
            $this->session->set_flashdata('error', 'You do not have permission to access this page.');
            redirect('auth');
        }
    }

    public function index() {
        // Load models
        $this->load->model('product_model');
        $this->load->model('transaksi_model');
        
        // Get data
        $data = [
            'title' => 'Admin Dashboard',
            'user' => [
                'name' => $this->session->userdata('name'),
                'role' => $this->session->userdata('role')
            ],
            'total_products' => $this->product_model->count_products(),
            'top_products' => $this->product_model->get_top_selling_products(5),
            'today_income' => $this->transaksi_model->get_today_income(),
            'income_stats' => $this->transaksi_model->get_income_stats(7),
            'transaction_count_today' => $this->transaksi_model->get_today_transaction_count(),
        ];
        
        // Load views
        $this->load->view('templates/header', $data);
        $this->load->view('admin/dashboard', $data);
        $this->load->view('templates/footer');
    }
}
