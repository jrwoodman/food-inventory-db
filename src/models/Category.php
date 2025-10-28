<?php
class Category {
    private $conn;
    private $table_name = "categories";

    public $id;
    public $name;
    public $type;
    public $description;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                 (name, type, description, created_at)
                 VALUES (?, ?, ?, CURRENT_TIMESTAMP)";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->type = htmlspecialchars(strip_tags($this->type));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));

        $stmt->execute([
            $this->name,
            $this->type,
            $this->description
        ]);

        if($stmt->rowCount()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function read($type = null) {
        $query = "SELECT * FROM " . $this->table_name;
        if ($type) {
            $query .= " WHERE type = ?";
        }
        $query .= " ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($query);
        if ($type) {
            $stmt->execute([$type]);
        } else {
            $stmt->execute();
        }
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
            $this->type = $row['type'];
            $this->description = $row['description'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                 SET name = ?, type = ?, description = ?, updated_at = CURRENT_TIMESTAMP
                 WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->type = htmlspecialchars(strip_tags($this->type));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));

        $stmt->execute([
            $this->name,
            $this->type,
            $this->description,
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

    public function nameExists($type = null, $exclude_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE name = ?";
        
        // Clean data the same way as in create/update
        $clean_name = htmlspecialchars(strip_tags($this->name));
        $params = [$clean_name];
        
        if ($type) {
            $query .= " AND type = ?";
            $params[] = $type;
        }
        
        if ($exclude_id) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        // Use fetch instead of rowCount for SELECT queries (SQLite compatibility)
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public static function getCategoryOptions($db, $type = null) {
        $query = "SELECT id, name, type, description FROM categories";
        if ($type) {
            $query .= " WHERE type = ?";
        }
        $query .= " ORDER BY name ASC";
        
        $stmt = $db->prepare($query);
        if ($type) {
            $stmt->execute([$type]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
