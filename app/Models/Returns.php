<?php
class Returns {
    private $conn;
    private $table_name = "returns";
    private $salesColumnCache = [];

    public function __construct($db) {
        $this->conn = $db;
    }

    private function hasSalesColumn($column) {
        if (!array_key_exists($column, $this->salesColumnCache)) {
            try {
                $stmt = $this->conn->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales' AND COLUMN_NAME = :column");
                $stmt->bindValue(':column', $column);
                $stmt->execute();
                $this->salesColumnCache[$column] = ((int)$stmt->fetchColumn()) > 0;
            } catch (Exception $e) {
                $this->salesColumnCache[$column] = false;
            }
        }
        return $this->salesColumnCache[$column];
    }

    public function create($saleId, $medicineId, $quantity, $reason, $refundAmount, $originalPaymentMethod = null) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (sale_id, medicine_id, quantity, reason, refund_amount, original_payment_method, status)
                  VALUES (:sale_id, :medicine_id, :quantity, :reason, :refund_amount, :original_payment_method, 'pending')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':sale_id', $saleId, PDO::PARAM_INT);
        $stmt->bindParam(':medicine_id', $medicineId, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':refund_amount', $refundAmount);
        $stmt->bindValue(':original_payment_method', $originalPaymentMethod ?: 'Cash');
        return $stmt->execute();
    }

    public function approve($returnId, $processedBy, $refundMethod = null, $refundReference = null) {
        $this->conn->beginTransaction();
        try {
            // Lock the return row so concurrent approvals cannot both restock
            $lock = $this->conn->prepare("SELECT status FROM " . $this->table_name . " WHERE id = :id FOR UPDATE");
            $lock->bindValue(':id', (int)$returnId, PDO::PARAM_INT);
            $lock->execute();
            $lockedStatus = $lock->fetchColumn();

            // Get return details
            $ret = $this->readOne($returnId);
            if ($lockedStatus !== 'pending' || !$ret || $ret['status'] !== 'pending') {
                $this->conn->rollBack();
                return false;
            }

            // Update return status
            $query = "UPDATE " . $this->table_name . " 
                      SET status = 'approved', processed_by = :processed_by, processed_at = NOW(), refunded_at = NOW(), refund_method = :refund_method, refund_reference = :refund_reference
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':processed_by', $processedBy, PDO::PARAM_INT);
            $stmt->bindValue(':refund_method', $refundMethod ?: ($ret['original_payment_method'] ?? 'Cash'));
            $stmt->bindValue(':refund_reference', $refundReference ?: null);
            $stmt->bindParam(':id', $returnId, PDO::PARAM_INT);
            $stmt->execute();

            // Restock through the shared routine: new batch, inventory log, and
            // medicines.stock recalculated from batches
            $inventory = new Inventory($this->conn);
            $batchNum = 'RETURN-' . $returnId . '-' . date('Ymd');
            $reason = 'Return approved - Refund #' . $returnId . ': ' . $ret['reason'];
            $expiry = date('Y-m-d', strtotime('+6 months'));
            if (!$inventory->adjustStock((int)$ret['medicine_id'], (int)$ret['quantity'], 'in', $reason, $batchNum, $expiry)) {
                throw new Exception('Failed to restock returned medicine.');
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Return approval error: " . $e->getMessage());
            return false;
        }
    }

    public function reject($returnId, $processedBy) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'rejected', processed_by = :processed_by, processed_at = NOW() 
                  WHERE id = :id AND status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':processed_by', $processedBy, PDO::PARAM_INT);
        $stmt->bindParam(':id', $returnId, PDO::PARAM_INT);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function readOne($id) {
        $saleInvoiceSelect = $this->hasSalesColumn('invoice_number') ? 's.invoice_number' : 'NULL';
        $salePaymentSelect = $this->hasSalesColumn('payment_method') ? 's.payment_method' : "'Cash'";
        $query = "SELECT r.*, m.name as medicine_name, s.customer_name, s.sale_date, s.unit_price, s.id as sale_id, {$saleInvoiceSelect} as sale_invoice_number, {$salePaymentSelect} as sale_payment_method
                  FROM " . $this->table_name . " r
                  JOIN medicines m ON r.medicine_id = m.id
                  JOIN sales s ON r.sale_id = s.id
                  WHERE r.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readAll($status = null) {
        $where = '';
        $params = [];
        if ($status) {
            $where = ' WHERE r.status = :status';
            $params[':status'] = $status;
        }
          $salePaymentSelect = $this->hasSalesColumn('payment_method') ? 's.payment_method' : "'Cash'";
          $saleInvoiceSelect = $this->hasSalesColumn('invoice_number') ? 's.invoice_number' : 'NULL';
          $query = "SELECT r.*, m.name as medicine_name, s.customer_name, s.sale_date, s.id as sale_id,
              {$saleInvoiceSelect} as sale_invoice_number,
              {$salePaymentSelect} as sale_payment_method,
                  u.first_name as processor_first, u.last_name as processor_last
                  FROM " . $this->table_name . " r
                  JOIN medicines m ON r.medicine_id = m.id
                  JOIN sales s ON r.sale_id = s.id
                  LEFT JOIN users u ON r.processed_by = u.user_id"
                  . $where . " ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSaleDetails($saleId) {
        $saleInvoiceSelect = $this->hasSalesColumn('invoice_number') ? 's.invoice_number' : 'NULL';
        $query = "SELECT s.*, m.name as medicine_name, m.sale_price, m.id as medicine_id
                  , {$saleInvoiceSelect} as invoice_number
                  FROM sales s
                  JOIN medicines m ON s.medicine_id = m.id
                  WHERE s.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $saleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRecentSalesForReturn($limit = 200) {
        $saleInvoiceSelect = $this->hasSalesColumn('invoice_number') ? 's.invoice_number' : 'NULL';
        $query = "SELECT s.id as sale_id,
                         {$saleInvoiceSelect} as invoice_number,
                         s.sale_date,
                         s.customer_name,
                         s.quantity,
                         s.total_price,
                         s.unit_price,
                         m.name as medicine_name,
                         COALESCE(rq.returned_qty, 0) as returned_qty,
                         (s.quantity - COALESCE(rq.returned_qty, 0)) as returnable_qty
                  FROM sales s
                  JOIN medicines m ON m.id = s.medicine_id
                  LEFT JOIN (
                      SELECT sale_id, SUM(quantity) as returned_qty
                      FROM returns
                      WHERE status IN ('pending', 'approved')
                      GROUP BY sale_id
                  ) rq ON rq.sale_id = s.id
                  WHERE (s.quantity - COALESCE(rq.returned_qty, 0)) > 0
                  ORDER BY s.sale_date DESC
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReturnedQtyForSale($saleId) {
        $query = "SELECT COALESCE(SUM(quantity), 0) as total 
                  FROM " . $this->table_name . " 
                  WHERE sale_id = :id AND status IN ('pending', 'approved')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $saleId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }

    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    COALESCE(SUM(CASE WHEN status = 'approved' THEN refund_amount ELSE 0 END), 0) as total_refunded
                  FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
