<?php
class Unit {
    private $conn;
    private $table_name = "units";

    public $id;
    public $name;
    public $abbreviation;
    public $description;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                 (name, abbreviation, description, is_active, created_at)
                 VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->name = strip_tags($this->name);
        $this->abbreviation = strip_tags($this->abbreviation);
        $this->description = strip_tags($this->description ?? '');
        $this->is_active = $this->is_active ?? 1;

        $stmt->execute([
            $this->name,
            $this->abbreviation,
            $this->description,
            $this->is_active
        ]);

        if($stmt->rowCount()) {
            $this->id = $this->conn->lastInsertId();
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

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->name = $row['name'];
            $this->abbreviation = $row['abbreviation'];
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
                 SET name = ?, abbreviation = ?, description = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP
                 WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->name = strip_tags($this->name);
        $this->abbreviation = strip_tags($this->abbreviation);
        $this->description = strip_tags($this->description ?? '');
        $this->is_active = $this->is_active ?? 1;

        $stmt->execute([
            $this->name,
            $this->abbreviation,
            $this->description,
            $this->is_active,
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

    public function toggleActive() {
        $query = "UPDATE " . $this->table_name . "
                 SET is_active = NOT is_active, updated_at = CURRENT_TIMESTAMP
                 WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        return $stmt->execute();
    }

    public function nameExists($exclude_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE name = ?";
        if ($exclude_id) {
            $query .= " AND id != ?";
        }
        
        // Clean data the same way as in create/update
        $clean_name = strip_tags($this->name);
        
        $stmt = $this->conn->prepare($query);
        if ($exclude_id) {
            $stmt->execute([$clean_name, $exclude_id]);
        } else {
            $stmt->execute([$clean_name]);
        }
        
        // Use fetch instead of rowCount for SELECT queries (SQLite compatibility)
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false;
    }
    
    public function abbreviationExists($exclude_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE abbreviation = ?";
        if ($exclude_id) {
            $query .= " AND id != ?";
        }
        
        // Clean data the same way as in create/update
        $clean_abbreviation = strip_tags($this->abbreviation);
        
        $stmt = $this->conn->prepare($query);
        if ($exclude_id) {
            $stmt->execute([$clean_abbreviation, $exclude_id]);
        } else {
            $stmt->execute([$clean_abbreviation]);
        }
        
        // Use fetch instead of rowCount for SELECT queries (SQLite compatibility)
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false;
    }

    public static function getUnitOptions($db, $active_only = false) {
        $query = "SELECT id, name, abbreviation, description FROM units";
        if ($active_only) {
            $query .= " WHERE is_active = 1";
        }
        $query .= " ORDER BY name ASC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
