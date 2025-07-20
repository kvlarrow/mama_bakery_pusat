<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Get all payment methods
     * 
     * @return array Array of payment methods
     */
    public function get_all_payments() {
        $this->db->order_by('name', 'ASC');
        return $this->db->get('payments')->result();
    }
    
    /**
     * Get payment method by ID
     * 
     * @param int $id Payment method ID
     * @return object Payment method object or null
     */
    public function get_payment($id) {
        $this->db->where('id', $id);
        return $this->db->get('payments')->row();
    }
    
    /**
     * Get payment method name by ID
     * 
     * @param int $id Payment method ID
     * @return string Payment method name or empty string
     */
    public function get_payment_name($id) {
        $payment = $this->get_payment($id);
        return $payment ? $payment->name : '';
    }
} 