<?php
class Food {
    private $conn;
    private $table_name = "foods";

    public $id;
    public $name;
    public $category;
    public $quantity;
    public $unit;
    public $expiry_date;
    public $purchase_date;
    public $purchase_location;
    public $location;
    public $notes;
    public $user_id;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                 (name, category, quantity, unit, expiry_date, purchase_date, 
                  purchase_location, location, notes, user_id, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->category = htmlspecialchars(strip_tags($this->category ?? ''));
        $this->quantity = $this->quantity ?? 0;
        $this->unit = htmlspecialchars(strip_tags($this->unit ?? 'pieces'));
        $this->expiry_date = $this->expiry_date ?: null;
        $this->purchase_date = $this->purchase_date ?: null;
        $this->purchase_location = htmlspecialchars(strip_tags($this->purchase_location ?? ''));
        $this->location = htmlspecialchars(strip_tags($this->location ?? ''));
        $this->notes = htmlspecialchars(strip_tags($this->notes ?? ''));
        $this->user_id = $this->user_id ?? null;

        $stmt->execute([
            $this->name,
            $this->category,
            $this->quantity,
            $this->unit,
            $this->expiry_date,
            $this->purchase_date,
            $this->purchase_location,
            $this->location,
            $this->notes,
            $this->user_id
        ]);

        if($stmt->rowCount()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->name = $row['name'];
            $this->category = $row['category'];
            $this->quantity = $row['quantity'];
            $this->unit = $row['unit'];
            $this->expiry_date = $row['expiry_date'];
            $this->purchase_date = $row['purchase_date'];
            $this->purchase_location = $row['purchase_location'];
            $this->location = $row['location'];
            $this->notes = $row['notes'];
            $this->user_id = $row['user_id'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                 SET name = ?, category = ?, quantity = ?, unit = ?, 
                     expiry_date = ?, purchase_date = ?, purchase_location = ?,
                     location = ?, notes = ?, user_id = ?, updated_at = CURRENT_TIMESTAMP
                 WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->category = htmlspecialchars(strip_tags($this->category ?? ''));
        $this->quantity = $this->quantity ?? 0;
        $this->unit = htmlspecialchars(strip_tags($this->unit ?? 'pieces'));
        $this->expiry_date = $this->expiry_date ?: null;
        $this->purchase_date = $this->purchase_date ?: null;
        $this->purchase_location = htmlspecialchars(strip_tags($this->purchase_location ?? ''));
        $this->location = htmlspecialchars(strip_tags($this->location ?? ''));
        $this->notes = htmlspecialchars(strip_tags($this->notes ?? ''));
        $this->user_id = $this->user_id ?? null;

        $stmt->execute([
            $this->name,
            $this->category,
            $this->quantity,
            $this->unit,
            $this->expiry_date,
            $this->purchase_date,
            $this->purchase_location,
            $this->location,
            $this->notes,
            $this->user_id,
            $this->id
        ]);

        return $stmt->rowCount() > 0;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getExpiringItems($days = 7) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE expiry_date <= date('now', '+' || ? || ' days')
                 AND expiry_date >= date('now')
                 ORDER BY expiry_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$days]);
        return $stmt;
    }

    // Search foods
    public function search($keywords) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE name LIKE ? OR category LIKE ? OR notes LIKE ? OR purchase_location LIKE ?
                 ORDER BY created_at DESC";
        
        $keywords = "%{$keywords}%";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$keywords, $keywords, $keywords, $keywords]);
        return $stmt;
    }
}
?>