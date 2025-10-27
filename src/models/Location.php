<?php
class Location {
    private $conn;
    private $table_name = "locations";

    public $id;
    public $name;
    public $description;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                 (name, description, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, datetime('now'), datetime('now'))";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));
        $this->is_active = $this->is_active ?? 1;

        if($stmt->execute([$this->name, $this->description, $this->is_active])) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function readActive() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE is_active = 1 ORDER BY name ASC";
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
            $this->description = $row['description'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                 SET name = ?, description = ?, is_active = ?, updated_at = datetime('now')
                 WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));

        if($stmt->execute([$this->name, $this->description, $this->is_active, $this->id])) {
            return true;
        }
        return false;
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
    
    public function toggleActive() {
        $query = "UPDATE " . $this->table_name . "
                 SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END,
                     updated_at = datetime('now')
                 WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function nameExists($exclude_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE name = ?";
        if ($exclude_id) {
            $query .= " AND id != ?";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->name);
        
        if ($exclude_id) {
            $stmt->bindParam(2, $exclude_id);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    // Get count of foods using this location
    public function getFoodCount() {
        $query = "SELECT COUNT(*) as count FROM foods WHERE location = (SELECT name FROM " . $this->table_name . " WHERE id = ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
    
    // Get count of ingredient locations using this location
    public function getIngredientLocationCount() {
        $query = "SELECT COUNT(*) as count FROM ingredient_locations 
                 WHERE location = (SELECT name FROM " . $this->table_name . " WHERE id = ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
    
    // Migrate all items from this location to another
    public function migrateToLocation($new_location_name) {
        try {
            $this->conn->beginTransaction();
            
            // Get current location name
            $query = "SELECT name FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $old_location_name = $row['name'];
            
            // Update foods
            $query = "UPDATE foods SET location = ? WHERE location = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$new_location_name, $old_location_name]);
            
            // Update ingredient_locations
            $query = "UPDATE ingredient_locations SET location = ? WHERE location = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$new_location_name, $old_location_name]);
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    
    // Static helper to get location options for dropdowns
    public static function getLocationOptions($db, $active_only = true) {
        $location = new Location($db);
        $stmt = $active_only ? $location->readActive() : $location->read();
        
        $options = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $options[] = $row;
        }
        return $options;
    }
}
?>
