<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produk extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in') || strtolower($this->session->userdata('role')) !== 'admin') {
            redirect('auth');
        }
        $this->load->model('Produk_model');
    }

    public function index() {
        $data['title'] = 'Manajemen Produk';
        // $data['produk'] = $this->Produk_model->get_all(); // Removed, as data is loaded via DataTables AJAX
        $data['categories'] = $this->db->get('categories')->result();
        $this->load->view('templates/header', $data);
        $this->load->view('admin/produk', $data);
        $this->load->view('templates/footer');
    }

    public function data()
    {
        if (ENVIRONMENT !== 'production') {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
        }
        header('Content-Type: application/json');
        $this->load->model('Produk_model');
        $list = $this->Produk_model->get_datatables();
        $data = [];
        foreach ($list as $p) {
            $row = [];
            $row['name'] = htmlspecialchars($p->name);
            $row['price'] = 'Rp ' . number_format($p->price, 0, ',', '.');
            $row['stock'] = $p->stock ?? '-';
            $row['category'] = $p->category_name ?? '-';
            $row['photo'] = $p->photo ? '<img src="'.base_url('uploads/products/'. $p->photo).'" class="img-thumbnail" width="50">': 'Tidak ada';
            $row['aksi'] = '<button class="btn btn-sm btn-warning btn-edit-produk me-1" data-id="'.$p->id.'" data-bs-toggle="modal" data-bs-target="#modalEditProduk"><i class="bi bi-pencil"></i></button> <button class="btn btn-sm btn-danger btn-hapus-produk" data-id="'.$p->id.'" data-nama="'.htmlspecialchars($p->name).'" data-bs-toggle="modal" data-bs-target="#modalHapusProduk"><i class="bi bi-trash"></i></button>';
            $data[] = $row;
        }
        echo json_encode([
            "draw" => intval($this->input->post('draw')),
            "recordsTotal" => $this->Produk_model->count_all(),
            "recordsFiltered" => $this->Produk_model->count_filtered(),
            "data" => $data,
        ]);
    }

    public function tambah()
    {
        if ($this->input->method() === 'post') {
            $this->load->model('Produk_model');

            $data = [
                'name' => $this->input->post('name'),
                'category_id' => $this->input->post('category_id'),
                'price' => $this->input->post('price'),
                'stock' => $this->input->post('stock'),
                'is_active' => 1
            ];

            // Handle file upload
            $config['upload_path'] = './uploads/products/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['max_size'] = 500; // 500KB
            $config['encrypt_name'] = TRUE;

            $this->load->library('upload', $config);

            if (!empty($_FILES['photo']['name'])) {
                if ($this->upload->do_upload('photo')) {
                    $upload_data = $this->upload->data();
                    $data['photo'] = $upload_data['file_name'];
                } else {
                    echo json_encode(['status' => 'error', 'message' => $this->upload->display_errors()]);
                    return;
                }
            }
            
            $this->Produk_model->insert($data);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        }
    }

    public function get_produk($id)
    {
        $this->load->model('Produk_model');
        $produk = $this->Produk_model->get($id);
        if ($produk) {
            echo json_encode($produk);
        } else {
            echo json_encode(['error' => 'Produk tidak ditemukan']);
        }
    }

    public function update()
    {
        if ($this->input->method() === 'post') {
            $id = $this->input->post('id');
            $data = [
                'name' => $this->input->post('name'),
                'category_id' => $this->input->post('category_id'),
                'price' => $this->input->post('price'),
                'stock' => $this->input->post('stock')
            ];

            $this->load->model('Produk_model');

            // Handle file upload for update
            $config['upload_path'] = './uploads/products/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['max_size'] = 500; // 500KB
            $config['encrypt_name'] = TRUE;

            $this->load->library('upload', $config);

            if (!empty($_FILES['photo']['name'])) {
                if ($this->upload->do_upload('photo')) {
                    $upload_data = $this->upload->data();
                    $data['photo'] = $upload_data['file_name'];

                    // Optional: Delete old photo if it exists
                    $old_product = $this->Produk_model->get($id);
                    if ($old_product && $old_product->photo) {
                        $file_path = './uploads/products/' . $old_product->photo;
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }

                } else {
                    echo json_encode(['status' => 'error', 'message' => $this->upload->display_errors()]);
                    return;
                }
            }
            
            $this->Produk_model->update($id, $data);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        }
    }

    public function hapus($id = null)
    {
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID produk tidak ditemukan.']);
            return;
        }
        // Cek apakah produk dipakai di transaksi
        $used = $this->db->where('product_id', $id)->count_all_results('transaction_items');
        if ($used > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Produk tidak bisa dihapus karena sudah pernah dipakai di transaksi.']);
            return;
        }
        $this->load->model('Produk_model');
        // Get product photo before deleting to remove the file
        $product = $this->Produk_model->get($id);
        if ($product && $product->photo) {
            $file_path = './uploads/products/' . $product->photo;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $deleted = $this->Produk_model->delete($id);
        if ($deleted) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus produk']);
        }
    }
} 