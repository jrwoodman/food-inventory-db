<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password_hash;
    public $first_name;
    public $last_name;
    public $role;
    public $is_active;
    public $last_login;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                     (username, email, password_hash, first_name, last_name, role, is_active, created_at)
                     VALUES (:username, :email, :password_hash, :first_name, :last_name, :role, :is_active, datetime('now'))";

            $stmt = $this->conn->prepare($query);

            // Hash the password
            $hashed_password = password_hash($this->password_hash, PASSWORD_DEFAULT);

            $stmt->bindParam(":username", $this->username);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":password_hash", $hashed_password);
            $stmt->bindParam(":first_name", $this->first_name);
            $stmt->bindParam(":last_name", $this->last_name);
            $stmt->bindParam(":role", $this->role);
            $stmt->bindParam(":is_active", $this->is_active);

            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
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
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password_hash = $row['password_hash'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->role = $row['role'];
            $this->is_active = $row['is_active'];
            $this->last_login = $row['last_login'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                 SET username=:username, email=:email, first_name=:first_name, 
                     last_name=:last_name, role=:role, is_active=:is_active,
                     updated_at=datetime('now')
                 WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function updatePassword($new_password) {
        $query = "UPDATE " . $this->table_name . "
                 SET password_hash=:password_hash, updated_at=datetime('now')
                 WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt->bindParam(":password_hash", $hashed_password);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }

    public function findByUsername($username) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = ? AND is_active = 1 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password_hash = $row['password_hash'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->role = $row['role'];
            $this->is_active = $row['is_active'];
            $this->last_login = $row['last_login'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = ? AND is_active = 1 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password_hash = $row['password_hash'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->role = $row['role'];
            $this->is_active = $row['is_active'];
            $this->last_login = $row['last_login'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    public function verifyPassword($password) {
        return password_verify($password, $this->password_hash);
    }

    public function updateLastLogin() {
        $query = "UPDATE " . $this->table_name . "
                 SET last_login=datetime('now')
                 WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    public function getUsersCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    public function getFullName() {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function isAdmin() {
        return $this->role === 'admin';
    }

    public function canEdit() {
        return in_array($this->role, ['admin', 'user']);
    }

    public function canView() {
        return in_array($this->role, ['admin', 'user', 'viewer']);
    }

    /**
     * Get all groups the user belongs to
     */
    public function getGroups() {
        $query = "SELECT g.id, g.name, g.description, ug.role, ug.joined_at
                  FROM groups g
                  INNER JOIN user_groups ug ON g.id = ug.group_id
                  WHERE ug.user_id = :user_id
                  ORDER BY g.name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Get group IDs for the user (for filtering inventory)
     */
    public function getGroupIds() {
        $query = "SELECT group_id FROM user_groups WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->execute();
        
        $group_ids = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $group_ids[] = $row['group_id'];
        }
        
        return $group_ids;
    }

    /**
     * Check if user is member of a specific group
     */
    public function isMemberOfGroup($group_id) {
        $query = "SELECT role FROM user_groups 
                  WHERE user_id = :user_id AND group_id = :group_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->bindParam(":group_id", $group_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get user's role in a specific group
     */
    public function getGroupRole($group_id) {
        $membership = $this->isMemberOfGroup($group_id);
        return $membership ? $membership['role'] : null;
    }
}
?>
