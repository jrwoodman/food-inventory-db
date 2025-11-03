<?php
class Food {
    private $conn;
    private $table_name = "foods";
    private $locations_table = "food_locations";

    public $id;
    public $name;
    public $category;
    public $brand;
    public $unit;
    public $expiry_date;
    public $purchase_date;
    public $purchase_location;
    public $notes;
    public $contains_gluten;
    public $contains_milk;
    public $contains_soy;
    public $contains_nuts;
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
            
            // Insert into foods table (without location and quantity)
            $query = "INSERT INTO " . $this->table_name . "
                     (name, category, brand, unit, expiry_date, purchase_date, 
                      purchase_location, notes, contains_gluten, contains_milk, 
                      contains_soy, contains_nuts, user_id, group_id, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";

            $stmt = $this->conn->prepare($query);

            // Clean data (remove HTML tags but keep special characters)
            $this->name = strip_tags($this->name);
            $this->category = strip_tags($this->category ?? '');
            $this->brand = strip_tags($this->brand ?? '');
            $this->unit = strip_tags($this->unit ?? 'pieces');
            $this->expiry_date = $this->expiry_date ?: null;
            $this->purchase_date = $this->purchase_date ?: null;
            $this->purchase_location = strip_tags($this->purchase_location ?? '');
            $this->notes = strip_tags($this->notes ?? '');
            $this->contains_gluten = isset($this->contains_gluten) ? (int)$this->contains_gluten : 0;
            $this->contains_milk = isset($this->contains_milk) ? (int)$this->contains_milk : 0;
            $this->contains_soy = isset($this->contains_soy) ? (int)$this->contains_soy : 0;
            $this->contains_nuts = isset($this->contains_nuts) ? (int)$this->contains_nuts : 0;
            $this->user_id = $this->user_id ?? null;
            $this->group_id = $this->group_id ?? null;

            $stmt->execute([
                $this->name,
                $this->category,
                $this->brand,
                $this->unit,
                $this->expiry_date,
                $this->purchase_date,
                $this->purchase_location,
                $this->notes,
                $this->contains_gluten,
                $this->contains_milk,
                $this->contains_soy,
                $this->contains_nuts,
                $this->user_id,
                $this->group_id
            ]);

            if(!$stmt->rowCount()) {
                $this->conn->rollBack();
                return false;
            }
            
            $food_id = $this->conn->lastInsertId();
            $this->id = $food_id;
            
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
        $query = "SELECT f.*, 
                  COALESCE(SUM(fl.quantity), 0) as total_quantity,
                  g.name as group_name
                  FROM " . $this->table_name . " f
                  LEFT JOIN " . $this->locations_table . " fl ON f.id = fl.food_id
                  LEFT JOIN groups g ON f.group_id = g.id
                  GROUP BY f.id
                  ORDER BY f.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function readByUser($user_id) {
        $query = "SELECT f.*, 
                  COALESCE(SUM(fl.quantity), 0) as total_quantity
                  FROM " . $this->table_name . " f
                  LEFT JOIN " . $this->locations_table . " fl ON f.id = fl.food_id
                  WHERE f.user_id = ?
                  GROUP BY f.id
                  ORDER BY f.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt;
    }

    public function readByGroups($group_ids) {
        if (empty($group_ids)) {
            return false;
        }
        
        $placeholders = implode(',', array_fill(0, count($group_ids), '?'));
        $query = "SELECT f.*, 
                  COALESCE(SUM(fl.quantity), 0) as total_quantity
                  FROM " . $this->table_name . " f
                  LEFT JOIN " . $this->locations_table . " fl ON f.id = fl.food_id
                  WHERE f.group_id IN ($placeholders)
                  GROUP BY f.id
                  ORDER BY f.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($group_ids);
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
            $this->brand = $row['brand'];
            $this->unit = $row['unit'];
            $this->expiry_date = $row['expiry_date'];
            $this->purchase_date = $row['purchase_date'];
            $this->purchase_location = $row['purchase_location'];
            $this->notes = $row['notes'];
            $this->contains_gluten = $row['contains_gluten'] ?? 0;
            $this->contains_milk = $row['contains_milk'] ?? 0;
            $this->contains_soy = $row['contains_soy'] ?? 0;
            $this->contains_nuts = $row['contains_nuts'] ?? 0;
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
            
            // Update foods table (without location and quantity)
            $query = "UPDATE " . $this->table_name . "
                     SET name = ?, category = ?, brand = ?, unit = ?, 
                         expiry_date = ?, purchase_date = ?, purchase_location = ?,
                         notes = ?, contains_gluten = ?, contains_milk = ?, 
                         contains_soy = ?, contains_nuts = ?, user_id = ?, group_id = ?, 
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = ?";

            $stmt = $this->conn->prepare($query);

            // Clean data (remove HTML tags but keep special characters)
            $this->name = strip_tags($this->name);
            $this->category = strip_tags($this->category ?? '');
            $this->brand = strip_tags($this->brand ?? '');
            $this->unit = strip_tags($this->unit ?? 'pieces');
            $this->expiry_date = $this->expiry_date ?: null;
            $this->purchase_date = $this->purchase_date ?: null;
            $this->purchase_location = strip_tags($this->purchase_location ?? '');
            $this->notes = strip_tags($this->notes ?? '');
            $this->contains_gluten = isset($this->contains_gluten) ? (int)$this->contains_gluten : 0;
            $this->contains_milk = isset($this->contains_milk) ? (int)$this->contains_milk : 0;
            $this->contains_soy = isset($this->contains_soy) ? (int)$this->contains_soy : 0;
            $this->contains_nuts = isset($this->contains_nuts) ? (int)$this->contains_nuts : 0;
            $this->user_id = $this->user_id ?? null;
            $this->group_id = $this->group_id ?? null;

            $stmt->execute([
                $this->name,
                $this->category,
                $this->brand,
                $this->unit,
                $this->expiry_date,
                $this->purchase_date,
                $this->purchase_location,
                $this->notes,
                $this->contains_gluten,
                $this->contains_milk,
                $this->contains_soy,
                $this->contains_nuts,
                $this->user_id,
                $this->group_id,
                $this->id
            ]);
            
            // Update location data if provided
            if (!empty($this->locations)) {
                // Delete existing locations and add new ones
                $this->clearLocations();
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

    public function getExpiringItems($days = 7) {
        $query = "SELECT f.*, 
                  COALESCE(SUM(fl.quantity), 0) as total_quantity,
                  CASE 
                    WHEN date(f.expiry_date) < date('now') THEN 'expired'
                    ELSE 'expiring'
                  END as status
                 FROM " . $this->table_name . " f
                 LEFT JOIN " . $this->locations_table . " fl ON f.id = fl.food_id
                 WHERE f.expiry_date IS NOT NULL
                 AND date(f.expiry_date) <= date('now', '+' || CAST(? AS TEXT) || ' days')
                 GROUP BY f.id
                 ORDER BY f.expiry_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$days]);
        return $stmt;
    }
    

    public function getExpiringItemsByUser($user_id, $days = 7) {
        $query = "SELECT f.*, 
                  COALESCE(SUM(fl.quantity), 0) as total_quantity,
                  CASE 
                    WHEN date(f.expiry_date) < date('now') THEN 'expired'
                    ELSE 'expiring'
                  END as status
                 FROM " . $this->table_name . " f
                 LEFT JOIN " . $this->locations_table . " fl ON f.id = fl.food_id
                 WHERE f.user_id = ?
                 AND f.expiry_date IS NOT NULL
                 AND date(f.expiry_date) <= date('now', '+' || CAST(? AS TEXT) || ' days')
                 GROUP BY f.id
                 ORDER BY f.expiry_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $days]);
        return $stmt;
    }

    public function getExpiringItemsByGroups($group_ids, $days = 7) {
        if (empty($group_ids)) {
            return false;
        }
        
        $placeholders = implode(',', array_fill(0, count($group_ids), '?'));
        $query = "SELECT f.*, 
                  COALESCE(SUM(fl.quantity), 0) as total_quantity,
                  CASE 
                    WHEN date(f.expiry_date) < date('now') THEN 'expired'
                    ELSE 'expiring'
                  END as status
                 FROM " . $this->table_name . " f
                 LEFT JOIN " . $this->locations_table . " fl ON f.id = fl.food_id
                 WHERE f.group_id IN ($placeholders)
                 AND f.expiry_date IS NOT NULL
                 AND date(f.expiry_date) <= date('now', '+' || CAST(? AS TEXT) || ' days')
                 GROUP BY f.id
                 ORDER BY f.expiry_date ASC";
        
        $params = array_merge($group_ids, [$days]);
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    public function getLowStockItems($threshold = 10) {
        $query = "SELECT f.*, COALESCE(SUM(fl.quantity), 0) as total_quantity
                 FROM " . $this->table_name . " f
                 LEFT JOIN " . $this->locations_table . " fl ON f.id = fl.food_id
                 GROUP BY f.id
                 HAVING total_quantity <= ?
                 ORDER BY total_quantity ASC, f.name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([floatval($threshold)]);
        return $stmt;
    }
    
    public function getLowStockItemsByGroups($group_ids, $threshold = 10) {
        if (empty($group_ids)) {
            return false;
        }
        
        $placeholders = implode(',', array_fill(0, count($group_ids), '?'));
        $query = "SELECT f.*, COALESCE(SUM(fl.quantity), 0) as total_quantity
                 FROM " . $this->table_name . " f
                 LEFT JOIN " . $this->locations_table . " fl ON f.id = fl.food_id
                 WHERE f.group_id IN ($placeholders)
                 GROUP BY f.id
                 HAVING total_quantity <= ?
                 ORDER BY total_quantity ASC, f.name ASC";
        
        $params = array_merge($group_ids, [floatval($threshold)]);
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    // Search foods
    public function search($keywords) {
        $query = "SELECT f.*, 
                  COALESCE(SUM(fl.quantity), 0) as total_quantity
                  FROM " . $this->table_name . " f
                  LEFT JOIN " . $this->locations_table . " fl ON f.id = fl.food_id
                  WHERE f.name LIKE ? OR f.category LIKE ? OR f.notes LIKE ? OR f.purchase_location LIKE ?
                  GROUP BY f.id
                  ORDER BY f.created_at DESC";
        
        $keywords = "%{$keywords}%";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$keywords, $keywords, $keywords, $keywords]);
        return $stmt;
    }
    
    // Location management methods
    public function addLocation($location, $quantity, $notes = '') {
        $query = "INSERT INTO " . $this->locations_table . " 
                 (food_id, location, quantity, notes, created_at)
                 VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
        
        $stmt = $this->conn->prepare($query);
        $location = htmlspecialchars(strip_tags($location));
        $notes = htmlspecialchars(strip_tags($notes));
        
        return $stmt->execute([$this->id, $location, $quantity, $notes]);
    }
    
    public function updateLocationQuantity($location, $quantity) {
        $query = "UPDATE " . $this->locations_table . " 
                 SET quantity = ?, updated_at = CURRENT_TIMESTAMP
                 WHERE food_id = ? AND location = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$quantity, $this->id, $location]);
    }
    
    public function removeLocation($location) {
        $query = "DELETE FROM " . $this->locations_table . " 
                 WHERE food_id = ? AND location = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$this->id, $location]);
    }
    
    public function clearLocations() {
        $query = "DELETE FROM " . $this->locations_table . " WHERE food_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$this->id]);
    }
    
    public function loadLocations() {
        $query = "SELECT * FROM " . $this->locations_table . " 
                 WHERE food_id = ? 
                 ORDER BY location";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        
        $this->locations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->locations[] = $row;
        }
        
        return $this->locations;
    }
    
    public function readWithLocations() {
        $query = "SELECT * FROM food_location_details ORDER BY name, location";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
