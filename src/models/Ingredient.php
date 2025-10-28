<?php
class Ingredient {
    private $conn;
    private $table_name = "ingredients";
    private $locations_table = "ingredient_locations";

    public $id;
    public $name;
    public $category;
    public $unit;
    public $cost_per_unit;
    public $supplier;
    public $purchase_date;
    public $purchase_location;
    public $expiry_date;
    public $notes;
    public $user_id;
    public $group_id;
    public $created_at;
    public $updated_at;
    
    // For handling locations
    public $locations = []; // Array of location data

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        try {
            $this->conn->beginTransaction();
            
            // Insert into ingredients table (without quantity/location)
            $query = "INSERT INTO " . $this->table_name . "
                     (name, category, unit, cost_per_unit, supplier, purchase_date, 
                      purchase_location, expiry_date, notes, user_id, group_id, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";

            $stmt = $this->conn->prepare($query);

            // Clean data
            $this->name = htmlspecialchars(strip_tags($this->name));
            $this->category = htmlspecialchars(strip_tags($this->category ?? ''));
            $this->unit = htmlspecialchars(strip_tags($this->unit ?? 'oz'));
            $this->cost_per_unit = $this->cost_per_unit ?: null;
            $this->supplier = htmlspecialchars(strip_tags($this->supplier ?? ''));
            $this->purchase_date = $this->purchase_date ?: null;
            $this->purchase_location = htmlspecialchars(strip_tags($this->purchase_location ?? ''));
            $this->expiry_date = $this->expiry_date ?: null;
            $this->notes = htmlspecialchars(strip_tags($this->notes ?? ''));
            $this->user_id = $this->user_id ?? null;
            $this->group_id = $this->group_id ?? null;

            $stmt->execute([
                $this->name,
                $this->category,
                $this->unit,
                $this->cost_per_unit,
                $this->supplier,
                $this->purchase_date,
                $this->purchase_location,
                $this->expiry_date,
                $this->notes,
                $this->user_id,
                $this->group_id
            ]);

            if(!$stmt->rowCount()) {
                $this->conn->rollBack();
                return false;
            }
            
            $ingredient_id = $this->conn->lastInsertId();
            $this->id = $ingredient_id;
            
            // Insert location data if provided
            if (!empty($this->locations)) {
                foreach ($this->locations as $location_data) {
                    $this->addLocation($location_data['location'], $location_data['quantity'], $location_data['notes'] ?? '');
                }
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function read() {
        $query = "SELECT * FROM ingredient_totals ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function readByUser($user_id) {
        $query = "SELECT i.*, 
                  COALESCE(SUM(il.quantity), 0) as total_quantity
                  FROM " . $this->table_name . " i
                  LEFT JOIN " . $this->locations_table . " il ON i.id = il.ingredient_id
                  WHERE i.user_id = ?
                  GROUP BY i.id
                  ORDER BY i.name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt;
    }

    public function readByGroups($group_ids) {
        if (empty($group_ids)) {
            return false;
        }
        
        $placeholders = implode(',', array_fill(0, count($group_ids), '?'));
        $query = "SELECT i.*, 
                  COALESCE(SUM(il.quantity), 0) as total_quantity
                  FROM " . $this->table_name . " i
                  LEFT JOIN " . $this->locations_table . " il ON i.id = il.ingredient_id
                  WHERE i.group_id IN ($placeholders)
                  GROUP BY i.id
                  ORDER BY i.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($group_ids);
        return $stmt;
    }
    
    public function readWithLocations() {
        $query = "SELECT * FROM ingredient_location_details ORDER BY ingredient_name, location";
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
            $this->unit = $row['unit'];
            $this->cost_per_unit = $row['cost_per_unit'];
            $this->supplier = $row['supplier'];
            $this->purchase_date = $row['purchase_date'];
            $this->purchase_location = $row['purchase_location'];
            $this->expiry_date = $row['expiry_date'];
            $this->notes = $row['notes'];
            $this->user_id = $row['user_id'];
            $this->group_id = $row['group_id'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            // Load location data
            $this->loadLocations();
            
            return true;
        }
        return false;
    }

    public function update() {
        try {
            $this->conn->beginTransaction();
            
            // Update ingredients table (without quantity/location)
            $query = "UPDATE " . $this->table_name . "
                     SET name = ?, category = ?, unit = ?, cost_per_unit = ?, 
                         supplier = ?, purchase_date = ?, purchase_location = ?,
                         expiry_date = ?, notes = ?, user_id = ?, group_id = ?, updated_at = CURRENT_TIMESTAMP
                     WHERE id = ?";

            $stmt = $this->conn->prepare($query);

            // Clean data
            $this->name = htmlspecialchars(strip_tags($this->name));
            $this->category = htmlspecialchars(strip_tags($this->category ?? ''));
            $this->unit = htmlspecialchars(strip_tags($this->unit ?? 'oz'));
            $this->cost_per_unit = $this->cost_per_unit ?: null;
            $this->supplier = htmlspecialchars(strip_tags($this->supplier ?? ''));
            $this->purchase_date = $this->purchase_date ?: null;
            $this->purchase_location = htmlspecialchars(strip_tags($this->purchase_location ?? ''));
            $this->expiry_date = $this->expiry_date ?: null;
            $this->notes = htmlspecialchars(strip_tags($this->notes ?? ''));
            $this->user_id = $this->user_id ?? null;
            $this->group_id = $this->group_id ?? null;

            $stmt->execute([
                $this->name,
                $this->category,
                $this->unit,
                $this->cost_per_unit,
                $this->supplier,
                $this->purchase_date,
                $this->purchase_location,
                $this->expiry_date,
                $this->notes,
                $this->user_id,
                $this->group_id,
                $this->id
            ]);

            if(!$stmt->rowCount()) {
                $this->conn->rollBack();
                return false;
            }
            
            // Update location data if provided
            if (!empty($this->locations)) {
                // Clear existing locations
                $this->clearLocations();
                
                // Add new locations
                foreach ($this->locations as $location_data) {
                    $this->addLocation($location_data['location'], $location_data['quantity'], $location_data['notes'] ?? '');
                }
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
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

    public function getLowStockItems($threshold = 10) {
        $query = "SELECT * FROM low_stock_ingredients WHERE total_quantity <= :threshold ORDER BY total_quantity ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":threshold", $threshold);
        $stmt->execute();
        return $stmt;
    }
    
    public function getLowStockItemsByUser($user_id, $threshold = 10) {
        $query = "SELECT i.*, 
                  COALESCE(SUM(il.quantity), 0) as total_quantity
                  FROM " . $this->table_name . " i
                  LEFT JOIN " . $this->locations_table . " il ON i.id = il.ingredient_id
                  WHERE i.user_id = ?
                  GROUP BY i.id
                  HAVING total_quantity <= ?
                  ORDER BY total_quantity ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $threshold]);
        return $stmt;
    }

    public function getLowStockItemsByGroups($group_ids, $threshold = 10) {
        if (empty($group_ids)) {
            return false;
        }
        
        $placeholders = implode(',', array_fill(0, count($group_ids), '?'));
        $query = "SELECT i.*, 
                  COALESCE(SUM(il.quantity), 0) as total_quantity
                  FROM " . $this->table_name . " i
                  LEFT JOIN " . $this->locations_table . " il ON i.id = il.ingredient_id
                  WHERE i.group_id IN ($placeholders)
                  GROUP BY i.id
                  HAVING total_quantity <= ?
                  ORDER BY total_quantity ASC";
        
        $params = array_merge($group_ids, [$threshold]);
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
    
    // New methods for handling locations
    public function addLocation($location, $quantity, $notes = '') {
        $query = "INSERT OR REPLACE INTO " . $this->locations_table . "
                 (ingredient_id, location, quantity, notes, created_at, updated_at)
                 VALUES (:ingredient_id, :location, :quantity, :notes, datetime('now'), datetime('now'))";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ingredient_id", $this->id);
        $stmt->bindParam(":location", $location);
        $stmt->bindParam(":quantity", $quantity);
        $stmt->bindParam(":notes", $notes);
        
        return $stmt->execute();
    }
    
    public function updateLocationQuantity($location, $quantity) {
        $query = "UPDATE " . $this->locations_table . "
                 SET quantity=:quantity, updated_at=datetime('now')
                 WHERE ingredient_id=:ingredient_id AND location=:location";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ingredient_id", $this->id);
        $stmt->bindParam(":location", $location);
        $stmt->bindParam(":quantity", $quantity);
        
        return $stmt->execute();
    }
    
    public function removeLocation($location) {
        $query = "DELETE FROM " . $this->locations_table . "
                 WHERE ingredient_id=:ingredient_id AND location=:location";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ingredient_id", $this->id);
        $stmt->bindParam(":location", $location);
        
        return $stmt->execute();
    }
    
    public function loadLocations() {
        $query = "SELECT location, quantity, notes FROM " . $this->locations_table . "
                 WHERE ingredient_id=:ingredient_id ORDER BY location";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ingredient_id", $this->id);
        $stmt->execute();
        
        $this->locations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->locations[] = $row;
        }
        
        return $this->locations;
    }
    
    public function clearLocations() {
        $query = "DELETE FROM " . $this->locations_table . " WHERE ingredient_id=:ingredient_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":ingredient_id", $this->id);
        return $stmt->execute();
    }
    
    public function getTotalQuantity() {
        $query = "SELECT COALESCE(SUM(quantity), 0) as total FROM " . $this->locations_table . "
                 WHERE ingredient_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['total'] : 0;
    }

    // Search ingredients
    public function search($keywords) {
        $query = "SELECT * FROM ingredient_totals 
                 WHERE name LIKE ? OR category LIKE ? OR supplier LIKE ?
                 ORDER BY name ASC";
        
        $keywords = "%{$keywords}%";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$keywords, $keywords, $keywords]);
        return $stmt;
    }
}
?>