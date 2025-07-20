<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produk_model extends CI_Model {
    private $table = 'products';
    private $column_order = ['name','price','stock','category_name',null];
    private $column_search = ['products.name', 'categories.name'];
    private $order = ['products.name' => 'asc'];

    private function _get_datatables_query()
    {
        $this->db->select('products.*, categories.name as category_name');
        $this->db->from($this->table);
        $this->db->join('categories', 'categories.id = products.category_id', 'left');

        $search_value = $this->input->post('search')['value'] ?? '';
        if ($search_value) {
            $this->db->group_start(); // Mulai pengelompokan
            $i = 0;
            foreach ($this->column_search as $item) {
                if ($i === 0) {
                    $this->db->like($item, $search_value);
                } else {
                    $this->db->or_like($item, $search_value);
                }
                $i++;
            }
            $this->db->group_end(); // Akhiri pengelompokan
        }

        if ($this->input->post('order')) {
            $order_column_index = $this->input->post('order')['0']['column'];
            $order_dir = $this->input->post('order')['0']['dir'];
            $this->db->order_by($this->column_order[$order_column_index], $order_dir);
        } else if (isset($this->order)) {
            $this->db->order_by(key($this->order), $this->order[key($this->order)]);
        }
    }

    public function get_datatables()
    {
        $this->_get_datatables_query();
        if ($this->input->post('length') && $this->input->post('length') != -1) {
            $this->db->limit($this->input->post('length'), $this->input->post('start'));
        }
        return $this->db->get()->result();
    }
    public function count_filtered()
    {
        $this->_get_datatables_query();
        return $this->db->count_all_results();
    }
    public function count_all()
    {
        $this->db->from($this->table);
        $this->db->join('categories', 'categories.id = products.category_id', 'left');
        return $this->db->count_all_results();
    }

    public function get_all() {
        return $this->db->get('products')->result();
    }

    public function insert($data) {
        return $this->db->insert('products', $data);
    }

    public function get($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table)->row();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
} 