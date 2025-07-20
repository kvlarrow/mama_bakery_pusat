<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('auth_model');
    }

    public function index() {
        // If already logged in, redirect to dashboard
        if ($this->session->userdata('logged_in')) {
            $this->redirect_by_role();
        }
        // Load login view (TANPA header/footer)
        $data['title'] = 'Login';
        $this->load->view('auth/login', $data);
    }

    public function login() {
        // Check if user is already logged in
        if ($this->session->userdata('logged_in')) {
            $this->redirect_by_role();
        }

        // Set validation rules
        $this->form_validation->set_rules('username', 'Username', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        
        if ($this->form_validation->run() === FALSE) {
            // If this is an AJAX request
            if ($this->input->is_ajax_request()) {
                $response = [
                    'status' => 'error',
                    'message' => validation_errors()
                ];
                return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response));
            }
            // Regular form submission (TANPA header/footer)
            $data['title'] = 'Login';
            $this->load->view('auth/login', $data);
            return;
        }
        
        // Get input & convert to lowercase to prevent case-sensitivity issues
        $username = strtolower($this->input->post('username', TRUE));
        $password = $this->input->post('password', TRUE);
        
        // Get user from database
        $user = $this->auth_model->get_user_by_username($username);

        // Verify user exists and password matches
        if ($user && $user->password === md5($password)) {
            // Set user session
            $user_data = [
                'user_id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'role' => $user->role_name,
                'logged_in' => TRUE
            ];

            $this->session->set_userdata($user_data);
            
            // If this is an AJAX request
            if ($this->input->is_ajax_request()) {
                $response = [
                    'status' => 'success',
                    'message' => 'Login berhasil!',
                    'redirect' => site_url(strtolower($user->role_name) . '/dashboard')
                ];
                return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response));
            }
            
            // Regular form submission
            $this->session->set_flashdata('login_success', 'Login berhasil!');
            $this->redirect_by_role();
        } else {
            // Login failed
            $error_message = 'Username atau password salah';
            
            // If this is an AJAX request
            if ($this->input->is_ajax_request()) {
                $response = [
                    'status' => 'error',
                    'message' => $error_message
                ];
                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(401)
                    ->set_output(json_encode($response));
            }
            
            // Regular form submission
            $this->session->set_flashdata('error', $error_message);
            redirect('auth');
        }
    }
    
    private function redirect_by_role() {
        $role = strtolower($this->session->userdata('role'));
        
        if ($role === 'kasir') {
            redirect('kasir/transaksi');
        } else {
            redirect($role . '/dashboard');
        }
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('auth');
    }
}
