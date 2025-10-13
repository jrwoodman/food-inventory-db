<?php
class Ingredient {
    private $conn;
    private $table_name = "ingredients";

    public $id;
    public $name;
    public $category;
    public $quantity;
    public $unit;
    public $cost_per_unit;
    public $supplier;
    public $purchase_date;
    public $expiry_date;
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
                     unit=:unit, cost_per_unit=:cost_per_unit, supplier=:supplier,
                     purchase_date=:purchase_date, expiry_date=:expiry_date,
                     location=:location, notes=:notes, created_at=NOW()";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":cost_per_unit", $this->cost_per_unit);
        $stmt->bindParam(":supplier", $this->supplier);
        $stmt->bindParam(":purchase_date", $this->purchase_date);
        $stmt->bindParam(":expiry_date", $this->expiry_date);
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
            $this->cost_per_unit = $row['cost_per_unit'];
            $this->supplier = $row['supplier'];
            $this->purchase_date = $row['purchase_date'];
            $this->expiry_date = $row['expiry_date'];
            $this->location = $row['location'];
            $this->notes = $row['notes'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                 SET name=:name, category=:category, quantity=:quantity,
                     unit=:unit, cost_per_unit=:cost_per_unit, supplier=:supplier,
                     purchase_date=:purchase_date, expiry_date=:expiry_date,
                     location=:location, notes=:notes, updated_at=NOW()
                 WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":cost_per_unit", $this->cost_per_unit);
        $stmt->bindParam(":supplier", $this->supplier);
        $stmt->bindParam(":purchase_date", $this->purchase_date);
        $stmt->bindParam(":expiry_date", $this->expiry_date);
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

    public function getLowStockItems($threshold = 10) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE quantity <= :threshold
                 ORDER BY quantity ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":threshold", $threshold);
        $stmt->execute();
        return $stmt;
    }
}
?>