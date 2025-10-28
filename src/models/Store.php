<?php
class Store {
    private $conn;
    private $table_name = "stores";

    // Store properties
    public $id;
    public $name;
    public $address;
    public $phone;
    public $website;
    public $notes;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create store
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, address, phone, website, notes, is_active) 
                  VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->address = htmlspecialchars(strip_tags($this->address ?? ''));
        $this->phone = htmlspecialchars(strip_tags($this->phone ?? ''));
        $this->website = htmlspecialchars(strip_tags($this->website ?? ''));
        $this->notes = htmlspecialchars(strip_tags($this->notes ?? ''));
        $this->is_active = $this->is_active ?? 1;

        $stmt->execute([
            $this->name,
            $this->address,
            $this->phone,
            $this->website,
            $this->notes,
            $this->is_active
        ]);

        if ($stmt->rowCount()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Read all stores
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read active stores only
    public function readActive() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE is_active = 1 
                  ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read one store
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->name = $row['name'];
            $this->address = $row['address'];
            $this->phone = $row['phone'];
            $this->website = $row['website'];
            $this->notes = $row['notes'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Update store
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = ?, address = ?, phone = ?, website = ?, 
                      notes = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->address = htmlspecialchars(strip_tags($this->address ?? ''));
        $this->phone = htmlspecialchars(strip_tags($this->phone ?? ''));
        $this->website = htmlspecialchars(strip_tags($this->website ?? ''));
        $this->notes = htmlspecialchars(strip_tags($this->notes ?? ''));
        $this->is_active = $this->is_active ?? 1;

        $stmt->execute([
            $this->name,
            $this->address,
            $this->phone,
            $this->website,
            $this->notes,
            $this->is_active,
            $this->id
        ]);

        return $stmt->rowCount() > 0;
    }

    // Delete store (soft delete by setting is_active to 0)
    public function delete() {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_active = 0, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        return $stmt->rowCount() > 0;
    }

    // Hard delete store (permanent removal)
    public function hardDelete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        return $stmt->rowCount() > 0;
    }

    // Search stores
    public function search($keywords) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE (name LIKE ? OR address LIKE ? OR notes LIKE ?) 
                  AND is_active = 1
                  ORDER BY name ASC";
        
        $keywords = "%{$keywords}%";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$keywords, $keywords, $keywords]);
        return $stmt;
    }

    // Count total stores
    public function countAll() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // Count active stores
    public function countActive() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // Get store names for dropdown
    public static function getStoreOptions($db) {
        $query = "SELECT id, name FROM stores WHERE is_active = 1 ORDER BY name ASC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check if store name exists (for validation)
    public function nameExists($exclude_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE name = ?";
        $params = [$this->name];
        
        if ($exclude_id) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    // Toggle active status
    public function toggleActive() {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END,
                      updated_at = CURRENT_TIMESTAMP 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        return $stmt->rowCount() > 0;
    }
}
?>