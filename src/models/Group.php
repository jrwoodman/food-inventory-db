<?php

class Group {
    private $conn;
    private $table_name = "groups";
    
    public $id;
    public $name;
    public $description;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create a new group
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, description) 
                  VALUES (:name, :description)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
    
    /**
     * Read all groups
     */
    public function read() {
        $query = "SELECT id, name, description, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Read groups for a specific user
     */
    public function readByUser($user_id) {
        $query = "SELECT g.id, g.name, g.description, g.created_at, g.updated_at, ug.role
                  FROM " . $this->table_name . " g
                  INNER JOIN user_groups ug ON g.id = ug.group_id
                  WHERE ug.user_id = :user_id
                  ORDER BY g.name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Read a single group by ID
     */
    public function readOne() {
        $query = "SELECT id, name, description, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }
    
    /**
     * Update group
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, 
                      description = :description,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Delete group
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Get all members of a group
     */
    public function getMembers() {
        $query = "SELECT u.id, u.username, u.email, u.first_name, u.last_name, ug.role, ug.joined_at
                  FROM users u
                  INNER JOIN user_groups ug ON u.id = ug.user_id
                  WHERE ug.group_id = :group_id
                  ORDER BY u.username ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":group_id", $this->id);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Add a user to the group
     */
    public function addMember($user_id, $role = 'member') {
        $query = "INSERT INTO user_groups (user_id, group_id, role) 
                  VALUES (:user_id, :group_id, :role)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":group_id", $this->id);
        $stmt->bindParam(":role", $role);
        
        return $stmt->execute();
    }
    
    /**
     * Remove a user from the group
     */
    public function removeMember($user_id) {
        $query = "DELETE FROM user_groups 
                  WHERE user_id = :user_id AND group_id = :group_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":group_id", $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Update a member's role
     */
    public function updateMemberRole($user_id, $role) {
        $query = "UPDATE user_groups 
                  SET role = :role 
                  WHERE user_id = :user_id AND group_id = :group_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":role", $role);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":group_id", $this->id);
        
        return $stmt->execute();
    }
    
    /**
     * Check if a user is a member of the group
     */
    public function isMember($user_id) {
        $query = "SELECT role FROM user_groups 
                  WHERE user_id = :user_id AND group_id = :group_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":group_id", $this->id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get member count
     */
    public function getMemberCount() {
        $query = "SELECT COUNT(*) as count FROM user_groups WHERE group_id = :group_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":group_id", $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
    
    /**
     * Get inventory counts for the group
     */
    public function getInventoryCounts() {
        $food_query = "SELECT COUNT(*) as count FROM foods WHERE group_id = :group_id";
        $ingredient_query = "SELECT COUNT(*) as count FROM ingredients WHERE group_id = :group_id";
        
        $stmt = $this->conn->prepare($food_query);
        $stmt->bindParam(":group_id", $this->id);
        $stmt->execute();
        $food_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $stmt = $this->conn->prepare($ingredient_query);
        $stmt->bindParam(":group_id", $this->id);
        $stmt->execute();
        $ingredient_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        return [
            'foods' => $food_count,
            'ingredients' => $ingredient_count
        ];
    }
}
