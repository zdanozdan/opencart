<?php
class ModelAccountTransaction extends Model {
	public function getTransactionsFrom($table, $data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . $table."` WHERE customer_id = '" . (int)$this->customer->getId() . "'";

		$sort_data = array(
			'amount',
			'description',
			'date_added'
		);

        if (isset($data['order_id'])) {
            $sql .= " AND order_id = ".$data['order_id'];
        }

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY date_added";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

    public function getTransactions($data = array()) {
        return $this->getTransactionsFrom('customer_transaction',$data);
    }

	public function getTotalTransactions() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "customer_transaction` WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		return $query->row['total'];
	}

	public function getTotalAmount() {
		$query = $this->db->query("SELECT SUM(amount) AS total FROM `" . DB_PREFIX . "customer_transaction` WHERE customer_id = '" . (int)$this->customer->getId() . "' GROUP BY customer_id");

		if ($query->num_rows) {
			return $query->row['total'];
		} else {
			return 0;
		}
	}

    //Payments
    public function getPaymentsHistory($data = array()) {
        return $this->getTransactionsFrom('customer_payments',$data);
    }

    public function getTotalPayments($data) {
		$sql = "SELECT SUM(amount) AS total FROM `" . DB_PREFIX . "customer_payments` WHERE customer_id = '" . (int)$this->customer->getId() . "'";
        if (isset($data['order_id'])) {
            $sql .= " AND order_id = '".$data['order_id']."'";
        }
        $query = $this->db->query($sql);
		return $query->row['total'];
	}

    public function getOrderPayments($order_id) {
        $sql = "SELECT SUM(amount) as total FROM `" . DB_PREFIX . "customer_payments` WHERE order_id = '" . (int)$order_id . "'";
        $query = $this->db->query($sql);
		return $query->row['total'];
    }
    
    public function addPaymentHistory($customer_id, $data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_payments` SET customer_id = '" . (int)$customer_id . "', order_id='".$data['order_id']."', description='".$data['description']."', amount='".$data['amount']."', date_added = NOW()");
    }
}