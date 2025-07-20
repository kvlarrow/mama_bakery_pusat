<?php
class Kategori extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('Kategori_model');
    }
    public function index() {
        $this->load->view('admin/kategori');
    }
    public function data() {
        header('Content-Type: application/json');
        $list = $this->Kategori_model->get_datatables();
        $data = [];
        foreach ($list as $k) {
            $row = [];
            $row['name'] = htmlspecialchars($k->name);
            $row['aksi'] = '<a href="#" class="btn btn-sm btn-warning btn-edit-kategori" data-id="'.$k->id.'"><i class="bi bi-pencil"></i></a> <a href="#" class="btn btn-sm btn-danger btn-hapus-kategori" data-id="'.$k->id.'"><i class="bi bi-trash"></i></a>';
            $data[] = $row;
        }
        $draw = intval($this->input->post('draw'));
        if ($draw < 1) $draw = 1;
        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $this->Kategori_model->count_all(),
            "recordsFiltered" => $this->Kategori_model->count_filtered(),
            "data" => $data
        ]);
    }
    public function tambah() {
        $name = $this->input->post('name');
        $this->Kategori_model->insert(['name' => $name]);
        echo json_encode(['status' => 'success']);
    }
    public function get_kategori($id) {
        $kategori = $this->Kategori_model->get($id);
        if ($kategori) {
            echo json_encode($kategori);
        } else {
            echo json_encode(['error' => 'Kategori tidak ditemukan']);
        }
    }
    public function update() {
        $id = $this->input->post('id');
        $name = $this->input->post('name');
        $this->Kategori_model->update($id, ['name' => $name]);
        echo json_encode(['status' => 'success']);
    }
    public function hapus($id = null) {
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID kategori tidak ditemukan.']);
            return;
        }
        // Cek relasi ke produk
        $used = $this->db->where('category_id', $id)->count_all_results('products');
        if ($used > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Kategori tidak bisa dihapus karena masih dipakai produk.']);
            return;
        }
        $this->Kategori_model->delete($id);
        echo json_encode(['status' => 'success']);
    }
} 