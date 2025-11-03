<?php
class StoreChain {
    private $conn;
    private $table_name = "store_chains";

    // StoreChain properties
    public $id;
    public $name;
    public $website;
    public $notes;
    public $is_active;
    public $created_at;
    public $updated_at;
    
    // For managing locations
    public $locations = [];

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create store chain
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, website, notes, is_active) 
                  VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->name = strip_tags($this->name);
        $this->website = strip_tags($this->website ?? '');
        $this->notes = strip_tags($this->notes ?? '');
        $this->is_active = $this->is_active ?? 1;

        $stmt->execute([
            $this->name,
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

    // Read all store chains
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read active store chains only
    public function readActive() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE is_active = 1 
                  ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read one store chain
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->name = $row['name'];
            $this->website = $row['website'];
            $this->notes = $row['notes'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            // Load locations
            $this->loadLocations();
            
            return true;
        }
        return false;
    }

    // Update store chain
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = ?, website = ?, notes = ?, is_active = ?, 
                      updated_at = CURRENT_TIMESTAMP 
                  WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->name = strip_tags($this->name);
        $this->website = strip_tags($this->website ?? '');
        $this->notes = strip_tags($this->notes ?? '');
        $this->is_active = $this->is_active ?? 1;

        $stmt->execute([
            $this->name,
            $this->website,
            $this->notes,
            $this->is_active,
            $this->id
        ]);

        return $stmt->rowCount() > 0;
    }

    // Delete store chain
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        return $stmt->rowCount() > 0;
    }

    // Search store chains
    public function search($keywords) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE (name LIKE ? OR notes LIKE ?) 
                  AND is_active = 1
                  ORDER BY name ASC";
        
        $keywords = "%{$keywords}%";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$keywords, $keywords]);
        return $stmt;
    }

    // Load locations for this chain
    public function loadLocations() {
        $query = "SELECT * FROM store_locations 
                  WHERE chain_id = ? 
                  ORDER BY location_name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        
        $this->locations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->locations[] = $row;
        }
        
        return $this->locations;
    }

    // Get location count for this chain
    public function getLocationCount() {
        $query = "SELECT COUNT(*) as count FROM store_locations WHERE chain_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    // Check if chain name exists (for validation)
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

    // Get store chain names for dropdown
    public static function getChainOptions($db) {
        $query = "SELECT id, name FROM store_chains WHERE is_active = 1 ORDER BY name ASC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get with location count
    public function readWithLocationCount() {
        $query = "SELECT sc.*, COUNT(sl.id) as location_count
                  FROM " . $this->table_name . " sc
                  LEFT JOIN store_locations sl ON sc.id = sl.chain_id
                  GROUP BY sc.id
                  ORDER BY sc.name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
