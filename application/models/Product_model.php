<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    public function get_active_products() {
        $this->db->select('products.*, categories.name as category_name');
        $this->db->from('products');
        $this->db->join('categories', 'categories.id = products.category_id', 'left');
        $this->db->where('products.is_active', 1);
        $this->db->order_by('products.name', 'ASC');
        return $this->db->get()->result();
    }

    public function get_product($id) {
        $this->db->where('id', $id);
        $this->db->where('is_active', 1);
        return $this->db->get('products')->row();
    }

    public function update_stock($product_id, $qty) {
        $this->db->set('stock', 'stock-' . $qty, FALSE);
        $this->db->where('id', $product_id);
        return $this->db->update('products');
    }
    
    /**
     * Count total active products
     * 
     * @return int Number of active products
     */
    public function count_products() {
        $this->db->where('is_active', 1);
        return $this->db->count_all_results('products');
    }

    // === DataTables methods for Produk ===
    var $column_order_product = [null, 'products.name', 'products.price', 'products.stock', 'categories.name', null, null]; // Sesuaikan dengan kolom di tabel view
    var $column_search_product = ['products.name', 'categories.name']; // Kolom yang bisa dicari
    var $order_product = ['products.name' => 'asc'];

    private function _get_datatables_query_product() {
        $this->db->select('products.id, products.name, products.price, products.stock, products.photo, categories.name as category_name');
        $this->db->from('products');
        $this->db->join('categories', 'categories.id = products.category_id', 'left');
        $this->db->where('products.is_active', 1);

        $i = 0;
        foreach ($this->column_search_product as $item) {
            if ($search = $this->input->post('search')['value'] ?? '') {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $search);
                } else {
                    $this->db->or_like($item, $search);
                }
                if (count($this->column_search_product) - 1 == $i) {
                    $this->db->group_end();
                }
            }
            $i++;
        }

        if ($this->input->post('order')) {
            $this->db->order_by($this->column_order_product[$this->input->post('order')[0]['column']], $this->input->post('order')[0]['dir']);
        } else if (isset($this->order_product)) {
            $order = $this->order_product;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    public function get_datatables_product() {
        $this->_get_datatables_query_product();
        if (($length = $this->input->post('length')) != -1) {
            $this->db->limit($length, $this->input->post('start'));
        }
        return $this->db->get()->result();
    }

    public function count_filtered_product() {
        $this->_get_datatables_query_product();
        return $this->db->count_all_results();
    }

    public function count_all_product() {
        $this->db->from('products');
        $this->db->where('is_active', 1);
        return $this->db->count_all_results();
    }

    // Ambil produk dengan penjualan terbanyak
    public function get_top_selling_products($limit = 5) {
        $this->db->select('p.id, p.name, p.photo, SUM(ti.quantity) as total_sold');
        $this->db->from('products p');
        $this->db->join('transaction_items ti', 'ti.product_id = p.id', 'left');
        $this->db->join('transactions t', 't.id = ti.transaction_id', 'left');
        $this->db->where('t.status', 'completed');
        $this->db->group_by('p.id');
        $this->db->order_by('total_sold', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }
}
