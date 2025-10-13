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
    public $location;
    public $notes;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                 SET name=:name, category=:category, quantity=:quantity, 
                     unit=:unit, expiry_date=:expiry_date, purchase_date=:purchase_date,
                     location=:location, notes=:notes, created_at=NOW()";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":expiry_date", $this->expiry_date);
        $stmt->bindParam(":purchase_date", $this->purchase_date);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":notes", $this->notes);

        if($stmt->execute()) {
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
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
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
            $this->location = $row['location'];
            $this->notes = $row['notes'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                 SET name=:name, category=:category, quantity=:quantity,
                     unit=:unit, expiry_date=:expiry_date, purchase_date=:purchase_date,
                     location=:location, notes=:notes, updated_at=NOW()
                 WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":expiry_date", $this->expiry_date);
        $stmt->bindParam(":purchase_date", $this->purchase_date);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
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

    public function getExpiringItems($days = 7) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE expiry_date <= DATE_ADD(NOW(), INTERVAL :days DAY) 
                 AND expiry_date >= NOW()
                 ORDER BY expiry_date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":days", $days);
        $stmt->execute();
        return $stmt;
    }
}
?>