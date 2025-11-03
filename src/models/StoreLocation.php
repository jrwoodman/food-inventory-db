<?php
class StoreLocation {
    private $conn;
    private $table_name = "store_locations";

    // StoreLocation properties
    public $id;
    public $chain_id;
    public $location_name;
    public $address;
    public $phone;
    public $hours;
    public $notes;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create store location
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (chain_id, location_name, address, phone, hours, notes, is_active) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->location_name = strip_tags($this->location_name ?? '');
        $this->address = strip_tags($this->address ?? '');
        $this->phone = strip_tags($this->phone ?? '');
        $this->hours = strip_tags($this->hours ?? '');
        $this->notes = strip_tags($this->notes ?? '');
        $this->is_active = $this->is_active ?? 1;

        $stmt->execute([
            $this->chain_id,
            $this->location_name,
            $this->address,
            $this->phone,
            $this->hours,
            $this->notes,
            $this->is_active
        ]);

        if ($stmt->rowCount()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Read all locations for a chain
    public function readByChain($chain_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE chain_id = ? 
                  ORDER BY location_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$chain_id]);
        return $stmt;
    }

    // Read one location
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->chain_id = $row['chain_id'];
            $this->location_name = $row['location_name'];
            $this->address = $row['address'];
            $this->phone = $row['phone'];
            $this->hours = $row['hours'];
            $this->notes = $row['notes'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Update location
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET chain_id = ?, location_name = ?, address = ?, phone = ?, 
                      hours = ?, notes = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->location_name = strip_tags($this->location_name ?? '');
        $this->address = strip_tags($this->address ?? '');
        $this->phone = strip_tags($this->phone ?? '');
        $this->hours = strip_tags($this->hours ?? '');
        $this->notes = strip_tags($this->notes ?? '');
        $this->is_active = $this->is_active ?? 1;

        $stmt->execute([
            $this->chain_id,
            $this->location_name,
            $this->address,
            $this->phone,
            $this->hours,
            $this->notes,
            $this->is_active,
            $this->id
        ]);

        return $stmt->rowCount() > 0;
    }

    // Delete location
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        return $stmt->rowCount() > 0;
    }

    // Check if location name exists for this chain (for validation)
    public function locationExists($chain_id, $exclude_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE chain_id = ? AND location_name = ?";
        $params = [$chain_id, $this->location_name];
        
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
    
    // Get location with chain name
    public function readOneWithChain() {
        $query = "SELECT sl.*, sc.name as chain_name
                  FROM " . $this->table_name . " sl
                  JOIN store_chains sc ON sl.chain_id = sc.id
                  WHERE sl.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->chain_id = $row['chain_id'];
            $this->location_name = $row['location_name'];
            $this->address = $row['address'];
            $this->phone = $row['phone'];
            $this->hours = $row['hours'];
            $this->notes = $row['notes'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return $row;
        }
        return false;
    }
}
?>
