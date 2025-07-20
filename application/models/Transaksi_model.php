<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaksi_model extends CI_Model {
    public function get_transaksi_aktif($user_id) {
        $this->db->select('t.*, u.name as kasir_name, p.name as product_name, ti.quantity, ti.unit_price, ti.total_price');
        $this->db->from('transactions t');
        $this->db->join('users u', 'u.id = t.user_id');
        $this->db->join('transaction_items ti', 'ti.transaction_id = t.id', 'left');
        $this->db->join('products p', 'p.id = ti.product_id', 'left');
        $this->db->where('t.user_id', $user_id);
        $this->db->where('t.status', 'draft');
        $this->db->order_by('t.created_at', 'DESC');
        $this->db->limit(1);
        
        $query = $this->db->get();
        return $query->row();
    }

    public function add_to_cart($product_id, $qty, $price) {
        $user_id = $this->session->userdata('user_id');
        $total_price = $qty * $price;
        
        // Cek stok produk
        $this->load->model('product_model');
        $product = $this->product_model->get_product($product_id);
        if (!$product || $product->stock < $qty) {
            return ['status' => 'error', 'message' => 'Stok tidak mencukupi'];
        }
        // Cek qty di keranjang (draft)
        $transaksi = $this->db->where('user_id', $user_id)
                             ->where('status', 'draft')
                             ->get('transactions')
                             ->row();
        if ($transaksi) {
            $item = $this->db->where('transaction_id', $transaksi->id)
                             ->where('product_id', $product_id)
                             ->get('transaction_items')
                             ->row();
            $qty_in_cart = $item ? $item->quantity : 0;
            if ($qty_in_cart + $qty > $product->stock) {
                return ['status' => 'error', 'message' => 'Qty di keranjang melebihi stok produk'];
            }
        }
        
        // Cek apakah ada transaksi draft
        $transaksi = $this->db->where('user_id', $user_id)
                             ->where('status', 'draft')
                             ->get('transactions')
                             ->row();
        
        if (!$transaksi) {
            // Buat transaksi baru
            $invoice_code = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            $data = [
                'invoice_code' => $invoice_code,
                'user_id' => $user_id,
                'status' => 'draft',
                'total_amount' => $total_price,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('transactions', $data);
            $transaksi_id = $this->db->insert_id();
        } else {
            $transaksi_id = $transaksi->id;
            // Update total amount
            $this->db->set('total_amount', 'total_amount + ' . $total_price, FALSE);
            $this->db->where('id', $transaksi_id);
            $this->db->update('transactions');
        }
        
        // Tambahkan atau update item di transaksi
        $item = $this->db->where('transaction_id', $transaksi_id)
                         ->where('product_id', $product_id)
                         ->get('transaction_items')
                         ->row();
        if ($item) {
            // Update qty dan subtotal
            $new_qty = $item->quantity + $qty;
            $new_total = $new_qty * $price;
            $this->db->where('id', $item->id)
                     ->update('transaction_items', [
                        'quantity' => $new_qty,
                        'total_price' => $new_total
                     ]);
        } else {
            // Insert baru
            $this->db->insert('transaction_items', [
                'transaction_id' => $transaksi_id,
                'product_id' => $product_id,
                'quantity' => $qty,
                'unit_price' => $price,
                'total_price' => $total_price
            ]);
        }
        
        // Update stok produk
        $this->load->model('product_model');
        $this->product_model->update_stock($product_id, $qty);
        
        return $transaksi_id;
    }

    public function checkout($transaksi_id, $payment_method, $paid_amount, $total_amount) {
        $this->db->trans_start();
        // Update status transaksi
        $data = [
            'status' => 'completed',
            'payment_id' => $payment_method,
            'paid_amount' => $paid_amount,
            'change_amount' => $paid_amount - $total_amount,
            'completed_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('id', $transaksi_id);
        $this->db->update('transactions', $data);
        // Kurangi stok produk untuk setiap item (pakai query SQL langsung)
        $items = $this->get_transaction_items($transaksi_id);
        $debug = [];
        foreach ($items as $item) {
            $this->db->query("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?", [
                $item->quantity, $item->product_id, $item->quantity
            ]);
            $debug[] = 'Update stok: product_id=' . $item->product_id . ', qty=' . $item->quantity . ', affected_rows=' . $this->db->affected_rows();
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return ['status' => 'error', 'message' => 'Checkout gagal', 'debug' => $debug, 'transaction_id' => $transaksi_id];
        }
        return ['status' => 'success', 'message' => 'Transaksi berhasil', 'debug' => $debug, 'transaction_id' => $transaksi_id];
    }

    public function simpan_draft($user_id, $cart, $total_amount) {
        // 1. Insert ke transactions
        $data = [
            'invoice_code' => $this->generate_invoice_code(),
            'user_id' => $user_id,
            'total_amount' => $total_amount,
            'status' => 'draft',
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('transactions', $data);
        $transaction_id = $this->db->insert_id();

        // 2. Insert ke transaction_items
        foreach ($cart as $item) {
            $this->db->insert('transaction_items', [
                'transaction_id' => $transaction_id,
                'product_id' => $item['id'],
                'quantity' => $item['qty'],
                'unit_price' => $item['price'],
                'total_price' => $item['subtotal']
            ]);
        }
        return $transaction_id;
    }

    public function generate_invoice_code() {
        // Contoh: INV20240601-0001
        $date = date('Ymd');
        $last = $this->db->select('invoice_code')
            ->like('invoice_code', "INV{$date}-", 'after')
            ->order_by('invoice_code', 'DESC')
            ->limit(1)
            ->get('transactions')
            ->row();
        $num = 1;
        if ($last) {
            $last_num = (int)substr($last->invoice_code, -4);
            $num = $last_num + 1;
        }
        return "INV{$date}-" . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function get_all_draft($user_id) {
        $this->db->where('status', 'draft');
        $this->db->where('user_id', $user_id);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('transactions')->result();
    }

    public function get_draft_items($transaction_id) {
        $this->db->select('ti.*, p.name as product_name');
        $this->db->from('transaction_items ti');
        $this->db->join('products p', 'p.id = ti.product_id');
        $this->db->where('ti.transaction_id', $transaction_id);
        return $this->db->get()->result();
    }

    public function activate_draft($transaction_id)
    {
        $this->db->where('id', $transaction_id);
        $this->db->where('status', 'draft'); // Pastikan hanya mengaktifkan draft
        $this->db->update('transactions', ['status' => 'pending']);
        return $this->db->affected_rows() > 0;
    }

    public function get_riwayat($kasir_id = null, $tanggal = null)
    {
        $this->db->select('*');
        $this->db->from('transactions');
        if ($kasir_id) {
            $this->db->where('user_id', $kasir_id);
        }
        if ($tanggal) {
            $this->db->where('DATE(created_at)', $tanggal);
        }
        $this->db->where('status', 'completed');
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get()->result();
    }

    // === DataTables methods for Riwayat Transaksi ===
    var $column_order_riwayat = [null, 'invoice_code', 'total_amount', 'payment_id', null]; // Sesuaikan dengan kolom di tabel view
    var $column_search_riwayat = ['transactions.invoice_code', 'users.name', 'payments.name']; // Kolom yang bisa dicari
    var $order_riwayat = ['transactions.created_at' => 'desc'];

    private function _get_datatables_query_riwayat($kasir_id = null, $tanggal = null) {
        $this->db->select('transactions.id, transactions.created_at, transactions.invoice_code, transactions.total_amount, payments.name as payment_method_name, users.name as kasir_name');
        $this->db->from('transactions');
        $this->db->join('payments', 'payments.id = transactions.payment_id', 'left');
        $this->db->join('users', 'users.id = transactions.user_id', 'left');
        $this->db->where('transactions.status', 'completed');

        if ($kasir_id) {
            $this->db->where('transactions.user_id', $kasir_id);
        }
        if ($tanggal) {
            $this->db->where('DATE(transactions.created_at)', $tanggal);
        }

        $i = 0;
        foreach ($this->column_search_riwayat as $item) {
            if ($search = $this->input->post('search')['value'] ?? '') {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $search);
                } else {
                    $this->db->or_like($item, $search);
                }
                if (count($this->column_search_riwayat) - 1 == $i) {
                    $this->db->group_end();
                }
            }
            $i++;
        }

        if ($this->input->post('order')) {
            $this->db->order_by($this->column_order_riwayat[$this->input->post('order')[0]['column']], $this->input->post('order')[0]['dir']);
        } else if (isset($this->order_riwayat)) {
            $order = $this->order_riwayat;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    public function get_datatables_riwayat($kasir_id = null, $tanggal = null) {
        $this->_get_datatables_query_riwayat($kasir_id, $tanggal);
        if (($length = $this->input->post('length')) != -1) {
            $this->db->limit($length, $this->input->post('start'));
        }
        return $this->db->get()->result();
    }

    public function count_filtered_riwayat($kasir_id = null, $tanggal = null) {
        $this->_get_datatables_query_riwayat($kasir_id, $tanggal);
        return $this->db->count_all_results();
    }

    public function count_all_riwayat($kasir_id = null, $tanggal = null) {
        $this->db->from('transactions');
        $this->db->where('status', 'completed');
        if ($kasir_id) {
            $this->db->where('user_id', $kasir_id);
        }
        if ($tanggal) {
            $this->db->where('DATE(created_at)', $tanggal);
        }
        return $this->db->count_all_results();
    }

    public function get_transaction_with_items($transaction_id) {
        // Get main transaction details
        $this->db->select('t.*, u.name as kasir_name, p.name as payment_method_name');
        $this->db->from('transactions t');
        $this->db->join('users u', 'u.id = t.user_id', 'left');
        $this->db->join('payments p', 'p.id = t.payment_id', 'left');
        $this->db->where('t.id', $transaction_id);
        $this->db->where('t.status', 'completed');
        $transaction = $this->db->get()->row();

        if ($transaction) {
            // Get transaction items
            $this->db->select('ti.*, prod.name as product_name');
            $this->db->from('transaction_items ti');
            $this->db->join('products prod', 'prod.id = ti.product_id', 'left');
            $this->db->where('ti.transaction_id', $transaction_id);
            $items = $this->db->get()->result();
            $transaction->items = $items;
        }

        return $transaction;
    }

    public function get_transaction($transaction_id) {
        return $this->db
            ->select('t.*, u.name as kasir, p.name as payment_method')
            ->from('transactions t')
            ->join('users u', 'u.id = t.user_id', 'left')
            ->join('payments p', 'p.id = t.payment_id', 'left')
            ->where('t.id', $transaction_id)
            ->get()
            ->row();
    }
    public function get_transaction_items($transaction_id) {
        return $this->db
            ->select('ti.*, p.name as product_name, (ti.unit_price * ti.quantity) as subtotal')
            ->from('transaction_items ti')
            ->join('products p', 'p.id = ti.product_id', 'left')
            ->where('ti.transaction_id', $transaction_id)
            ->get()
            ->result();
    }

    // Ambil pendapatan per hari selama N hari terakhir
    public function get_income_stats($days = 7) {
        $this->db->select(["DATE(created_at) as tanggal", "SUM(total_amount) as total"]);
        $this->db->from('transactions');
        $this->db->where('status', 'completed');
        $this->db->where('created_at >=', date('Y-m-d 00:00:00', strtotime('-'.($days-1).' days')));
        $this->db->group_by('DATE(created_at)');
        $this->db->order_by('tanggal', 'ASC');
        return $this->db->get()->result();
    }

    // Ambil pendapatan hari ini
    public function get_today_income() {
        $this->db->select('SUM(total_amount) as total');
        $this->db->from('transactions');
        $this->db->where('status', 'completed');
        $this->db->where('DATE(created_at)', date('Y-m-d'));
        return $this->db->get()->row()->total ?? 0;
    }

    // Hitung jumlah transaksi completed hari ini
    public function get_today_transaction_count() {
        $this->db->from('transactions');
        $this->db->where('status', 'completed');
        $this->db->where('DATE(created_at)', date('Y-m-d'));
        return $this->db->count_all_results();
    }
}
