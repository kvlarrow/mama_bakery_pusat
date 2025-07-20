<?php
class Laporan_model extends CI_Model {
    var $table = 'transactions';
    var $column_order = ['created_at', 'invoice_code', 'kasir', 'jenis_pembayaran', 'total_amount'];
    var $column_search = ['transactions.invoice_code', 'users.name', 'payments.name'];
    var $order = ['transactions.created_at' => 'desc'];

    private function _get_datatables_query($bulan, $tahun) {
        $this->db->select('transactions.created_at, transactions.invoice_code, users.name as kasir, payments.name as jenis_pembayaran, transactions.total_amount');
        $this->db->from('transactions');
        $this->db->join('users', 'users.id = transactions.user_id', 'left');
        $this->db->join('payments', 'payments.id = transactions.payment_id', 'left');
        $this->db->where('MONTH(transactions.created_at)', $bulan);
        $this->db->where('YEAR(transactions.created_at)', $tahun);
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
    public function get_datatables($bulan, $tahun) {
        $this->_get_datatables_query($bulan, $tahun);
        if (($length = $this->input->post('length')) != -1) {
            $this->db->limit($length, $this->input->post('start'));
        }
        return $this->db->get()->result();
    }
    public function count_filtered($bulan, $tahun) {
        $this->_get_datatables_query($bulan, $tahun);
        return $this->db->count_all_results();
    }
    public function count_all($bulan, $tahun) {
        $this->db->from('transactions');
        $this->db->where('MONTH(transactions.created_at)', $bulan);
        $this->db->where('YEAR(transactions.created_at)', $tahun);
        return $this->db->count_all_results();
    }
} 