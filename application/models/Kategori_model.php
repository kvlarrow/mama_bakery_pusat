<?php
class Kategori_model extends CI_Model {
    var $table = 'categories';
    var $column_order = ['name', null];
    var $column_search = ['name'];
    var $order = ['name' => 'asc'];

    private function _get_datatables_query() {
        $this->db->from($this->table);
        $i = 0;
        foreach ($this->column_search as $item) {
            if ($search = $this->input->post('search')['value'] ?? '') {
                if ($i === 0) {
                    $this->db->like($item, $search);
                } else {
                    $this->db->or_like($item, $search);
                }
            }
            $i++;
        }
        if ($order = $this->input->post('order')[0]['column'] ?? false) {
            $this->db->order_by($this->column_order[$order], $this->input->post('order')[0]['dir']);
        } else {
            $this->db->order_by(key($this->order), $this->order[key($this->order)]);
        }
    }
    public function get_datatables() {
        $this->_get_datatables_query();
        if (($length = $this->input->post('length')) != -1) {
            $this->db->limit($length, $this->input->post('start'));
        }
        return $this->db->get()->result();
    }
    public function count_filtered() {
        $this->_get_datatables_query();
        return $this->db->count_all_results();
    }
    public function count_all() {
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }
    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }
    public function get($id) {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
} 