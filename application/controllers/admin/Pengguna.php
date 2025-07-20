<?php
class Pengguna extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('Pengguna_model');
        $this->load->library('form_validation');
        $this->load->library('encryption');
    }
    public function index() {
        $this->load->view('admin/pengguna');
    }
    public function get_ajax_pengguna() {
        header('Content-Type: application/json');
        $list = $this->Pengguna_model->get_datatables();
        $data = [];
        foreach ($list as $u) {
            $row = [];
            $row['name'] = htmlspecialchars($u->name);
            $row['username'] = htmlspecialchars($u->username);
            $row['role'] = $u->role_id == 1 ? 'Admin' : 'Kasir';
            $row['status'] = $u->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>';
            $row['aksi'] = '<a href="#" class="btn btn-sm btn-warning btn-edit" data-id="'.$u->id.'" data-name="'.htmlspecialchars($u->name).'"><i class="bi bi-pencil"></i></a> <a href="#" class="btn btn-sm btn-danger btn-delete" data-id="'.$u->id.'" data-name="'.htmlspecialchars($u->name).'"><i class="bi bi-trash"></i></a>';
            $data[] = $row;
        }
        $draw = intval($this->input->post('draw'));
        if ($draw < 1) $draw = 1;
        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $this->Pengguna_model->count_all(),
            "recordsFiltered" => $this->Pengguna_model->count_filtered(),
            "data" => $data
        ]);
    }
    public function store() {
        $this->form_validation->set_rules('name', 'Nama', 'required');
        $this->form_validation->set_rules('username', 'Username', 'required|is_unique[users.username]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('role_id', 'Role', 'required');
        $this->form_validation->set_rules('is_active', 'Status', 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }

        $data = [
            'name' => $this->input->post('name'),
            'username' => $this->input->post('username'),
            'password' => md5($this->input->post('password')),
            'role_id' => $this->input->post('role_id'),
            'is_active' => $this->input->post('is_active')
        ];
        $this->Pengguna_model->insert($data);
        echo json_encode(['status' => 'success', 'message' => 'Pengguna berhasil ditambahkan.']);
    }
    public function get_pengguna_by_id($id) {
        $u = $this->Pengguna_model->get($id);
        if ($u) {
            unset($u->password); // Never send password to frontend
            echo json_encode($u);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Pengguna tidak ditemukan']);
        }
    }
    public function update() {
        $id = $this->input->post('id');
        $this->form_validation->set_rules('name', 'Nama', 'required');
        $this->form_validation->set_rules('username', 'Username', 'required|callback_username_check['.$id.']');
        $this->form_validation->set_rules('role_id', 'Role', 'required');
        $this->form_validation->set_rules('is_active', 'Status', 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(['status' => 'error', 'message' => validation_errors()]);
            return;
        }

        $data = [
            'name' => $this->input->post('name'),
            'username' => $this->input->post('username'),
            'role_id' => $this->input->post('role_id'),
            'is_active' => $this->input->post('is_active')
        ];
        
        if (!empty($this->input->post('password'))) {
            $data['password'] = md5($this->input->post('password'));
        }

        $this->Pengguna_model->update($id, $data);
        echo json_encode(['status' => 'success', 'message' => 'Pengguna berhasil diperbarui.']);
    }
    public function destroy($id = null) {
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID pengguna tidak ditemukan.']);
            return;
        }
        $this->Pengguna_model->delete($id);
        echo json_encode(['status' => 'success', 'message' => 'Pengguna berhasil dihapus.']);
    }

    // Custom callback for username uniqueness on update
    public function username_check($username, $id) {
        $this->db->where('username', $username);
        $this->db->where('id !=', $id);
        $query = $this->db->get('users');
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message('username_check', 'Username ini sudah digunakan.');
            return FALSE;
        }
        return TRUE;
    }
} 