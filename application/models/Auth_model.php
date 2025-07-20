<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    public function get_user_by_username($username) {
        $this->db->select('users.id, users.name, users.username, users.password, users.role_id, users.is_active, users.created_at, roles.name as role_name');
        $this->db->from('users');
        $this->db->join('roles', 'roles.id = users.role_id', 'left');
        $this->db->where('users.username', $username);
        $this->db->where('users.is_active', 1);
        $query = $this->db->get();
        
        return $query->row();
    }
}
